<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Mặc định 10 bài mỗi trang

        $posts = Post::published()
            ->pinnedFirst()
            ->with('topics:id,name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách bài viết thành công',
            'data' => $posts->getCollection()->map(fn($post) => $this->formatPost($post)),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }


    public function show($slug)
    {
        $post = Post::published()
            ->with('topics:id,name')
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Bài viết không tồn tại',
                'data' => null
            ], 404);
        }

        $post->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết bài viết thành công',
            'data' => $this->formatPost($post, true)
        ]);
    }

    public function related($slug)
    {
        $post = Post::published()
            ->with('topics')
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Bài viết không tồn tại',
                'data' => null
            ], 404);
        }

        $topicIds = $post->topics->pluck('id');

        $relatedPosts = Post::published()
            ->with('topics:id,name')
            ->where('id', '!=', $post->id)
            ->whereHas('topics', function ($query) use ($topicIds) {
                $query->whereIn('topics.id', $topicIds);
            })
            ->latest()
            ->take(4)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách bài viết liên quan thành công',
            'data' => $relatedPosts->map(fn($p) => $this->formatPost($p))
        ]);
    }

    public function popular(Request $request)
    {
        $limit = $request->input('limit', 5);

        $posts = Post::published()
            ->with('topics:id,name')
            ->orderByDesc('views')
            ->take($limit)
            ->get();

        if ($posts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có bài viết phổ biến nào',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách bài viết phổ biến thành công',
            'data' => $posts->map(fn($post) => $this->formatPost($post))
        ]);
    }

    protected function formatPost($post, $withContent = false)
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'thumbnail' => $post->thumbnail,
            'excerpt' => $withContent ? null : Str::limit(strip_tags($post->content), 100),
            'content' => $withContent ? $post->content : null,
            'created_at' => $post->created_at->format('d/m/Y'),
            'views' => $post->views ?? null,
            'topics' => $post->topics->map(fn($topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
            ]),
        ];
    }
}
