<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
                $query->select('books.id', 'title', 'price','cover_image');
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
                        'thumb' => $book->cover_image, // ✅ lấy đúng ảnh chính
                        'title' => $book->title,
                        'price' => $book->price,
                        'discount_percent' => $book->pivot->discount_percent,
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

    public function show($id)
    {
        try {
            $event = Event::with([
                'books' => function ($query) {
                    $query->select('books.id', 'title', 'price');
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
                        'books_id' => $book->id, // Thêm books_id để tương thích với frontend
                        'title' => $book->title,
                        'price' => $book->price,
                        'cover_images' => $book->cover_image,
                        'discount_percent' => $book->pivot->discount_percent,
                        'quantity_limit' => $book->pivot->quantity_limit,
                        'sold_quantity' => $book->pivot->sold_quantity,
                        'sale_price' => $book->price - ($book->price * $book->pivot->discount_percent / 100),

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
        Event::destroy($id);
        return response()->json(['message' => 'Event deleted']);
    }
}
