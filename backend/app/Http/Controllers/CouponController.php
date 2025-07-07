<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::all();
        return view('admin.Coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        try {
            // Validate input data
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:coupons,name',
                'description' => 'nullable|string',
                'discount_type' => 'required|in:percent,fixed',
                'discount_value' => 'required|numeric|min:0',
                'scope' => 'required|in:all,product,category', // ✅ đã sửa
                'min_order_value' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'book_ids' => 'nullable|array',
                'book_ids.*' => 'exists:books,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'exists:categories,id',
            ]);


            // Additional validation based on discount type
            if ($data['discount_type'] === 'percent' && $data['discount_value'] > 100) {
                throw ValidationException::withMessages([
                    'discount_value' => 'Giá trị giảm giá phần trăm không được vượt quá 100%.'
                ]);
            }

            // Set default values
            $data['is_active'] = $data['is_active'] ?? true;
            $data['used_count'] = 0;

            DB::beginTransaction();

            try {
                // Create coupon
                $coupon = Coupon::create($data);

                // Sync relationships if provided
                if (isset($data['book_ids']) && !empty($data['book_ids'])) {
                    $coupon->books()->sync($data['book_ids']);
                }

                if (isset($data['category_ids']) && !empty($data['category_ids'])) {
                    $coupon->categories()->sync($data['category_ids']);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Tạo mã giảm giá thành công.',
                    'coupon' => $coupon->load(['books', 'categories'])
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        try {
            $coupons = Coupon::with(['books', 'categories'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách mã giảm giá thành công.',
                'coupons' => $coupons
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Coupon $coupon)
    {
        try {
            // Validate input data
            $data = $request->validate([
                'name' => 'sometimes|string|max:255|unique:coupons,name,' . $coupon->id,
                'description' => 'nullable|string',
                'discount_type' => 'sometimes|in:percent,fixed',
                'discount_value' => 'sometimes|numeric|min:0',
                'scope' => 'required|in:all,category,product,collection,book',

                'min_order_value' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'book_ids' => 'nullable|array',
                'book_ids.*' => 'exists:books,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'exists:categories,id',

            ]);

            // Additional validation based on discount type
            if (
                isset($data['discount_type']) && $data['discount_type'] === 'percent' &&
                isset($data['discount_value']) && $data['discount_value'] > 100
            ) {
                throw ValidationException::withMessages([
                    'discount_value' => 'Giá trị giảm giá phần trăm không được vượt quá 100%.'
                ]);
            }

            // Check if coupon is already being used
            if ($coupon->used_count > 0 && isset($data['discount_value'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi giá trị giảm giá của mã đã được sử dụng.'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Update coupon
                $coupon->update($data);

                // Sync relationships if provided
                if (isset($data['book_ids'])) {
                    $coupon->books()->sync($data['book_ids']);
                }

                if (isset($data['category_ids'])) {
                    $coupon->categories()->sync($data['category_ids']);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật mã giảm giá thành công.',
                    'coupon' => $coupon->load(['books', 'categories'])
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Coupon $coupon)
    {
        try {
            // Check if coupon is being used
            if ($coupon->used_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa mã giảm giá đã được sử dụng.'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Detach all relationships
                $coupon->books()->detach();
                $coupon->categories()->detach();

                // Delete coupon
                $coupon->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Xóa mã giảm giá thành công.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function check(Request $request)
    {
        try {
            $coupon = null;

            if ($request->filled('id')) {
                $coupon = Coupon::find($request->input('id'));
            }

            if (!$coupon && $request->filled('name')) {
                $coupon = Coupon::where('name', $request->input('name'))->first();
            }

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã giảm giá.'
                ], 404);
            }

            // Check if coupon is active
            if (!$coupon->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đang bị vô hiệu hóa.'
                ], 400);
            }

            // Check if coupon has not started yet
            if (now()->lt($coupon->start_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá chưa đến thời gian áp dụng.'
                ], 400);
            }

            // Check if coupon has expired
            if (now()->gt($coupon->end_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết hạn.'
                ], 400);
            }

            // Check usage limit
            if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã đạt giới hạn sử dụng.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mã giảm giá hợp lệ.',
                'coupon' => $coupon->load(['books', 'categories'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi kiểm tra mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            $coupon = Coupon::with(['books', 'categories'])->find($id);

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã giảm giá.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết mã giảm giá thành công.',
                'coupon' => $coupon
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy chi tiết mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}