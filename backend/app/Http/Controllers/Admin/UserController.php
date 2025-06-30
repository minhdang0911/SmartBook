<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::withTrashed()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:100',
        'phone' => 'nullable|string|max:20|regex:/^[0-9]+$/',
        'role' => 'required|in:user,admin',
    ], [
        'phone.max' => '📱 Số điện thoại không được vượt quá 20 ký tự.',
        'phone.regex' => '📵 Số điện thoại chỉ được chứa các chữ số (0-9).',
        'name.required' => '⚠️ Vui lòng nhập tên người dùng.',
    ]);

    $user->update($request->only('name', 'phone', 'role'));

    return redirect()->route('admin.users.index')->with('success', '✅ Đã cập nhật thông tin người dùng!');
}


    public function toggleStatus(User $user)
    {
        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now()
        ]);

        return back()->with('success', '⚙️ Đã cập nhật trạng thái xác thực email!');
    }

    public function lock(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', '❌ Không thể khóa tài khoản admin.');
        }

        $user->delete(); // Soft delete
        return back()->with('success', '🔒 Tài khoản đã bị khóa.');
    }

    public function unlock($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->restore();
            return back()->with('success', '🔓 Tài khoản đã được mở khóa.');
        }

        return back()->with('info', '⚠️ Tài khoản này không bị khóa.');
    }
}
