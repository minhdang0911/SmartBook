<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'message' => 'Cập nhật thành công',
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validate input
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'confirmed',
                'different:current_password',
                'regex:/[A-Z]/',        // chữ in hoa
                'regex:/[a-z]/',        // chữ thường
                'regex:/[0-9]/',        // số
                'regex:/[@$!%*#?&]/',   // ký tự đặc biệt
            ],
        ], [
            'new_password.different' => 'Mật khẩu mới không được trùng mật khẩu cũ.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'new_password.regex' => 'Mật khẩu phải có chữ hoa, chữ thường, số và ký tự đặc biệt.',
        ]);

        // Check mật khẩu hiện tại đúng không
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng.'
            ], 422);
        }

        // Check trùng với thông tin cá nhân
        if (
            $request->new_password === $user->email ||
            $request->new_password === $user->name ||
            $request->new_password === $user->phone
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không được trùng với email, tên hoặc số điện thoại.'
            ], 422);
        }

        // Lưu mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công.'
        ]);
    }
}
