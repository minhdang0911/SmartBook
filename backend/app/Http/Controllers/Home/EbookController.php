<?php

namespace App\Http\Controllers\Home;


use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class EbookController extends Controller
{
public function Ebooks(Request $request)
{
    $limit = $request->query('limit', default: 10); // Mặc định là 10 nếu không truyền
    $page = $request->query('page', 1);

    $query = Book::where('is_physical', 0)
        ->select('id', 'title', 'cover_image');

    $total = $query->count();

    $ebooks = $query->skip(($page - 1) * $limit)
        ->take($limit)
        ->get();

    return response()->json([
        'status' => 'success',
        'total' => $total,
        'page' => (int) $page,
        'limit' => (int) $limit,
        'data' => $ebooks
    ]);
}


}