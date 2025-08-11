<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'role',
        'otp_secret',
        'email_verified_at',
        // mới thêm
        'date_of_birth',
        'gender',
        'avatar_url',
        'avatar_public_id',
        // nếu DB có 2 cột này thì để luôn:
        'blocked_until',
        'block_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_secret',
        'avatar_public_id', // không show ra API
    ];

    // Laravel 10+ style
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'blocked_until'     => 'datetime',
            'date_of_birth'     => 'date:Y-m-d',
            'password'          => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Dùng MustVerifyEmail nhưng thêm helper cho tiện
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    /* ===== TƯƠNG THÍCH NGƯỢC =====
     * Code cũ dùng $user->avatar thì trả về avatar_url hiện tại
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->attributes['avatar_url'] ?? null;
    }
}
