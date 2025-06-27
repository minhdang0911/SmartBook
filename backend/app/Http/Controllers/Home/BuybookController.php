<?php

namespace App\Http\Controllers\Home;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuybookController extends Controller
{
    public function buyBooks(Request $request)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);

        $query = Book::where('is_physical', 1)
            ->select('id', 'title', 'cover_image');

        $total = $query->count();

        $buyBooks = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'total' => $total,
            'page' => (int) $page,
            'limit' => (int) $limit,
            'data' => $buyBooks
        ]);
    }
}
