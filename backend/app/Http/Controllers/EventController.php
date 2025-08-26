<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Book;
use Illuminate\Http\Request;
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
                'event_id'   => $event->event_id,
                'event_name' => $event->event_name,
                'start_date' => $event->start_date,
                'end_date'   => $event->end_date,
                'status'     => $event->status,
                'books'      => $event->books->map(function ($book) {
                    return [
                        'id'              => $book->id,
                        'thumb'           => $book->cover_image,
                        'title'           => $book->title,
                        'price'           => $book->price,
                        'discount_price'  => $book->discount_price,
                        'quantity_limit'  => $book->pivot->quantity_limit,
                        'sold_quantity'   => $book->pivot->sold_quantity,
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
            'end_date'   => 'required|date|after_or_equal:start_date',
            'status'     => 'in:active,inactive'
        ]);

        $event = Event::create($data);
        return response()->json($event, 201);
    }

    /**
     * Thêm sách vào sự kiện với giá giảm
     * Quy ước: 0.00 = không giảm giá (KHÔNG dùng null)
     */
    public function addBookToEvent(Request $request, $eventId)
    {
        $data = $request->validate([
            'book_id'          => 'required|exists:books,id',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'quantity_limit'   => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $book  = Book::findOrFail($data['book_id']);
            $event = Event::findOrFail($eventId);

            // Không cho thêm nếu sách đã có discount_price > 0
            if ((float)$book->discount_price > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sách này đã có giá giảm, không thể thêm vào sự kiện'
                ], 400);
            }

            // Không cho trùng cùng event
            if ($event->books()->where('books.id', $data['book_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sách đã có trong sự kiện này'
                ], 400);
            }

            // Tính giá sau giảm
            $discountPrice = round($book->price - ($book->price * $data['discount_percent'] / 100), 2);
            if ($discountPrice < 0) {
                $discountPrice = 0.00;
            }

            // Cập nhật discount_price trong bảng books (dùng 0.00 nếu không giảm)
            $book->update(['discount_price' => $discountPrice]);

            // Thêm pivot
            $event->books()->attach($data['book_id'], [
                'quantity_limit' => $data['quantity_limit'],
                'sold_quantity'  => 0
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thêm sách vào sự kiện thành công',
                'data'    => [
                    'book_id'          => $book->id,
                    'original_price'   => $book->price,
                    'discount_price'   => $discountPrice,
                    'discount_percent' => (float)$data['discount_percent']
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
     * Xóa sách khỏi sự kiện và reset discount_price = 0.00 (KHÔNG set null)
     */
    public function removeBookFromEvent($eventId, $bookId)
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($eventId);
            $book  = Book::findOrFail($bookId);

            // Xóa khỏi event (pivot)
            $event->books()->detach($bookId);

            // Reset discount_price về 0.00 (tránh NOT NULL lỗi)
            $book->update(['discount_price' => 0.00]);

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
                'event_id'    => $event->event_id,
                'event_name'  => $event->event_name,
                'start_date'  => $event->start_date,
                'end_date'    => $event->end_date,
                'status'      => $event->status,
                'created_at'  => $event->created_at,
                'updated_at'  => $event->updated_at,
                'books'       => $event->books->map(function ($book) {
                    $dp = (float)$book->discount_price;
                    $discountPercent = $dp > 0
                        ? round((($book->price - $dp) / $book->price) * 100, 2)
                        : 0.0;

                    return [
                        'id'               => $book->id,
                        'books_id'         => $book->id, // giữ field cũ nếu FE đang dùng
                        'title'            => $book->title,
                        'price'            => $book->price,
                        'discount_price'   => $book->discount_price,
                        'cover_images'     => $book->cover_image,
                        'quantity_limit'   => $book->pivot->quantity_limit,
                        'sold_quantity'    => $book->pivot->sold_quantity,
                        'discount_percent' => $discountPercent,
                    ];
                }),
                'total_products' => $event->books->count(),
                'total_sold'     => $event->books->sum('pivot.sold_quantity'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Event details retrieved successfully',
                'data'    => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'error'   => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->update($request->only(['event_name', 'start_date', 'end_date', 'status']));
        return response()->json($event);
    }

    /**
     * Xóa event:
     * 1) Reset discount_price = 0.00 cho tất cả sách
     * 2) Detach pivot
     * 3) Xóa event
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $event = Event::with('books:id')->findOrFail($id);

            $bookIds = $event->books->pluck('id')->all();

            if (!empty($bookIds)) {
                Book::whereIn('id', $bookIds)->update(['discount_price' => 0.00]);
                $event->books()->detach($bookIds);
            }

            $event->delete();

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
     * Lấy danh sách sách có thể thêm vào event (chưa có giảm giá)
     * Dùng chuẩn 0.00 = không giảm
     */
    public function getAvailableBooks()
    {
        try {
            $availableBooks = DB::table('books')
                ->where(function ($q) {
                    $q->where('discount_price', 0)
                      ->orWhere('discount_price', '0.00');
                })
                ->where('is_physical', 1)
                ->select('id', 'title', 'price', 'discount_price', 'cover_image', 'stock')
                ->orderBy('title')
                ->get();

            return response()->json([
                'success'          => true,
                'data'             => $availableBooks,
                'available_count'  => $availableBooks->count(),
                'message'          => 'Lấy danh sách sách thành công'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Debug error: ' . $e->getMessage(),
                'error_details' => [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }
}
