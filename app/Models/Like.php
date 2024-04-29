<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $table = 'likes';

    protected $primaryKey = 'LikeID';

    protected $fillable = ['UserID', 'TweetID'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'TweetID');
    }
}