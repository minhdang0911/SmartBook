<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['topics'])->latest();

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('topic_id')) {
            $query->whereHas('topics', function ($q) use ($request) {
                $q->where('topics.id', $request->topic_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

       $posts = $query->paginate(9)->withQueryString();

        $topics = Topic::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'topics'));
    }


    public function create()
    {
        $topics = Topic::orderBy('name')->get();
        return view('admin.posts.create', compact('topics'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|max:255',
            'slug'      => 'nullable|max:255|unique:posts,slug',
            'excerpt'   => 'nullable',
            'content'   => 'nullable',
            'status'    => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|max:5120',
            'topics'    => 'nullable|array',
            'topics.*'  => 'exists:topics,id',
        ], [
            'title.required'      => 'Vui lòng nhập tiêu đề.',
            'title.max'           => 'Tiêu đề không được vượt quá 255 ký tự.',
            'slug.unique'         => 'Slug đã tồn tại, vui lòng chọn slug khác.',
            'thumbnail.image'     => 'File tải lên phải là hình ảnh.',
            'thumbnail.max'       => 'Ảnh không được vượt quá 5MB.',
            'topics.*.exists'     => 'Chủ đề không hợp lệ.',
            'status.required'     => 'Vui lòng chọn trạng thái.',
            'status.in'           => 'Trạng thái không hợp lệ.',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        // Upload thumbnail
        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $cloudinary = new CloudinaryService();
            $thumbnailUrl = $cloudinary->uploadImage($request->file('thumbnail'), 'thumbnails');
        }

        $post = Post::create([
            'title'     => $request->title,
            'slug'      => $slug,
            'excerpt'   => $request->excerpt,
            'content'   => $request->content,
            'is_pinned' => $request->boolean('is_pinned'),
            'status'    => $request->status,
            'thumbnail' => $thumbnailUrl,
        ]);

        $post->topics()->attach($request->topics ?? []);

        return redirect()->route('admin.posts.index')->with('success', 'Thêm bài viết thành công!');
    }

    public function edit(Post $post)
    {
        $topics = Topic::orderBy('name')->get();
        $selectedTopics = $post->topics->pluck('id')->toArray();

        return view('admin.posts.edit', compact('post', 'topics', 'selectedTopics'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title'     => 'required|max:255',
            'slug'      => 'nullable|max:255|unique:posts,slug,' . $post->id,
            'excerpt'   => 'nullable',
            'content'   => 'nullable',
            'status'    => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|max:5120',
            'topics'    => 'nullable|array',
            'topics.*'  => 'exists:topics,id',
        ], [
            'title.required'      => 'Vui lòng nhập tiêu đề.',
            'title.max'           => 'Tiêu đề không được vượt quá 255 ký tự.',
            'slug.unique'         => 'Slug đã tồn tại, vui lòng chọn slug khác.',
            'thumbnail.image'     => 'File tải lên phải là hình ảnh.',
            'thumbnail.max'       => 'Ảnh không được vượt quá 5MB.',
            'topics.*.exists'     => 'Chủ đề không hợp lệ.',
            'status.required'     => 'Vui lòng chọn trạng thái.',
            'status.in'           => 'Trạng thái không hợp lệ.',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        // Update thumbnail
        $thumbnailUrl = $post->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $cloudinary = new CloudinaryService();
            if ($thumbnailUrl) {
                $cloudinary->deleteImageByPublicId($thumbnailUrl);
            }
            $thumbnailUrl = $cloudinary->uploadImage($request->file('thumbnail'), 'thumbnails');
        }

        $post->update([
            'title'     => $request->title,
            'slug'      => $slug,
            'excerpt'   => $request->excerpt,
            'content'   => $request->content,
            'is_pinned' => $request->boolean('is_pinned'),
            'status'    => $request->status,
            'thumbnail' => $thumbnailUrl,
        ]);

        $post->topics()->sync($request->topics ?? []);

        return redirect()->route('admin.posts.index')->with('success', 'Cập nhật bài viết thành công!');
    }

    public function destroy(Post $post)
    {
        if ($post->thumbnail) {
            $cloudinary = new CloudinaryService();
            $cloudinary->deleteImageByPublicId($post->thumbnail);
        }

        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Xoá bài viết thành công!');
    }
}
