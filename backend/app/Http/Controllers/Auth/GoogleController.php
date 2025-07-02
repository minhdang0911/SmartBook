<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;

class GoogleController extends Controller 
{
    public function redirectToGoogle(Request $request)
    {
        // Lưu frontend_url vào session để sử dụng sau
        if ($request->has('frontend_url')) {
            session(['frontend_url' => $request->get('frontend_url')]);
        }
        
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request) 
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(uniqid()),
                ]
            );

            $token = Auth::login($user);

            // Lấy frontend_url từ session hoặc dùng default
            $frontendUrl = session('frontend_url', 'http://localhost:3000');
            
            // Xóa session sau khi sử dụng
            session()->forget('frontend_url');

            // Redirect đến frontend với token
            return redirect()->to($frontendUrl . '/api/google-redirect?access_token=' . $token);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Google login failed: ' . $e->getMessage()
            ], 500);
        }
    }
}