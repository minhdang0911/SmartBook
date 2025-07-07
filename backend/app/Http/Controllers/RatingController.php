<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;



class RatingController extends Controller
{


    /**
     * Tạo hoặc cập nhật rating cho sách
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|integer|exists:books,id',
                'rating_star' => [
                    'required',
                    'numeric',
                    'min:0.5',
                    'max:5',
                    function ($attribute, $value, $fail) {
                        // Kiểm tra rating chỉ được phép là bội số của 0.5
                        if (fmod($value * 2, 1) != 0) {
                            $fail('Điểm đánh giá chỉ được phép là 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5 hoặc 5.');
                        }
                    }
                ],
                'comment' => 'nullable|string|max:1000' // Thêm comment cho đánh giá
            ], [
                'book_id.required' => 'ID sách là bắt buộc.',
                'book_id.exists' => 'Sách không tồn tại.',
                'rating_star.required' => 'Điểm đánh giá là bắt buộc.',
                'rating_star.numeric' => 'Điểm đánh giá phải là số.',
                'rating_star.min' => 'Điểm đánh giá tối thiểu là 0.5 sao.',
                'rating_star.max' => 'Điểm đánh giá tối đa là 5 sao.',
                'comment.max' => 'Bình luận không được vượt quá 1000 ký tự.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không thể xác thực người dùng'
                ], 401);
            }

            $bookId = $request->book_id;
            $ratingStar = $request->rating_star;
            $comment = $request->comment;

            // Kiểm tra xem user đã rating cho sách này chưa
            $existingRating = Rating::where('book_id', $bookId)
                ->where('user_id', $userId)
                ->first();

            if ($existingRating) {
                // Cập nhật rating hiện tại
                $existingRating->update([
                    'rating_star' => $ratingStar,
                    'comment' => $comment
                ]);

                // ✅ Cập nhật lại trung bình
                $book = Book::find($bookId);
                $book->updateRatingAvg();

                return response()->json([
                    'status' => true,
                    'message' => 'Cập nhật đánh giá thành công!',
                    'data' => [
                        'rating_id' => $existingRating->id,
                        'book_id' => $bookId,
                        'user_id' => $userId,
                        'rating_star' => $this->formatRating($ratingStar),
                        'comment' => $comment,
                        'updated_at' => $existingRating->updated_at
                    ]
                ], 200);
            } else {
                $book = Book::find($bookId);
                // Tạo rating mới
                $rating = Rating::create([
                    'book_id' => $bookId,
                    'user_id' => $userId,
                    'rating_star' => $ratingStar,
                    'comment' => $comment
                ]);
                $book->updateRatingAvg();

                return response()->json([
                    'status' => true,
                    'message' => 'Đánh giá thành công!',
                    'data' => [
                            'rating_id' => $rating->id,
                            'book_id' => $bookId,
                            'user_id' => $userId,
                            'rating_star' => $this->formatRating($ratingStar),
                            'comment' => $comment,
                            'created_at' => $rating->created_at
                        ]
                ], 201);
            }

        } catch (\Exception $e) {
            \Log::error('Rating Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy rating của user cho một sách cụ thể
     */
    public function getUserRating($bookId)
    {
        try {
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sách không tồn tại.'
                ], 404);
            }

            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không thể xác thực người dùng'
                ], 401);
            }

            $rating = Rating::where('book_id', $bookId)
                ->where('user_id', $userId)
                ->first();

            if (!$rating) {
                return response()->json([
                    'status' => true,
                    'message' => 'Bạn chưa đánh giá sách này.',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => true,
                'data' => [
                        'rating_id' => $rating->id,
                        'book_id' => $rating->book_id,
                        'user_id' => $rating->user_id,
                        'rating_star' => $this->formatRating($rating->rating_star),
                        'comment' => $rating->comment,
                        'created_at' => $rating->created_at,
                        'updated_at' => $rating->updated_at
                    ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get User Rating Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tất cả ratings của user hiện tại
     */
    public function getUserRatings()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không thể xác thực người dùng'
                ], 401);
            }

            $ratings = Rating::with('book:id,title,author')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'user_id' => $userId,
                'data' => $ratings->map(function ($rating) {
                    return [
                        'rating_id' => $rating->id,
                        'book_id' => $rating->book_id,
                        'user_id' => $rating->user_id,
                        'book_title' => $rating->book->title ?? 'N/A',
                        'book_author' => $rating->book->author ?? 'N/A',
                        'rating_star' => $this->formatRating($rating->rating_star),
                        'comment' => $rating->comment,
                        'created_at' => $rating->created_at,
                        'updated_at' => $rating->updated_at
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get User Ratings Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa rating của user
     */
    public function destroy($bookId)
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không thể xác thực người dùng'
                ], 401);
            }

            $rating = Rating::where('book_id', $bookId)
                ->where('user_id', $userId)
                ->first();

            if (!$rating) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy đánh giá để xóa.'
                ], 404);
            }

            $rating->delete();

            return response()->json([
                'status' => true,
                'message' => 'Xóa đánh giá thành công!',
                'data' => [
                        'user_id' => $userId,
                        'book_id' => $bookId
                    ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Delete Rating Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê rating cho một sách (Google Style)
     */
    public function getBookRatingStats($bookId)
    {
        try {
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sách không tồn tại.'
                ], 404);
            }

            $ratings = Rating::where('book_id', $bookId)->get();
            $totalRatings = $ratings->count();

            if ($totalRatings == 0) {
                return response()->json([
                    'status' => true,
                    'data' => [
                            'book_id' => $bookId,
                            'total_ratings' => 0,
                            'average_rating' => 0,
                            'average_display' => '0.0',
                            'star_distribution' => [
                                    '5' => ['count' => 0, 'percentage' => 0],
                                    '4' => ['count' => 0, 'percentage' => 0],
                                    '3' => ['count' => 0, 'percentage' => 0],
                                    '2' => ['count' => 0, 'percentage' => 0],
                                    '1' => ['count' => 0, 'percentage' => 0]
                                ],
                            'detailed_breakdown' => [
                                '5.0' => 0,
                                '4.5' => 0,
                                '4.0' => 0,
                                '3.5' => 0,
                                '3.0' => 0,
                                '2.5' => 0,
                                '2.0' => 0,
                                '1.5' => 0,
                                '1.0' => 0,
                                '0.5' => 0
                            ]
                        ]
                ], 200);
            }

            $averageRating = $ratings->avg('rating_star');

            // Tính phân bố sao theo kiểu Google (1-5 sao)
            $starDistribution = [];
            for ($i = 5; $i >= 1; $i--) {
                $count = $ratings->filter(function ($rating) use ($i) {
                    return floor($rating->rating_star) == $i ||
                        ($i == 5 && $rating->rating_star >= 4.5) ||
                        ($i == 4 && $rating->rating_star >= 3.5 && $rating->rating_star < 4.5) ||
                        ($i == 3 && $rating->rating_star >= 2.5 && $rating->rating_star < 3.5) ||
                        ($i == 2 && $rating->rating_star >= 1.5 && $rating->rating_star < 2.5) ||
                        ($i == 1 && $rating->rating_star < 1.5);
                })->count();

                $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100, 1) : 0;

                $starDistribution[$i] = [
                    'count' => $count,
                    'percentage' => $percentage
                ];
            }

            // Chi tiết từng mức rating
            $detailedBreakdown = [
                '5.0' => $ratings->where('rating_star', 5.0)->count(),
                '4.5' => $ratings->where('rating_star', 4.5)->count(),
                '4.0' => $ratings->where('rating_star', 4.0)->count(),
                '3.5' => $ratings->where('rating_star', 3.5)->count(),
                '3.0' => $ratings->where('rating_star', 3.0)->count(),
                '2.5' => $ratings->where('rating_star', 2.5)->count(),
                '2.0' => $ratings->where('rating_star', 2.0)->count(),
                '1.5' => $ratings->where('rating_star', 1.5)->count(),
                '1.0' => $ratings->where('rating_star', 1.0)->count(),
                '0.5' => $ratings->where('rating_star', 0.5)->count()
            ];

            return response()->json([
                'status' => true,
                'data' => [
                    'book_id' => $bookId,
                    'total_ratings' => $totalRatings,
                    'average_rating' => round($averageRating, 2),
                    'average_display' => number_format($averageRating, 1),
                    'star_distribution' => $starDistribution,
                    'detailed_breakdown' => $detailedBreakdown
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get Book Rating Stats Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách đánh giá theo số sao cụ thể (Google Style)
     */
    public function getRatingsByStar(Request $request, $bookId)
    {
        try {
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sách không tồn tại.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'star_level' => 'nullable|integer|min:1|max:5', // Đổi thành nullable
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $starLevel = $request->get('star_level');
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);

            $query = Rating::with(['user:id,name,avatar'])
                ->where('book_id', $bookId);

            // Nếu không truyền star_level, lấy comment ưu tiên 5 sao và giới hạn 5 kết quả
            if (is_null($starLevel)) {
                $limit = 5; // Force limit = 5 khi không truyền star_level
                $page = 1;  // Force page = 1

                // Sắp xếp ưu tiên 5 sao trước, sau đó theo thời gian mới nhất
                $query->orderBy('rating_star', 'desc')
                    ->orderBy('created_at', 'desc');

                $total = $query->count();
                $ratings = $query->take($limit)->get();

                return response()->json([
                    'status' => true,
                    'data' => [
                        'book_id' => $bookId,
                        'mode' => 'priority_5_star', // Thêm mode để frontend biết
                        'star_level' => null,
                        'current_page' => 1,
                        'per_page' => $limit,
                        'total' => $total,
                        'total_pages' => 1,
                        'ratings' => $ratings->map(function ($rating) {
                            return [
                                'rating_id' => $rating->id,
                                'user_name' => $rating->user->name ?? 'Ẩn danh',
                                'user_avatar' => $rating->user->avatar ?? null,
                                'rating_star' => $this->formatRating($rating->rating_star),
                                'comment' => $rating->comment,
                                'created_at' => $rating->created_at->format('d/m/Y H:i'),
                                'time_ago' => $rating->created_at->diffForHumans()
                            ];
                        })
                    ]
                ], 200);
            }

            // Logic cũ khi có truyền star_level
            $query->where(function ($q) use ($starLevel) {
                if ($starLevel == 5) {
                    $q->where('rating_star', '>=', 4.5);
                } elseif ($starLevel == 4) {
                    $q->where('rating_star', '>=', 3.5)
                        ->where('rating_star', '<', 4.5);
                } elseif ($starLevel == 3) {
                    $q->where('rating_star', '>=', 2.5)
                        ->where('rating_star', '<', 3.5);
                } elseif ($starLevel == 2) {
                    $q->where('rating_star', '>=', 1.5)
                        ->where('rating_star', '<', 2.5);
                } else { // $starLevel == 1
                    $q->where('rating_star', '<', 1.5);
                }
            })->orderBy('created_at', 'desc');

            $total = $query->count();
            $ratings = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'book_id' => $bookId,
                    'mode' => 'filter_by_star', // Thêm mode để frontend biết
                    'star_level' => $starLevel,
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                    'ratings' => $ratings->map(function ($rating) {
                        return [
                            'rating_id' => $rating->id,
                            'user_name' => $rating->user->name ?? 'Ẩn danh',
                            'user_avatar' => $rating->user->avatar ?? null,
                            'rating_star' => $this->formatRating($rating->rating_star),
                            'comment' => $rating->comment,
                            'created_at' => $rating->created_at->format('d/m/Y H:i'),
                            'time_ago' => $rating->created_at->diffForHumans()
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get Ratings By Star Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tất cả đánh giá của một sách với phân trang
     */
    public function getAllBookRatings(Request $request, $bookId)
    {
        try {
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sách không tồn tại.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:50',
                'sort' => 'in:newest,oldest,highest,lowest'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $sort = $request->get('sort', 'newest');

            $query = Rating::with(['user:id,name,avatar'])
                ->where('book_id', $bookId);

            // Sắp xếp
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest':
                    $query->orderBy('rating_star', 'desc')->orderBy('created_at', 'desc');
                    break;
                case 'lowest':
                    $query->orderBy('rating_star', 'asc')->orderBy('created_at', 'desc');
                    break;
                default: // newest
                    $query->orderBy('created_at', 'desc');
            }

            $total = $query->count();
            $ratings = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'book_id' => $bookId,
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                    'sort' => $sort,
                    'ratings' => $ratings->map(function ($rating) {
                        return [
                            'rating_id' => $rating->id,
                            'user_name' => $rating->user->name ?? 'Ẩn danh',
                            'user_avatar' => $rating->user->avatar ?? null,
                            'rating_star' => $this->formatRating($rating->rating_star),
                            'comment' => $rating->comment,
                            'created_at' => $rating->created_at->format('d/m/Y H:i'),
                            'time_ago' => $rating->created_at->diffForHumans()
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get All Book Ratings Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method để format rating display
     */
    private function formatRating($rating)
    {
        return $rating == floor($rating) ? (int) $rating : $rating;
    }
}