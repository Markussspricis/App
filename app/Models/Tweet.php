<?php

namespace App\Models;

use App\Events\TweetDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Tweet extends Model
{
    use HasFactory;

    protected $table = 'tweets';

    protected $primaryKey = 'TweetID';

    protected $fillable = ['TweetText', 'TweetImage', 'UserID'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'TweetID');
    }
    public function retweets()
    {
        return $this->hasMany(Retweet::class, 'TweetID');
    }
    public function likes()
    {
        return $this->hasMany(Like::class, 'TweetID');
    }
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'TweetID');
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($tweet) {
            Log::info('Event sent twitter.php');
            event(new TweetDeleting($tweet));
        });
    }
}