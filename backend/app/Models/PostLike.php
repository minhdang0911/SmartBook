<?php

namespace App\Models; // 👈 QUAN TRỌNG

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostLike extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'post_id'];

    protected $table = 'post_likes'; // 👈 Thêm rõ tên bảng nếu không theo chuẩn đặt tên
}
