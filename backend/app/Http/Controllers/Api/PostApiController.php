<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PostLike;


class PostApiController extends Controller
{
    // Danh sách bài viết có lọc, tìm kiếm, sắp xếp
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $keyword = $request->input('keyword');
        $topicId = $request->input('topic_id');
        $sortBy = $request->input('sort_by', 'default'); // views | pinned | default

        $query = Post::published()->with('topics:id,name');

        // Tìm theo từ khoá trong tiêu đề hoặc nội dung
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                    ->orWhere('content', 'like', "%$keyword%");
            });
        }

        // Lọc theo chủ đề
        if ($topicId) {
            $query->whereHas('topics', fn($q) => $q->where('topics.id', $topicId));
        }

        // Sắp xếp
        switch ($sortBy) {
            case 'views':
                $query->orderByDesc('views');
                break;
            case 'pinned':
                $query->orderByDesc('is_pinned')->latest();
                break;
            default:
                $query->latest();
                break;
        }

        $posts = $query->paginate($perPage);

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

    // Bài viết phổ biến (views cao)
    public function popular(Request $request)
    {
        $limit = $request->input('limit', 5);

        $posts = Post::published()
            ->with('topics:id,name')
            ->orderByDesc('views')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy bài viết phổ biến thành công',
            'data' => $posts->map(fn($post) => $this->formatPost($post))
        ]);
    }

    // Bài viết đã ghim
    public function pinned(Request $request)
    {
        $limit = $request->input('limit', 4);

        $posts = Post::published()
            ->where('is_pinned', true)
            ->with('topics:id,name')
            ->latest()
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách bài viết đã ghim thành công',
            'data' => $posts->map(fn($post) => $this->formatPost($post))
        ]);
    }

    // Bài viết nổi bật (nhiều like nhất)
    public function featured(Request $request)
    {
        $limit = $request->input('limit', 4); // mặc định lấy 4 bài

        $posts = Post::published()
            ->with('topics:id,name')
            ->orderByDesc('like_count')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy bài viết nổi bật thành công',
            'data' => $posts->map(fn($post) => $this->formatPost($post))
        ]);
    }

    // Chi tiết bài viết
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

    // Bài viết liên quan theo chủ đề
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
            ->whereHas('topics', function ($q) use ($topicIds) {
                $q->whereIn('topics.id', $topicIds);
            })
            ->orderByDesc('views')
            ->take(4)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy bài viết liên quan thành công',
            'data' => $relatedPosts->map(fn($p) => $this->formatPost($p))
        ]);
    }

    // Like bài viết
    public function like(Post $post)
    {

        $userId = auth()->id();

        // Tìm record kể cả đã bị xóa mềm
        $existing = PostLike::withTrashed()
            ->where('user_id', $userId)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                // Nếu đã bị soft delete → khôi phục
                $existing->restore();
                $post->increment('like_count');

                return response()->json([
                    'success' => true,
                    'message' => 'Đã like lại bài viết!',
                ]);
            }

            // Nếu đã like rồi và chưa xoá
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã like bài viết này rồi',
            ]);
        }

        // Chưa like lần nào → tạo mới
        PostLike::create([
            'user_id' => $userId,
            'post_id' => $post->id,
        ]);

        $post->increment('like_count');

        return response()->json([
            'success' => true,
            'message' => 'Đã like bài viết thành công!',
        ]);
    }

    // Unlike bài viết
    public function unlike(Request $request, $postId)
    {
        $user = $request->user();

        $like = PostLike::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->first();

        if (!$like) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa like bài viết này.'
            ], 400);
        }

        // Xoá like (soft delete)
        $like->delete();

        // Giảm like_count, đảm bảo không âm
        $post = Post::published()->find($postId);
        if ($post && $post->like_count > 0) {
            $post->decrement('like_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã bỏ like bài viết.'
        ]);
    }

    // Format lại bài viết để return
    protected function formatPost($post, $withContent = false)
    {
        $user = auth()->user();

        // Kiểm tra đã like chưa (nếu có user đăng nhập)
        $hasLiked = false;
        if ($user) {
            $hasLiked = \App\Models\PostLike::where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->exists();
        }

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'thumbnail' => $post->thumbnail,
            'excerpt' => $withContent ? null : Str::limit(strip_tags($post->content), 100),
            'content' => $withContent ? $post->content : null,
            'created_at' => $post->created_at->format('d/m/Y'),
            'views' => $post->views ?? 0,
            'is_pinned' => (bool) $post->is_pinned,
            'like_count' => $post->like_count ?? 0,
            'has_liked' => $hasLiked,
            'topics' => $post->topics->map(fn($topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
            ]),
        ];
    }
}
