<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments'; // Set the table name

    protected $primaryKey = 'CommentID'; // Set the primary key field

    protected $fillable = ['CommentText', 'UserID', 'TweetID']; // Define the fields that can be mass-assigned

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID'); // Define the relationship with the User model
    }

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'TweetID'); // Define the relationship with the Tweet model
    }
}