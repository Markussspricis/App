<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'messages';

    protected $primaryKey = 'MessageID';

    protected $fillable = ['SenderID', 'ReceiverID', 'Content', 'Image', 'ConversationID', 'deleted_by'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'SenderID');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ReceiverID');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'ConversationID');
    }
}
