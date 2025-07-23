<?php
namespace App\Http\Controllers;

use App\Models\EventProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventProductController extends Controller
{
    public function store(Request $request, $event_id)
    {
        $data = $request->validate([
            'books_id' => 'required|exists:books,id',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'quantity_limit' => 'nullable|integer|min:0',
            'sold_quantity' => 'nullable|integer|min:0',
        ]);

        $data['event_id'] = $event_id;

        // Bổ sung mặc định nếu không truyền
        $data['sold_quantity'] = $data['sold_quantity'] ?? 0;

        \DB::table('event_products')->updateOrInsert(
            [
                'event_id' => $event_id,
                'books_id' => $data['books_id'],
            ],
            [
                'discount_percent' => $data['discount_percent'],
                'quantity_limit' => $data['quantity_limit'],
                'sold_quantity' => $data['sold_quantity'],
            ]
        );

        return response()->json([
            'message' => 'Book added/updated successfully in event',
            'event_id' => $event_id,
            'books_id' => $data['books_id']
        ], 200);
    }


    public function update(Request $request, $event_id, $book_id)
    {
        $eventProduct = EventProduct::where('event_id', $event_id)
            ->where('books_id', $book_id)
            ->firstOrFail();

        $eventProduct->update($request->only(['discount_percent', 'quantity_limit', 'sold_quantity']));
        return response()->json($eventProduct);
    }

    public function destroy($event_id, $book_id)
    {
        EventProduct::where('event_id', $event_id)
            ->where('books_id', $book_id)
            ->delete();

        return response()->json(['message' => 'Book removed from event']);
    }
}
