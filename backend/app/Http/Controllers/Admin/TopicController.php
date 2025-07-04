<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $query = Topic::query();

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $topics = $query->latest()->paginate(10);

        return view('admin.topics.index', compact('topics'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|max:100']);

        Topic::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Thêm chủ đề thành công!');
    }

    public function update(Request $request, Topic $topic)
    {
        $request->validate(['name' => 'required|max:100']);

        $topic->update([
            'name' => $request->name,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Cập nhật chủ đề thành công!');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()->back()->with('success', 'Xoá chủ đề thành công!');
    }
}
