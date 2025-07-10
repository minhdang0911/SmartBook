<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Lấy danh sách bình luận theo post_id, gồm replies
    public function index($postId)
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
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
