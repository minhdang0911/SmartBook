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

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role' => 'required|in:user,admin'
        ]);

        $user->update($request->only('name', 'phone', 'address', 'role'));

        return redirect()->route('admin.users.index')->with('success', '👤 Thông tin người dùng đã được cập nhật!');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now()
        ]);

        return redirect()->back()->with('success', '⚙️ Trạng thái người dùng đã được cập nhật!');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', '❌ Không thể xóa quản trị viên.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', '🗑️ Người dùng đã bị xóa.');
    }
}
