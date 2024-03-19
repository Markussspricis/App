<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    protected $table = 'messages'; // Set the table name

    protected $primaryKey = 'MessageID'; // Set the primary key field

    protected $fillable = ['SenderID', 'ReceiverID', 'Content', 'Image']; // Define the fields that can be mass-assigned

    public function sender()
    {
        return $this->belongsTo(User::class, 'SenderID'); // Define the relationship with the User model for the sender
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ReceiverID'); // Define the relationship with the User model for the receiver
    }
}
