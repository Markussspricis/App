<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retweet extends Model
{
    use HasFactory;

    protected $table = 'retweets';

    protected $primaryKey = 'RetweetID';

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