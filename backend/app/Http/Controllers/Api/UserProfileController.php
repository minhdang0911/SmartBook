<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\CloudinaryService; // Thêm import này
use Illuminate\Support\Facades\Validator; 
use Carbon\Carbon;     

class UserProfileController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            'name'  => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }

    public function update(Request $request)
    {
        // 1) Validate cơ bản (dùng Validator để còn xử lý DOB custom)
        $validator = Validator::make(
            $request->all(),
            [
                'name'          => 'sometimes|required|string|max:100',
                'phone'         => ['sometimes','nullable','string','regex:/^(0?)(3[2-9]|5[689]|7[06-9]|8[1-5]|9[0-9])[0-9]{7}$/'],
                'gender'        => 'sometimes|nullable|in:male,female,other,unknown',
                // form-data mới có file; JSON không gửi field này thì bỏ qua
                'avatar'        => 'sometimes|file|image|mimes:jpg,jpeg,png,webp|max:10240', // 10MB
                'remove_avatar' => 'sometimes|boolean',
                // dob sẽ tự parse nên để string/nullable ở đây
                'date_of_birth' => 'sometimes|nullable|string',
            ],
            [
                'name.required' => 'Vui lòng nhập tên.',
                'name.max'      => 'Tên không vượt quá 100 ký tự.',
                'phone.regex'   => 'Số điện thoại không hợp lệ (VN 10 số).',
                'gender.in'     => 'Giới tính không hợp lệ.',
                'avatar.image'  => 'Avatar phải là ảnh.',
                'avatar.max'    => 'Ảnh tối đa 10MB nha.',
            ]
        );

        // 1.1) Parse DOB thủ công (chấp nhận YYYY-MM-DD hoặc DD/MM/YYYY)
        $dob = null;
        if ($request->filled('date_of_birth')) {
            $raw = trim((string)$request->input('date_of_birth'));
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
            foreach ($formats as $fmt) {
                try {
                    $dob = Carbon::createFromFormat($fmt, $raw)->startOfDay();
                    break;
                } catch (\Throwable $e) {}
            }
            if (!$dob) {
                $validator->errors()->add('date_of_birth', 'Ngày sinh không hợp lệ. Dùng YYYY-MM-DD hoặc DD/MM/YYYY.');
            } else {
                if ($dob->gte(today())) {
                    $validator->errors()->add('date_of_birth', 'Ngày sinh phải trước hôm nay.');
                }
                if ($dob->lt(Carbon::create(1900,1,1))) {
                    $validator->errors()->add('date_of_birth', 'Ngày sinh phải sau 1900-01-01.');
                }
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2) Lấy user (JWT/Sanctum đều ok)
        $user = $request->user() ?? auth('api')->user() ?? auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 3) Cập nhật text fields
        $dirty = false;
        foreach (['name','phone','gender'] as $f) {
            if ($request->has($f)) {
                $user->{$f} = $request->input($f);
                $dirty = true;
            }
        }
        if ($request->has('date_of_birth')) {
            $user->date_of_birth = $dob ? $dob->format('Y-m-d') : null;
            $dirty = true;
        }

        // 4) Avatar: chỉ xử lý khi là form-data có file / hoặc remove
        try {
            if ($request->hasFile('avatar')) {
                \Log::info('Avatar upload attempt', [
                    'user_id' => $user->id,
                    'file_name' => $request->file('avatar')->getClientOriginalName(),
                    'file_size' => $request->file('avatar')->getSize(),
                    'mime_type' => $request->file('avatar')->getMimeType(),
                    'is_valid' => $request->file('avatar')->isValid()
                ]);

                // Xóa avatar cũ nếu có
                if ($user->avatar_public_id) {
                    try { 
                        $this->cloudinaryService->deleteFile($user->avatar_public_id, 'image'); 
                    } catch (\Throwable $e) {
                        \Log::warning('Failed to delete old avatar: ' . $e->getMessage());
                    }
                }

                // Upload avatar mới sử dụng method uploadAvatar
                $uploadResult = $this->cloudinaryService->uploadAvatar($request->file('avatar'), $user->id);

                $user->avatar_url       = $uploadResult['secure_url'];
                $user->avatar_public_id = $uploadResult['public_id'];
                $dirty = true;

            } elseif ($request->boolean('remove_avatar')) {
                if ($user->avatar_public_id) {
                    try { 
                        $this->cloudinaryService->deleteFile($user->avatar_public_id, 'image'); 
                    } catch (\Throwable $e) {
                        \Log::warning('Failed to delete avatar during removal: ' . $e->getMessage());
                    }
                }
                $user->avatar_url = null;
                $user->avatar_public_id = null;
                $dirty = true;
            }
        } catch (\Throwable $e) {
            \Log::error('Update avatar failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Upload avatar thất bại',
                'error' => $e->getMessage()
            ], 500);
        }

        if ($dirty) $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'user' => [
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null,
                'gender'        => $user->gender,
                'avatar_url'    => $user->avatar_url,
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required','min:8','confirmed','different:current_password',
                'regex:/[A-Z]/','regex:/[a-z]/','regex:/[0-9]/','regex:/[@$!%*#?&]/',
            ],
        ], [
            'new_password.different' => 'Mật khẩu mới không được trùng mật khẩu cũ.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'new_password.regex'     => 'Mật khẩu phải có chữ hoa, chữ thường, số và ký tự đặc biệt.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false,'message' => 'Mật khẩu hiện tại không đúng.'], 422);
        }

        if (in_array($request->new_password, [$user->email, $user->name, $user->phone], true)) {
            return response()->json(['success' => false,'message' => 'Mật khẩu không được trùng với email, tên hoặc số điện thoại.'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true,'message' => 'Đổi mật khẩu thành công.']);
    }
}