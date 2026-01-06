<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Helper: trả JSON không escape Unicode (fix \u00ea, \u1ec7...)
     */
    private function json($data, int $status = 200, array $headers = [])
    {
        return response()->json($data, $status, $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Lấy tất cả danh mục
     */
    public function index()
    {
        try {
            $categories = Category::all();

            return $this->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Lấy danh sách danh mục thành công'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tất cả danh mục kèm theo sách
     */
    public function indexWithBooks()
    {
        try {
            $categories = Category::with('books')->get();

            return $this->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Lấy danh sách danh mục kèm sách thành công'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh mục theo ID
     */
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);

            return $this->json([
                'success' => true,
                'data' => $category,
                'message' => 'Lấy thông tin danh mục thành công'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }
    }

    /**
     * Lấy danh mục theo ID kèm theo sách
     */
    public function showWithBooks($id)
    {
        try {
            $category = Category::with('books')->findOrFail($id);

            return $this->json([
                'success' => true,
                'data' => $category,
                'message' => 'Lấy thông tin danh mục kèm sách thành công'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }
    }

    /**
     * Tạo danh mục mới
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name'
            ]);

            $category = Category::create([
                'name' => $request->name
            ]);

            return $this->json([
                'success' => true,
                'data' => $category,
                'message' => 'Tạo danh mục thành công'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật danh mục
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id
            ]);

            $category->update([
                'name' => $request->name
            ]);

            return $this->json([
                'success' => true,
                'data' => $category,
                'message' => 'Cập nhật danh mục thành công'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục hoặc có lỗi xảy ra'
            ], 404);
        }
    }

    /**
     * Xóa danh mục
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Kiểm tra xem danh mục có sách nào không
            if ($category->books()->count() > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục này vì còn có sách thuộc danh mục'
                ], 400);
            }

            $category->delete();

            return $this->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công'
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }
    }
}
