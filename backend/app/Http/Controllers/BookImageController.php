<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookImage;
use Illuminate\Http\Request;

class BookImageController extends Controller
{
    public function getImagesByBookId($book_id)
    {
        $images = BookImage::where('book_id', $book_id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $images
        ]);
    }
}
