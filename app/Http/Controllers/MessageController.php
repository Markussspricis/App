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
    public function getConversationMessages($conversationID)
    {
        try {
            // Fetch messages based on the provided conversation ID
            $messages = Messages::where('ConversationID', $conversationID)->with(['sender', 'receiver'])->get();

            // Check if any messages are found
            if ($messages->isEmpty()) {
                return response()->json(['error' => 'No messages found for the provided conversation ID'], 404);
            }

            // Augment each message with additional properties
            foreach ($messages as $message) {
                $message->time_ago = $this->formatTimeAgo($message->created_at, Carbon::now());
                $message->type = ($message->sender_id == auth()->id()) ? 'sent' : 'received'; // Assuming authenticated user ID is used for comparison
            }

            // Sort the messages by created_at timestamp
            $messages = $messages->sortBy('created_at')->values()->all();

            return response()->json(['conversationId' => $conversationID, 'messages' => $messages]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching messages: ' . $e->getMessage()], 500);
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

        return response()->json(['conversation' => $conversation]); // Return conversation directly
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
}
