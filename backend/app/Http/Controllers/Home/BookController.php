<?php

namespace App\Http\Controllers\Home;

use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookTemplateExport;

class BookController extends Controller
{
    /**
     * Helper: trả JSON không escape Unicode (fix \u00ea, \u1ec7...)
     */
    private function json($data, int $status = 200, array $headers = [])
    {
        return response()->json($data, $status, $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Helper: chuyển HTML -> plain text (bỏ <p>, <table>, ...)
     */
    private function plainText(?string $html): string
    {
        if (!$html) return '';

        // Bỏ tag
        $text = strip_tags($html);

        // Decode entity (&nbsp; &amp; ...)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Gom khoảng trắng cho gọn
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // Chuẩn hoá xuống dòng
        $text = preg_replace("/\r\n|\r|\n/", "\n", $text);

        return trim($text);
    }

    /**
     * Helper: strip HTML cho description trong collection
     */
    private function sanitizeBooksDescription($collection)
    {
        if (!$collection) return $collection;

        return $collection->transform(function ($b) {
            if (isset($b->description)) {
                $b->description = $this->plainText($b->description);
            }
            return $b;
        });
    }

    public function index()
    {
        // 5 sách giấy được đánh giá cao nhiều nhất
        $topRatedBooks = Book::with(['author', 'category', 'publisher'])
            ->where('is_physical', 1)
            ->orderByDesc('rating_avg')
            ->take(6)
            ->get();

        // 5 sách giấy được xem nhiều nhất
        $topViewedBooks = Book::with(['author', 'category', 'publisher'])
            ->where('is_physical', 0)
            ->orderByDesc('views')
            ->take(6)
            ->get();

        // 10 sách giấy mới nhất
        $latestPaperBooks = Book::with(['author', 'category', 'publisher'])
            ->where('is_physical', 0)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // 10 sách ebook mới nhất
        $latestEbooks = Book::with(['author', 'category', 'publisher'])
            ->where('is_physical', 1)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // ✅ strip HTML description
        $this->sanitizeBooksDescription($topRatedBooks);
        $this->sanitizeBooksDescription($topViewedBooks);
        $this->sanitizeBooksDescription($latestPaperBooks);
        $this->sanitizeBooksDescription($latestEbooks);

        return $this->json([
            'status' => 'success',
            'top_rated_books' => $topRatedBooks,        // 5 sách giấy đánh giá cao nhất
            'top_viewed_books' => $topViewedBooks,      // 5 sách giấy xem nhiều nhất
            'latest_paper_books' => $latestPaperBooks,  // 10 sách giấy mới
            'latest_ebooks' => $latestEbooks            // 10 sách điện tử mới
        ]);
    }

    public function getAllIds()
    {
        try {
            // Lấy tất cả ID và title của sách
            $books = Book::select('id', 'title')
                ->orderBy('title', 'asc')
                ->get();

            return $this->json([
                'status' => 'success',
                'message' => 'Lấy danh sách ID và tên sách thành công',
                'data' => $books,
                'total' => $books->count()
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy danh sách sách',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Book::with(['author', 'category', 'publisher']);

        // Search theo tên sách
        if ($request->has('name') && !empty($request->name)) {
            $query->where('title', 'LIKE', '%' . $request->name . '%');
        }

        // Filter theo categories (multiple values)
        if ($request->has('category') && !empty($request->category)) {
            $categories = explode(',', $request->category);
            $categories = array_map('trim', $categories); // Loại bỏ space

            $query->where(function ($q) use ($categories) {
                foreach ($categories as $category) {
                    $q->orWhere(function ($subQ) use ($category) {
                        if (is_numeric($category)) {
                            $subQ->where('category_id', $category);
                        } else {
                            $subQ->whereHas('category', function ($categoryQ) use ($category) {
                                $categoryQ->where('name', 'LIKE', '%' . $category . '%');
                            });
                        }
                    });
                }
            });
        }

        // Filter theo authors (multiple values)
        if ($request->has('author') && !empty($request->author)) {
            $authors = explode(',', $request->author);
            $authors = array_map('trim', $authors); // Loại bỏ space

            $query->where(function ($q) use ($authors) {
                foreach ($authors as $author) {
                    $q->orWhere(function ($subQ) use ($author) {
                        if (is_numeric($author)) {
                            $subQ->where('author_id', $author);
                        } else {
                            $subQ->whereHas('author', function ($authorQ) use ($author) {
                                $authorQ->where('name', 'LIKE', '%' . $author . '%');
                            });
                        }
                    });
                }
            });
        }

        // Filter theo publisher (có thể truyền ID hoặc tên)
        if ($request->has('publisher') && !empty($request->publisher)) {
            $publisher = $request->publisher;
            if (is_numeric($publisher)) {
                $query->where('publisher_id', $publisher);
            } else {
                $query->whereHas('publisher', function ($q) use ($publisher) {
                    $q->where('name', 'LIKE', '%' . $publisher . '%');
                });
            }
        }

        // Filter theo giá cụ thể
        if ($request->has('price') && !empty($request->price)) {
            $query->where('price', $request->price);
        }

        // Filter theo khoảng giá - SỬA TÊN PARAM
        if ($request->has('price_min') && !empty($request->price_min)) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max') && !empty($request->price_max)) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter theo khoảng giá định sẵn
        if ($request->has('price_range') && !empty($request->price_range)) {
            switch ($request->price_range) {
                case 'free':
                    $query->where('price', 0);
                    break;
                case 'cheap':      // Dưới 50k
                    $query->where('price', '>', 0)->where('price', '<', 50000);
                    break;
                case 'medium':     // 50k - 150k
                    $query->whereBetween('price', [50000, 150000]);
                    break;
                case 'expensive':  // 150k - 300k
                    $query->whereBetween('price', [150000, 300000]);
                    break;
                case 'premium':    // Trên 300k
                    $query->where('price', '>', 300000);
                    break;
            }
        }

        // Filter theo loại sách - SỬA LOGIC
        if ($request->has('type') && $request->type !== '') {
            if ($request->type === 'paper') {
                $query->where('is_physical', 1); // Sách giấy = 1
            } elseif ($request->type === 'ebook') {
                $query->where('is_physical', 0); // Ebook = 0
            }
        }

        // Filter theo stock
        if ($request->has('available') && $request->available !== '') {
            if ($request->available == 1 || $request->available === 'true') {
                $query->where('stock', '>', 0);
            } else {
                $query->where('stock', 0);
            }
        }

        // Sắp xếp
        $sort = $request->get('sort', 'newest');

        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            case 'liked':
                $query->orderBy('likes', 'desc');
                break;
            case 'name_az':
                $query->orderBy('title', 'asc');
                break;
            case 'name_za':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Phân trang
        $limit = $request->get('limit', 12);
        $limit = min($limit, 50);

        // Lấy kết quả
        if ($request->has('all') && $request->all == 1) {
            $books = $query->get();

            // ✅ strip HTML description
            $this->sanitizeBooksDescription($books);

            $result = [
                'status' => 'success',
                'data' => $books,
                'total' => $books->count()
            ];
        } else {
            $books = $query->paginate($limit);

            // ✅ strip HTML description (items của paginator)
            $items = collect($books->items());
            $items = $items->map(function ($b) {
                if (isset($b->description)) {
                    $b->description = $this->plainText($b->description);
                }
                return $b;
            })->values();

            $result = [
                'status' => 'success',
                'data' => $items,
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                    'per_page' => $books->perPage(),
                    'total' => $books->total(),
                    'from' => $books->firstItem(),
                    'to' => $books->lastItem(),
                    'has_more' => $books->hasMorePages()
                ]
            ];
        }

        return $this->json($result);
    }

    // Helper function để hiển thị các filter đã áp dụng
    private function getAppliedFilters(Request $request)
    {
        $filters = [];

        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }

        if ($request->has('author_id') && !empty($request->author_id)) {
            $filters['author_id'] = $request->author_id;
        }

        if ($request->has('author_name') && !empty($request->author_name)) {
            $filters['author_name'] = $request->author_name;
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $filters['category_id'] = $request->category_id;
        }

        if ($request->has('category_name') && !empty($request->category_name)) {
            $filters['category_name'] = $request->category_name;
        }

        if ($request->has('publisher_id') && !empty($request->publisher_id)) {
            $filters['publisher_id'] = $request->publisher_id;
        }

        if ($request->has('is_physical') && $request->is_physical !== '') {
            $filters['is_physical'] = $request->is_physical;
        }

        if ($request->has('price_min') && !empty($request->price_min)) {
            $filters['price_min'] = $request->price_min;
        }

        if ($request->has('price_max') && !empty($request->price_max)) {
            $filters['price_max'] = $request->price_max;
        }

        if ($request->has('price_range') && !empty($request->price_range)) {
            $filters['price_range'] = $request->price_range;
        }

        if ($request->has('in_stock') && $request->in_stock !== '') {
            $filters['in_stock'] = $request->in_stock;
        }

        if ($request->has('sort_by') && !empty($request->sort_by)) {
            $filters['sort_by'] = $request->sort_by;
        }

        if ($request->has('sort_order') && !empty($request->sort_order)) {
            $filters['sort_order'] = $request->sort_order;
        }

        return $filters;
    }

    // Chi tiết sách
    public function show($id)
    {
        $book = Book::with(['author', 'publisher', 'category'])->find($id);

        if (!$book) {
            return $this->json(['message' => 'Book not found1'], 404);
        }

        // ✅ strip HTML description
        $desc = $this->plainText($book->description);

        if ($book->is_physical == 1) {
            // Sách giấy
            return $this->json([
                'id' => $book->id,
                'title' => $book->title,
                'description' => $desc,
                'cover_image' => $book->cover_image,
                'author' => $book->author ? [
                    'id' => $book->author->id,
                    'name' => $book->author->name,
                ] : null,
                'publisher' => $book->publisher ? [
                    'id' => $book->publisher->id,
                    'name' => $book->publisher->name,
                ] : null,
                'category' => $book->category ? [
                    'id' => $book->category->id,
                    'name' => $book->category->name,
                ] : null,
                'ratings' => $book->rating ? [
                    'id' => $book->rating->id,
                    'name' => $book->rating->name,
                ] : null,
                'price' => $book->price,
                'discount_price' => $book->discount_price,
                'stock' => $book->stock,
                'views' => $book->views,
                'likes' => $book->likes,
                'is_physical' => $book->is_physical,
                'format' => 'paper',
            ]);
        } elseif ($book->is_physical == 0) {
            // Sách điện tử
            return $this->json([
                'id' => $book->id,
                'title' => $book->title,
                'description' => $desc,
                'cover_image' => $book->cover_image,
                'author' => $book->author ? [
                    'id' => $book->author->id,
                    'name' => $book->author->name,
                ] : null,
                'publisher' => $book->publisher ? [
                    'id' => $book->publisher->id,
                    'name' => $book->publisher->name,
                ] : null,
                'category' => $book->category ? [
                    'id' => $book->category->id,
                    'name' => $book->category->name,
                ] : null,
                'views' => $book->views,
                'likes' => $book->likes,
                'format' => 'ebook',
                'is_physical' => $book->is_physical,
            ]);
        } else {
            return $this->json(['message' => 'Unknown book type'], 400);
        }
    }

    // view update
    public function increaseView($id)
    {
        $book = \App\Models\Book::find($id);

        if (!$book) {
            return $this->json([
                'success' => false,
                'message' => 'Không tìm thấy sách.',
            ], 404);
        }

        // Tăng view lên 1
        $book->increment('views');

        return $this->json([
            'success' => true,
            'message' => 'Lượt xem đã được cập nhật.',
            'views' => $book->views,
        ]);
    }

    /**
     * Import sách từ file Excel
     */
    public function importBooks(Request $request)
    {
        // Validate file upload
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return $this->json([
                'status' => 'error',
                'message' => 'File không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('excel_file');

            // Import data using Maatwebsite Excel
            $data = Excel::toArray([], $file)[0]; // Lấy sheet đầu tiên

            // Bỏ qua dòng header (dòng đầu tiên)
            array_shift($data);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $duplicates = [];

            DB::beginTransaction();

            foreach ($data as $rowIndex => $row) {
                try {
                    // Kiểm tra nếu dòng trống
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Map dữ liệu từ Excel
                    $bookData = $this->mapExcelRowToBookData($row);

                    // Validate dữ liệu
                    $validationResult = $this->validateBookData($bookData, $rowIndex + 2);

                    if (!$validationResult['valid']) {
                        $errors[] = $validationResult['error'];
                        $errorCount++;
                        continue;
                    }

                    // Kiểm tra trùng lặp
                    $existingBook = Book::where('title', $bookData['title'])->first();
                    if ($existingBook) {
                        $duplicates[] = [
                            'row' => $rowIndex + 2,
                            'title' => $bookData['title'],
                            'message' => 'Sách đã tồn tại'
                        ];
                        continue;
                    }

                    // Tạo sách mới với ID trực tiếp
                    Book::create([
                        'title' => $bookData['title'],
                        'description' => $bookData['description'],
                        'author_id' => $bookData['author_id'],
                        'category_id' => $bookData['category_id'],
                        'publisher_id' => $bookData['publisher_id'],
                        'price' => $bookData['price'],
                        'discount_price' => $bookData['discount_price'],
                        'stock' => $bookData['stock'],
                        'is_physical' => $bookData['is_physical'],
                        'cover_image' => $bookData['cover_image'],
                        'views' => 0,
                        'likes' => 0,
                        'rating_avg' => 0,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowIndex + 2,
                        'message' => 'Lỗi xử lý: ' . $e->getMessage()
                    ];
                    $errorCount++;
                }
            }

            DB::commit();

            return $this->json([
                'status' => 'success',
                'message' => 'Import hoàn tất',
                'summary' => [
                    'total_rows' => count($data),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'duplicate_count' => count($duplicates)
                ],

            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy template Excel để import
     */
    public function downloadTemplate()
    {
        try {
            $fileName = 'book_import_template_' . date('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new BookTemplateExport(), $fileName);

        } catch (\Exception $e) {
            \Log::error('Template Download Error: ' . $e->getMessage());

            return $this->json([
                'status' => 'error',
                'message' => 'Lỗi khi tạo template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xem trước dữ liệu từ file Excel
     */
    public function previewImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->json([
                'status' => 'error',
                'message' => 'File không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('excel_file');

            // Import với heading row để lấy header
            $data = Excel::toArray([], $file)[0];

            // Lấy header
            $headers = array_shift($data);

            // Lấy tối đa 10 dòng để preview
            $previewData = array_slice($data, 0, 10);

            $preview = [];
            $validationErrors = [];

            foreach ($previewData as $rowIndex => $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $bookData = $this->mapExcelRowToBookData($row);
                $validationResult = $this->validateBookData($bookData, $rowIndex + 2);

                $preview[] = [
                    'row' => $rowIndex + 2,
                    'data' => $bookData,
                    'valid' => $validationResult['valid']
                ];

                if (!$validationResult['valid']) {
                    $validationErrors[] = $validationResult['error'];
                }
            }

            return $this->json([
                'status' => 'success',
                'headers' => $headers,
                'preview_data' => $preview,
                'total_rows' => count($data) + count($previewData),
                'preview_rows' => count($preview),
                'validation_errors' => $validationErrors
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Authors, Categories, Publishers để tạo dropdown
     */
    public function getDropdownData()
    {
        try {
            $data = [
                'authors' => Author::select('id', 'name')->orderBy('name')->get(),
                'categories' => Category::select('id', 'name')->orderBy('name')->get(),
                'publishers' => Publisher::select('id', 'name')->orderBy('name')->get(),
                'book_types' => [
                    ['value' => 'paper', 'label' => 'Sách giấy'],
                    ['value' => 'ebook', 'label' => 'Sách điện tử']
                ]
            ];

            return $this->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu dropdown thành công',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Lỗi khi lấy dữ liệu dropdown: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getImportStats()
    {
        try {
            $stats = [
                'total_books' => Book::count(),
                'total_authors' => Author::count(),
                'total_categories' => Category::count(),
                'total_publishers' => Publisher::count(),
                'recent_imports' => Book::orderBy('created_at', 'desc')->take(10)->get(['id', 'title', 'created_at']),
                'books_by_type' => [
                    'paper_books' => Book::where('is_physical', 1)->count(),
                    'ebooks' => Book::where('is_physical', 0)->count()
                ]
            ];

            return $this->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map dữ liệu từ Excel row sang book data - CẬP NHẬT ĐỂ SỬ DỤNG ID
     */
    private function mapExcelRowToBookData($row)
    {
        return [
            'title' => trim($row[0] ?? ''),
            'description' => trim($row[1] ?? ''),
            'author_id' => $this->parseIdFromDropdown($row[2] ?? ''),
            'category_id' => $this->parseIdFromDropdown($row[3] ?? ''),
            'publisher_id' => $this->parseIdFromDropdown($row[4] ?? ''),
            'price' => $this->parseNumber($row[5] ?? 0),
            'discount_price' => $this->parseNumber($row[6] ?? null),
            'stock' => (int)($row[7] ?? 0),
            'is_physical' => $this->parseBookType($row[8] ?? ''),
            'cover_image' => trim($row[9] ?? ''),
        ];
    }

    /**
     * Validate dữ liệu sách - CẬP NHẬT ĐỂ VALIDATE ID
     */
    private function validateBookData($data, $rowNumber)
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = 'Tên sách không được để trống';
        }

        if (empty($data['author_id']) || $data['author_id'] <= 0) {
            $errors[] = 'ID tác giả không hợp lệ';
        } else {
            if (!Author::find($data['author_id'])) {
                $errors[] = 'Không tìm thấy tác giả với ID: ' . $data['author_id'];
            }
        }

        if (empty($data['category_id']) || $data['category_id'] <= 0) {
            $errors[] = 'ID thể loại không hợp lệ';
        } else {
            if (!Category::find($data['category_id'])) {
                $errors[] = 'Không tìm thấy thể loại với ID: ' . $data['category_id'];
            }
        }

        if (empty($data['publisher_id']) || $data['publisher_id'] <= 0) {
            $errors[] = 'ID nhà xuất bản không hợp lệ';
        } else {
            if (!Publisher::find($data['publisher_id'])) {
                $errors[] = 'Không tìm thấy nhà xuất bản với ID: ' . $data['publisher_id'];
            }
        }

        if ($data['price'] < 0) {
            $errors[] = 'Giá không hợp lệ';
        }

        if ($data['stock'] < 0) {
            $errors[] = 'Số lượng không hợp lệ';
        }

        if (!in_array($data['is_physical'], [0, 1])) {
            $errors[] = 'Loại sách không hợp lệ (paper/ebook)';
        }

        if (empty($errors)) {
            return ['valid' => true];
        }

        return [
            'valid' => false,
            'error' => [
                'row' => $rowNumber,
                'messages' => $errors
            ]
        ];
    }

    /**
     * Parse số từ string
     */
    private function parseNumber($value)
    {
        if (empty($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^\d.]/', '', $value);

        return is_numeric($cleaned) ? (float)$cleaned : 0;
    }

    /**
     * Parse loại sách
     */
    private function parseBookType($value)
    {
        $value = strtolower(trim($value));

        if (in_array($value, ['paper', 'giấy', 'sách giấy', '1'])) {
            return 1;
        } elseif (in_array($value, ['ebook', 'điện tử', 'sách điện tử', '0'])) {
            return 0;
        }

        return 1;
    }

    /**
     * Parse ID từ dropdown value (format: "ID - Name")
     */
    private function parseIdFromDropdown($value)
    {
        $value = trim($value);

        if (is_numeric($value)) {
            return (int)$value;
        }

        if (strpos($value, ' - ') !== false) {
            $parts = explode(' - ', $value);
            $id = trim($parts[0]);
            return is_numeric($id) ? (int)$id : 0;
        }

        if (strpos($value, '-') !== false) {
            $parts = explode('-', $value);
            $id = trim($parts[0]);
            return is_numeric($id) ? (int)$id : 0;
        }

        return 0;
    }
}
