<?php

namespace App\Http\Controllers;

use App\Models\EventProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventProductController extends Controller
{
    /**
     * Lấy % giảm tốt nhất cho book và cập nhật books.discount_price
     */
    private function recomputeAndUpdateBookDiscountPrice(int $bookId): ?float
    {
        $bookPrice = DB::table('books')->where('id', $bookId)->value('price');
        if ($bookPrice === null) {
            return null;
        }

        $bestPercent = DB::table('event_products')
            ->where('books_id', $bookId)
            ->max('discount_percent');

        if ($bestPercent === null) {
            DB::table('books')->where('id', $bookId)->update(['discount_price' => null]);
            return null;
        }

        $discountPrice = round(((float) $bookPrice) * (100 - (float) $bestPercent) / 100, 2);
        if ($discountPrice < 0) {
            $discountPrice = 0;
        }

        DB::table('books')->where('id', $bookId)->update([
            'discount_price' => $discountPrice,
        ]);

        return $discountPrice;
    }

    public function store(Request $request, int $event_id)
    {
        $data = $request->validate([
            'books_id'         => 'required|exists:books,id',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'quantity_limit'   => 'nullable|integer|min:0',
            'sold_quantity'    => 'nullable|integer|min:0',
        ]);

        $data['event_id'] = $event_id;
        $data['sold_quantity'] = $data['sold_quantity'] ?? 0;

        DB::transaction(function () use ($data) {
            DB::table('event_products')->updateOrInsert(
                ['event_id' => $data['event_id'], 'books_id' => $data['books_id']],
                [
                    'discount_percent' => $data['discount_percent'],
                    'quantity_limit'   => $data['quantity_limit'],
                    'sold_quantity'    => $data['sold_quantity'],
                ]
            );

            $this->recomputeAndUpdateBookDiscountPrice((int) $data['books_id']);
        });

        $discountPrice = DB::table('books')->where('id', $data['books_id'])->value('discount_price');

        return response()->json([
            'message'        => 'Book added/updated successfully in event',
            'event_id'       => $event_id,
            'books_id'       => $data['books_id'],
            'discount_price' => $discountPrice,
        ], 200);
    }

    public function update(Request $request, int $event_id, int $book_id)
    {
        $eventProduct = EventProduct::where('event_id', $event_id)
            ->where('books_id', $book_id)
            ->firstOrFail();

        $validated = $request->validate([
            'discount_percent' => 'sometimes|numeric|min:0|max:100',
            'quantity_limit'   => 'sometimes|nullable|integer|min:0',
            'sold_quantity'    => 'sometimes|nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($eventProduct, $validated, $book_id) {
            $eventProduct->update($validated);
            $this->recomputeAndUpdateBookDiscountPrice((int) $book_id);
        });

        $eventProduct->refresh();
        $discountPrice = DB::table('books')->where('id', $book_id)->value('discount_price');

        return response()->json([
            'event_product'  => $eventProduct,
            'discount_price' => $discountPrice,
        ]);
    }

    public function destroy(int $event_id, int $book_id)
    {
        DB::transaction(function () use ($event_id, $book_id) {
            EventProduct::where('event_id', $event_id)
                ->where('books_id', $book_id)
                ->delete();

            $this->recomputeAndUpdateBookDiscountPrice((int) $book_id);
        });

        return response()->json(['message' => 'Book removed from event']);
    }
}
