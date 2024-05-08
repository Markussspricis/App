<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';
    
    protected $primaryKey = 'UserID';

    protected $fillable = [
        'Name',
        'UserTag',
        'Email',
        'Password',
        'DOB',
        'ProfilePicture',
        'Description',
        'Banner',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = ['Password',];

    public function tweets()
    {
        return $this->hasMany(Tweet::class, 'UserID');
    }

    public function retweets()
    {
        return $this->hasMany(Retweet::class, 'UserID');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'UserID');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'UserID');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'UserID');
    }

    public function follows()
    {
        return $this->belongsToMany(User::class, 'follows', 'FollowerID', 'FollowingID');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'FollowingID', 'FollowerID');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'FollowerID', 'FollowingID');
    }

    public function messagesSent()
    {
        return $this->hasMany(Messages::class, 'SenderID');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Messages::class, 'ReceiverID');
    }

    public function notificationsSent()
    {
        return $this->hasMany(Notification::class, 'SenderID');
    }

    public function notificationsReceived()
    {
        return $this->hasMany(Notification::class, 'ReceiverID');
    }

    public function tokens(): MorphMany
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function createPasswordResetToken()
    {
        $this->password_reset_token = Str::random(60);
        $this->password_reset_expires_at = now()->addHours(2);
        $this->save();
    }

    public function resetPassword($newPassword)
    {
        $this->password = bcrypt($newPassword);
        $this->password_reset_token = null;
        $this->password_reset_expires_at = null;
        $this->save();
    }

    public function isPasswordResetTokenValid()
    {
        return $this->password_reset_expires_at > now();
    }
}