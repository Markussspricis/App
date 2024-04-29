<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $primaryKey = 'CommentID';

    protected $fillable = ['CommentText', 'UserID', 'TweetID'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'TweetID');
    }
}