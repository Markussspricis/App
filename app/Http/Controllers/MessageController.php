<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Messages;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        // Fetch or create the conversation
        $conversation = Conversation::where(function ($query) use ($user, $request) {
            $query->where('user1_id', $user->UserID)
                ->where('user2_id', $request->input('ReceiverID'));
        })->orWhere(function ($query) use ($user, $request) {
            $query->where('user1_id', $request->input('ReceiverID'))
                ->where('user2_id', $user->UserID);
        })->first();

        // Ensure conversation exists
        if (!$conversation) {
            // Create a new conversation if it doesn't exist
            $conversation = new Conversation();
            $conversation->user1_id = $user->UserID;
            $conversation->user2_id = $request->input('ReceiverID');
            $conversation->save();
        }

        // Create a new message
        $message = new Messages();
        $message->SenderID = $user->UserID;
        $message->ReceiverID = $request->input('ReceiverID');
        $message->Content = $request->input('Content');

        // Assign the conversation ID to the message
        $message->ConversationID = $conversation->ConversationID;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('tweet_pictures', 'public');
            $message->Image = $path;
        }

        // Save the message to the database
        $message->save();

        return response()->json(['success' => true, 'message' => 'Message sent successfully']);
    }

    private function formatTimeAgo($created_at, $now)
    {
        $diff = $created_at->diff($now);

        if ($diff->y > 0) {
            return $diff->y . 'y';
        } elseif ($diff->m > 0) {
            return $diff->m . 'mo';
        } elseif ($diff->d > 0) {
            return $diff->d . 'd';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h';
        } elseif ($diff->i > 0) {
            return $diff->i . 'm';
        } else {
            return $diff->s . 's';
        }
    }

    public function deleteMessage($id)
    {
        $message = Messages::find($id);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    public function checkConversation(Request $request, $userId)
    {
        $authUserId = Auth::id();

        // Check if a conversation exists between the authenticated user and the specified user
        $conversation = Conversation::where(function ($query) use ($authUserId, $userId) {
            $query->where('user1_id', $authUserId)
                ->where('user2_id', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('user1_id', $userId)
                ->where('user2_id', $authUserId);
        })->first();

        if ($conversation) {
            return response()->json(['conversationExists' => true, 'conversation' => $conversation]);
        } else {
            return response()->json(['conversationExists' => false]);
        }
    }

    public function getConversation(Request $request, $userId)
    {
        $authUserId = Auth::id();
        // Check if a conversation exists between the authenticated user and the specified user
        $conversation = Conversation::where(function ($query) use ($authUserId, $userId) {
            $query->where('user1_id', $authUserId)
                ->where('user2_id', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('user1_id', $userId)
                ->where('user2_id', $authUserId);
        })->first();

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        // If conversation exists, fetch associated messages
        $messages = Messages::where('ConversationID', $conversation->ConversationID)
                            ->with(['sender', 'receiver'])
                            ->orderBy('created_at')
                            ->get();

        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found in this conversation'], 404);
        }

        // Augment each message with additional properties
        $now = Carbon::now();
        foreach ($messages as $message) {
            $message->time_ago = $this->formatTimeAgo($message->created_at, $now);
            $message->type = ($message->SenderID == $authUserId) ? 'sent' : 'received'; // Use authenticated user for comparison
        }

        return response()->json(['conversation' => $conversation, 'messages' => $messages]);
    }

    public function createConversation(Request $request)
    {
        $authUserId = Auth::id();
        $userId = $request->input('userId');

        // Create a new conversation
        $conversation = new Conversation();
        $conversation->user1_id = $authUserId;
        $conversation->user2_id = $userId;
        $conversation->save();

        return response()->json(['conversation' => $conversation]);
    }

    public function getUserConversations(Request $request)
    {
        $userId = Auth::id();
        
        // Fetch conversations for the authenticated user
        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with(['user1', 'user2']) // Eager load both user1 and user2 relationships
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json(['conversations' => $conversations]);
    }

}
