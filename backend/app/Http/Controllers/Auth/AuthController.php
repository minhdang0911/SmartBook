<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\OtpVerificationNotification;
use Tymon\JWTAuth\Facades\JWTFactory;



class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Tìm người dùng theo email (cả đã xóa mềm)
        $user = User::withTrashed()->where('email', $credentials['email'])->first();

        if ($user && $user->trashed()) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản của bạn đã bị khóa.'
            ], 403);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Email hoặc mật khẩu không đúng.'
            ], 401);
        }

        $user = auth()->user();

        // Tính thời gian 23:59:59 hôm nay
        $expiresAt = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59);

        // Tạo custom token có exp cố định là cuối ngày
        $payload = JWTFactory::customClaims([
            'sub' => $user->getJWTIdentifier(),
            'exp' => $expiresAt->timestamp,
        ])->make();

        $customToken = JWTAuth::encode($payload)->get();

        return response()->json([
            'status' => true,
            'access_token' => $customToken,
            'token_type' => 'bearer',
            'expires_at' => $expiresAt->timestamp,
            'user' => $user,
            'email_verified' => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at
        ], 200);
    }




    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'email_verified_at' => null, // Đặt email verify = false
            ]);

            $token = Auth::login($user);

            // Tự động gửi OTP sau khi đăng ký thành công
            $otp = OtpVerification::generateOtp();
            $expiresAt = Carbon::now()->addMinutes(5);

            // Lưu OTP vào database
            OtpVerification::create([
                'email' => $user->email,
                'otp' => $otp,
                'expires_at' => $expiresAt
            ]);

            // Gửi email OTP
            $user->notify(new OtpVerificationNotification($otp));

            return response()->json([
                'status' => true,
                'message' => 'Đăng ký thành công! Mã OTP đã được gửi về email của bạn.',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
                'user' => $user,
                'email_verified' => false,
                'otp_expires_at' => $expiresAt->timestamp
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi đăng ký: ' . $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        $user = auth()->user();
        return response()->json([
            'status' => true,
            'user' => auth()->user()
        ]);
    }

    // API gửi OTP
    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;

            // Kiểm tra user đã verify chưa
            $user = User::where('email', $email)->first();
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email đã được xác thực trước đó.'
                ], 400);
            }

            // Xóa OTP cũ nếu có
            OtpVerification::where('email', $email)->delete();

            // Tạo OTP mới
            $otp = OtpVerification::generateOtp();
            $expiresAt = Carbon::now()->addMinutes(5); // OTP có hiệu lực 5 phút

            // Lưu OTP vào database
            OtpVerification::create([
                'email' => $email,
                'otp' => $otp,
                'expires_at' => $expiresAt
            ]);

            // Gửi email OTP
            $user->notify(new OtpVerificationNotification($otp));

            return response()->json([
                'status' => true,
                'message' => 'Mã OTP đã được gửi về email của bạn.',
                'expires_at' => $expiresAt->timestamp
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // API xác thực OTP
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $inputOtp = $request->otp;

            // Tìm OTP trong database
            $otpRecord = OtpVerification::where('email', $email)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy mã OTP. Vui lòng yêu cầu gửi lại.'
                ], 400);
            }

            // Kiểm tra OTP đã hết hạn chưa
            if ($otpRecord->isExpired()) {
                $otpRecord->delete(); // Xóa OTP hết hạn
                return response()->json([
                    'status' => false,
                    'message' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu gửi lại.'
                ], 400);
            }

            // Kiểm tra OTP có khớp không
            if ($otpRecord->otp !== $inputOtp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mã OTP không đúng. Vui lòng kiểm tra lại.'
                ], 400);
            }

            // OTP đúng - cập nhật email_verified_at
            $user = User::where('email', $email)->first();
            $user->email_verified_at = Carbon::now();
            $user->save();

            // Xóa OTP đã sử dụng
            $otpRecord->delete();

            return response()->json([
                'status' => true,
                'message' => 'Xác thực email thành công!',
                'user' => $user,
                'email_verified' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Các method khác giữ nguyên...
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $token = Str::random(60);
            $hashedToken = Hash::make($token);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $hashedToken, 'created_at' => Carbon::now()]
            );

            $user = User::where('email', $request->email)->first();
            $user->notify(new ResetPasswordNotification($token));

            return response()->json([
                'status' => true,
                'message' => 'Email khôi phục đã được gửi!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$reset) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy yêu cầu đặt lại mật khẩu.'
            ], 400);
        }

        if (!Hash::check($request->token, $reset->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn.'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công!'
        ]);
    }
}
