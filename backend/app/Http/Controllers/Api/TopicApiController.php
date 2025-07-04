<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Post;
use Illuminate\Support\Str;

class TopicApiController extends Controller
{
    public function index()
    {
        $topics = Topic::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách chủ đề thành công',
            'data' => $topics
        ]);
    }

    public function posts(Request $request, $slug)
    {
        $topic = Topic::where('slug', $slug)->first();

        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Chủ đề không tồn tại',
                'data' => null
            ], 404);
        }

        $perPage = $request->input('per_page', 10);

        $posts = Post::published()
            ->with('topics:id,name')
            ->whereHas('topics', fn($q) => $q->where('topics.id', $topic->id))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy bài viết theo chủ đề thành công',
            'data' => $posts->getCollection()->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'thumbnail' => $post->thumbnail,
                    'excerpt' => Str::limit(strip_tags($post->content), 100),
                    'created_at' => $post->created_at->format('d/m/Y'),
                    'topics' => $post->topics->map(fn($t) => [
                        'id' => $t->id,
                        'name' => $t->name
                    ])
                ];
            }),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }
}
