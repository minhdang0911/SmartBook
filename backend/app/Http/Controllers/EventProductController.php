<?php
namespace App\Http\Controllers;

use App\Models\EventProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventProductController extends Controller
{
    public function store(Request $request, $event_id) {
        $data = $request->validate([
            'books_id' => 'required|exists:books,id',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'quantity_limit' => 'nullable|integer|min:0',
            'sold_quantity' => 'nullable|integer|min:0',
        ]);

        $data['event_id'] = $event_id;

        $eventProduct = EventProduct::create($data);
        return response()->json($eventProduct, 201);
    }
    

    public function update(Request $request, $event_id, $book_id) {
        $eventProduct = EventProduct::where('event_id', $event_id)
            ->where('books_id', $book_id)
            ->firstOrFail();

        $eventProduct->update($request->only(['discount_percent', 'quantity_limit', 'sold_quantity']));
        return response()->json($eventProduct);
    }

    public function destroy($event_id, $book_id) {
        EventProduct::where('event_id', $event_id)
            ->where('books_id', $book_id)
            ->delete();

        return response()->json(['message' => 'Book removed from event']);
    }
}
