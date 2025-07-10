<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommentReaction;

class CommentReactionController extends Controller
{
    public function react(Request $request, $id)
    {
        $request->validate([
            'reaction_type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        $reaction = CommentReaction::updateOrCreate(
            ['user_id' => $request->user()->id, 'comment_id' => $id],
            ['reaction_type' => $request->reaction_type]
        );

        return response()->json(['success' => true, 'data' => $reaction]);
    }

    public function unreact(Request $request, $id)
    {
        $deleted = CommentReaction::where('user_id', $request->user()->id)
            ->where('comment_id', $id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Bỏ cảm xúc']);
    }

    public function listReactions($id)
    {
        $reactions = CommentReaction::where('comment_id', $id)
            ->select('reaction_type', \DB::raw('COUNT(*) as count'))
            ->groupBy('reaction_type')
            ->get();

        return response()->json(['success' => true, 'data' => $reactions]);
    }
}
