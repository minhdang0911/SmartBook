<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    //use App\Models\PostLike;

    public function getLikedPosts(Request $request)
    {
        $user = $request->user();

        // Lấy tất cả post_id mà user đã like
        $likedPostIds = PostLike::where('user_id', $user->id)
            ->pluck('post_id');

        // Lấy thông tin chi tiết các bài viết
        $likedPosts = Post::whereIn('id', $likedPostIds)
            ->select('id', 'title', 'slug', 'created_at', 'like_count', 'views') // 👈 chỉ lấy các field cần
            ->get();

        return response()->json([
            'success' => true,
            'data' => $likedPosts,
            'trangthai' => true,
        ]);
    }

}
