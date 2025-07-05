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
        $request->validate([
            'name' => 'required|max:100',
        ], [
            'name.required' => 'Vui lòng nhập tên chủ đề.',
            'name.max' => 'Tên chủ đề không được vượt quá 100 ký tự.',
        ]);

        // Lấy slug từ request nếu có, không thì tạo từ name
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        if (Topic::where('slug', $slug)->exists()) {
            return redirect()->back()
                ->withErrors(['name' => 'Slug đã tồn tại.'])
                ->withInput()
                ->with('_form', 'add');
        }

        Topic::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->back()->with('success', 'Thêm chủ đề thành công!');
    }

    public function update(Request $request, Topic $topic)
    {
        $request->validate([
            'name' => 'required|max:100',
        ], [
            'name.required' => 'Vui lòng nhập tên chủ đề.',
            'name.max' => 'Tên chủ đề không được vượt quá 100 ký tự.',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        if (Topic::where('slug', $slug)->where('id', '!=', $topic->id)->exists()) {
            return redirect()->back()
                ->withErrors(['name' => 'Slug đã tồn tại.'])
                ->withInput()
                ->with('_form', 'edit')
                ->with('_edit_id', $topic->id);
        }

        $topic->update([
            'name' => $request->name,
            'slug' => $slug,
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
