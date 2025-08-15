<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $Event = Event::all();
        return view('admin.event.index', compact('Event'));
    }

    public function getall()
    {
        $events = Event::with([
            'books' => function ($query) {
                $query->select('books.id', 'title', 'price', 'discount_price', 'cover_image');
            }
        ])->get();

        $result = $events->map(function ($event) {
            return [
                'event_id' => $event->event_id,
                'event_name' => $event->event_name,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'status' => $event->status,
                'books' => $event->books->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'thumb' => $book->cover_image,
                        'title' => $book->title,
                        'price' => $book->price,
                        'discount_price' => $book->discount_price, // Lấy từ bảng books
                        'quantity_limit' => $book->pivot->quantity_limit,
                        'sold_quantity' => $book->pivot->sold_quantity,
                    ];
                }),
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'in:active,inactive'
        ]);
        $event = Event::create($data);
        return response()->json($event, 201);
    }

    /**
     * Thêm sách vào sự kiện với giá giảm
     */
   public function addBookToEvent(Request $request, $eventId)
{
    // ✅ Đổi validation từ books_id thành book_id
    $data = $request->validate([
        'book_id' => 'required|exists:books,id',  // ✅ book_id thay vì books_id
        'discount_percent' => 'required|numeric|min:0|max:100',
        'quantity_limit' => 'required|integer|min:1'
    ]);

    try {
        DB::beginTransaction();

        $book = Book::findOrFail($data['book_id']);  // ✅ book_id
        $event = Event::findOrFail($eventId);

        // Kiểm tra sách đã có discount_price chưa
        if ($book->discount_price !== null && $book->discount_price > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sách này đã có giá giảm, không thể thêm vào sự kiện'
            ], 400);
        }

        // Kiểm tra sách đã có trong event này chưa
        if ($event->books()->where('books.id', $data['book_id'])->exists()) {  // ✅ books.id
            return response()->json([
                'success' => false,
                'message' => 'Sách đã có trong sự kiện này'
            ], 400);
        }

        // Tính giá sau giảm
        $discountPrice = $book->price - ($book->price * $data['discount_percent'] / 100);

        // Cập nhật discount_price trong bảng books
        $book->update(['discount_price' => $discountPrice]);

        // Thêm vào bảng event_products
        $event->books()->attach($data['book_id'], [  // ✅ book_id
            'quantity_limit' => $data['quantity_limit'],
            'sold_quantity' => 0
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Thêm sách vào sự kiện thành công',
            'data' => [
                'book_id' => $book->id,
                'original_price' => $book->price,
                'discount_price' => $discountPrice,
                'discount_percent' => $data['discount_percent']
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Xóa sách khỏi sự kiện và reset discount_price
     */
    public function removeBookFromEvent($eventId, $bookId)
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($eventId);
            $book = Book::findOrFail($bookId);

            // Xóa khỏi event
            $event->books()->detach($bookId);

            // Reset discount_price về null
            $book->update(['discount_price' => null]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa sách khỏi sự kiện thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::with([
                'books' => function ($query) {
                    $query->select('books.id', 'title', 'price', 'discount_price', 'cover_image');
                }
            ])->findOrFail($id);

            $result = [
                'event_id' => $event->event_id,
                'event_name' => $event->event_name,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'status' => $event->status,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
                'books' => $event->books->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'books_id' => $book->id,
                        'title' => $book->title,
                        'price' => $book->price,
                        'discount_price' => $book->discount_price,
                        'cover_images' => $book->cover_image,
                        'quantity_limit' => $book->pivot->quantity_limit,
                        'sold_quantity' => $book->pivot->sold_quantity,
                        // Tính discount_percent từ giá gốc và giá giảm
                        'discount_percent' => $book->discount_price ? 
                            round((($book->price - $book->discount_price) / $book->price) * 100, 2) : 0,
                    ];
                }),
                'total_products' => $event->books->count(),
                'total_sold' => $event->books->sum('pivot.sold_quantity'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Event details retrieved successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->update($request->only(['event_name', 'start_date', 'end_date', 'status']));
        return response()->json($event);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($id);
            
            // Reset discount_price của tất cả sách trong event về null
            $bookIds = $event->books()->pluck('books.id');
            Book::whereIn('id', $bookIds)->update(['discount_price' => null]);

            // Xóa event (cascade sẽ xóa event_products)
            Event::destroy($id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted and book prices reset successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách sách có thể thêm vào event (chưa có discount_price)
     */
   public function getAvailableBooks()
{
    // Thêm log để debug
    \Log::info('=== DEBUG getAvailableBooks ===');
    
    try {
        // Test cơ bản trước
        \Log::info('Step 1: Testing basic response');
        
        // Test kết nối database
        $bookCount = \DB::table('books')->count();
        \Log::info("Total books in database: " . $bookCount);
        
        if ($bookCount == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sách nào trong database',
                'debug' => 'No books found in database'
            ]);
        }

        // Test query đơn giản
        \Log::info('Step 2: Testing simple query');
        $allBooks = \DB::table('books')
                      ->select('id', 'title', 'price', 'discount_price')
                      ->limit(5)
                      ->get();
        \Log::info('Sample books: ' . $allBooks->toJson());

        // Test query với điều kiện
        \Log::info('Step 3: Testing filtered query');
      $availableBooks = \DB::table('books')
    ->where(function($query) {
        $query->whereNull('discount_price')
              ->orWhere('discount_price', 0)
              ->orWhere('discount_price', '0.00');
    })
    ->where('is_physical', 1) // <-- thêm dòng này
    ->select('id', 'title', 'price', 'discount_price', 'cover_image', 'stock')
    ->orderBy('title')
    ->get();


        \Log::info('Available books count: ' . $availableBooks->count());
        
        return response()->json([
            'success' => true,
            'data' => $availableBooks,
            'total_books' => $bookCount,
            'available_count' => $availableBooks->count(),
            'message' => 'Debug: Lấy danh sách sách thành công'
        ]);

    } catch (\Throwable $e) {
        \Log::error('=== ERROR in getAvailableBooks ===');
        \Log::error('Message: ' . $e->getMessage());
        \Log::error('File: ' . $e->getFile());
        \Log::error('Line: ' . $e->getLine());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Debug error: ' . $e->getMessage(),
            'error_details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ], 500);
    }
}
}