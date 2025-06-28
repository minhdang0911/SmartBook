<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::all(); // ✅ đúng biến
        return view('admin.Coupons.index', compact('coupons')); // ✅ trùng tên
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'scope' => 'required|in:all,category,product,collection',
            'min_order_value' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'is_active' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'book_ids' => 'array',
            'book_ids.*' => 'exists:books,id',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $coupon = Coupon::create($data);

        if (!empty($data['book_ids'])) {
            $coupon->books()->sync($data['book_ids']);
        }

        if (!empty($data['category_ids'])) {
            $coupon->categories()->sync($data['category_ids']);
        }

        return response()->json($coupon->load(['books', 'categories']), 201);
    }
    public function show()
    {
        return Coupon::with(['books', 'categories'])->get();
    }


    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|in:percent,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'scope' => 'sometimes|in:all,category,product,collection',
            'min_order_value' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'is_active' => 'boolean',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'book_ids' => 'array',
            'book_ids.*' => 'exists:books,id',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $coupon->update($data);

        if (isset($data['book_ids'])) {
            $coupon->books()->sync($data['book_ids']);
        }

        if (isset($data['category_ids'])) {
            $coupon->categories()->sync($data['category_ids']);
        }

        return response()->json($coupon->load(['books', 'categories']));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully']);
    }
    public function check(Request $request)
    {
        $coupon = null;

        if ($request->filled('id')) {
            $coupon = Coupon::find($request->input('id'));
        }

        if (!$coupon && $request->filled('name')) {
            $coupon = Coupon::where('name', $request->input('name'))->first();
        }

        if (!$coupon) {
            return response()->json([
                'message' => 'Không tìm thấy mã giảm giá.'
            ], 404);
        }

        // Kiểm tra trạng thái hoạt động
        if (!$coupon->is_active) {
            return response()->json([
                'message' => 'Mã giảm giá đang bị vô hiệu hóa.'
            ], 400);
        }

        // Kiểm tra chưa đến thời gian bắt đầu
        if (now()->lt($coupon->start_date)) {
            return response()->json([
                'message' => 'Mã giảm giá chưa đến thời gian áp dụng.'
            ], 400);
        }

        // Kiểm tra đã hết hạn
        if (now()->gt($coupon->end_date)) {
            return response()->json([
                'message' => 'Mã giảm giá đã hết hạn.'
            ], 400);
        }

        return response()->json([
            'message' => 'Mã giảm giá hợp lệ.',
            'coupon' => $coupon->load(['books', 'categories']),
        ]);
    }

}
