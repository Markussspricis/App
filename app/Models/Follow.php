<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $table = 'follows';
    protected $fillable = ['FollowerID', 'FollowingID'];

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'FollowingID', 'FollowerID');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'FollowerID', 'FollowingID');
    }
}