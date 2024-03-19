<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'SenderID', 'ReceiverID', 'NotificationType', 'NotificationText', 'NotificationLink', 'Read'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'SenderID'); // Define the relationship with the User model for the sender
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ReceiverID'); // Define the relationship with the User model for the receiver
    }
}
