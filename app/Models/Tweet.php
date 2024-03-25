<?php

namespace App\Models;

use App\Events\TweetDeleting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Tweet extends Model
{
    use HasFactory;

    protected $table = 'tweets'; // Set the table name

    protected $primaryKey = 'TweetID'; // Set the primary key field

    protected $fillable = ['TweetText', 'TweetImage', 'UserID']; // Define the fields that can be mass-assigned

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID'); // Define the relationship with the User model
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