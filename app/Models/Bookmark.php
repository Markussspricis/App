<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory;

    protected $table = 'bookmarks';

    protected $primaryKey = 'BookmarkID';

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