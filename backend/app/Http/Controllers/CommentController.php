<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Lấy danh sách bình luận theo post_id, gồm replies

    public function index(Request $request)
    {
        $postId = $request->query('post_id');

        $comments = Comment::with([
            'user:id,name,avatar',
            'reactions.user:id,name'
        ])
            ->withCount('replies')
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->latest()
            ->get();

        // Transform comments để có format mong muốn
        $comments->transform(function ($comment) {
            // Rút gọn user info
            $comment->user = [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->avatar,
            ];

            // Tạo reactions data array
            $reactionsData = [];
            $reactionCounts = [];

            foreach ($comment->reactions as $reaction) {
                // Thêm vào reactions data
                $reactionsData[] = [
                    'id' => $reaction->id,
                    'user_id' => $reaction->user->id,
                    'type' => $reaction->reaction_type,
                    'user' => [
                        'id' => $reaction->user->id,
                        'name' => $reaction->user->name,
                    ]
                ];

                // Đếm số lượng từng loại reaction
                $type = $reaction->reaction_type;
                if (!isset($reactionCounts[$type])) {
                    $reactionCounts[$type] = 0;
                }
                $reactionCounts[$type]++;
            }

            // Xóa reactions gốc và thêm format mới
            unset($comment->reactions);

            // Thêm reactions với format mong muốn
            $comment->reactions = [
                'data' => $reactionsData
            ];

            // Thêm các count fields (loveCount, likeCount, etc.)
            foreach ($reactionCounts as $type => $count) {
                $comment->{$type . 'Count'} = $count;
            }

            // Hoặc bạn có thể thêm reaction_summary như cũ
            $comment->reaction_summary = $reactionCounts;

            return $comment;
        });

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    public function replies(Request $request)
    {
        $parentId = $request->query('parent_id');

        $replies = Comment::with(['user', 'reactions']) // 👈 thêm reactions
            ->where('parent_id', $parentId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $replies
        ]);
    }



    // Thêm mới comment hoặc reply
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bình luận đã được thêm',
            'data' => $comment
        ]);
    }

    // Cập nhật nội dung comment
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $request->validate(['content' => 'required|string']);

        $comment->update(['content' => $request->content]);

        return response()->json(['success' => true, 'message' => 'Đã cập nhật', 'data' => $comment]);
    }

    // Xoá comment (soft delete)
    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $comment->delete();

        return response()->json(['success' => true, 'message' => 'Đã xoá bình luận']);
    }
}
