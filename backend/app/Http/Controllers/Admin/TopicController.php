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
        // Generate slug trước để validate
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $request->merge(['slug' => $slug]);

        $request->validate([
            'name' => 'required|max:100|unique:topics,name',
            'slug' => 'required|unique:topics,slug',
        ], [
            'name.required' => 'Vui lòng nhập tên chủ đề.',
            'name.max' => 'Tên chủ đề không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên chủ đề đã tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
        ]);

        Topic::create([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->back()->with('success', 'Thêm chủ đề thành công!');
    }

    public function update(Request $request, Topic $topic)
    {
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $request->merge(['slug' => $slug]);

        $request->validate([
            'name' => 'required|max:100|unique:topics,name,' . $topic->id,
            'slug' => 'required|unique:topics,slug,' . $topic->id,
        ], [
            'name.required' => 'Vui lòng nhập tên chủ đề.',
            'name.max' => 'Tên chủ đề không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên chủ đề đã tồn tại.',
            'slug.unique' => 'Slug đã tồn tại.',
        ]);

        $topic->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->back()->with('success', 'Cập nhật chủ đề thành công!');
    }

    public function destroy(Topic $topic)
    {
        if ($topic->posts()->exists()) {
            return redirect()->back()->with('error', 'Không thể xoá chủ đề vì đang được sử dụng trong bài viết.');
        }

        $topic->delete();

        return redirect()->back()->with('success', 'Đã xoá chủ đề thành công!');
    }
}
