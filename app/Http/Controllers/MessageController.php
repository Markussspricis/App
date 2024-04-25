<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Messages;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        $conversation = Conversation::where(function ($query) use ($user, $request) {
            $query->where('user1_id', $user->UserID)
                ->where('user2_id', $request->input('ReceiverID'));
        })->orWhere(function ($query) use ($user, $request) {
            $query->where('user1_id', $request->input('ReceiverID'))
                ->where('user2_id', $user->UserID);
        })->first();

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->user1_id = $user->UserID;
            $conversation->user2_id = $request->input('ReceiverID');
            $conversation->save();
        }

        $message = new Messages();
        $message->SenderID = $user->UserID;
        $message->ReceiverID = $request->input('ReceiverID');
        $message->Content = $request->input('Content');

        $message->ConversationID = $conversation->ConversationID;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('tweet_pictures', 'public');
            $message->Image = $path;
        }

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

    public function deleteMessage(Request $request, $id)
    {
        try {
            $message = Messages::find($id);

            if (!$message) {
                return response()->json(['message' => 'Message not found'], 404);
            }

            $authUser = Auth::user();

            if (!$authUser) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            $authenticatedUserID = $request->input('authenticatedUserID');

            if ($authUser->UserID != $authenticatedUserID) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $deleteType = $request->input('deleteType', 'self');

            if ($deleteType === 'self') {
                if ($message->deleted_by === null) {
                    $message->deleted_by = $authenticatedUserID;
                    //$message->deleted_at = now();
                    $message->save();
                } elseif ($message->deleted_by !== $authenticatedUserID) {
                    $message->forceDelete();
                    return response()->json(['message' => 'Message deleted successfully', 'success' => true]);
                }
            } elseif ($deleteType === 'all') {
                $message->forceDelete();
            }

            return response()->json(['message' => 'Message deleted successfully', 'success' => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete message', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function checkConversation(Request $request, $userId)
    {
        $authUserId = Auth::id();

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

        $messages = Messages::where('ConversationID', $conversation->ConversationID)
                            ->with(['sender', 'receiver'])
                            ->orderBy('created_at')
                            ->get();

        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found in this conversation'], 404);
        }

        $now = Carbon::now();
        foreach ($messages as $message) {
            $message->time_ago = $this->formatTimeAgo($message->created_at, $now);
            $message->type = ($message->SenderID == $authUserId) ? 'sent' : 'received';
        }

        return response()->json(['conversation' => $conversation, 'messages' => $messages]);
    }

    public function createConversation(Request $request)
    {
        $authUserId = Auth::id();
        $userId = $request->input('userId');

        $conversation = new Conversation();
        $conversation->user1_id = $authUserId;
        $conversation->user2_id = $userId;
        $conversation->save();

        return response()->json(['conversation' => $conversation]);
    }

    public function getUserConversations(Request $request)
    {
        $userId = Auth::id();
        
        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with(['user1', 'user2'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json(['conversations' => $conversations]);
    }

    public function deleteConversation(Request $request, $conversationId)
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            $authUser = Auth::user();
            if (!$authUser) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }
            if ($conversation->user1_id != $authUser->UserID && $conversation->user2_id != $authUser->UserID) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $deletedMessagesCount = $conversation->messages()->delete();

            \Log::info('Deleted ' . $deletedMessagesCount . ' messages for conversation ID: ' . $conversationId);

            $conversation->delete();

            return response()->json(['message' => 'Conversation deleted successfully', 'success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete conversation: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to delete conversation', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUnreadMessagesCount()
    {
        $user = Auth::user();
        $unreadCount = $user->messagesReceived()->count();
        if ($unreadCount > 9) {
            $unreadCount = '9+';
        }

        return response()->json(['unreadCount' => $unreadCount]);
    }

    public function getUnreadMessageCount($conversationId)
    {
        $user = Auth::user();
        
        // Fetch and return the updated unread count
        $unreadCount = $user->messagesReceived()
            ->where('ConversationID', $conversationId)
            ->where('Read', false)
            ->count();

        return response()->json(['unreadCount' => $unreadCount]);
    }

    public function markConversationAsRead($conversationId)
    {
        $user = Auth::user();

        // Update the Read status of messages in the conversation
        $user->messagesReceived()
            ->where('ConversationID', $conversationId)
            ->update(['Read' => true]);

        // Fetch and return the updated unread count
        $unreadCount = $user->messagesReceived()
            ->where('ConversationID', $conversationId)
            ->where('Read', false)
            ->count();

        return response()->json(['updatedUnreadCount' => $unreadCount]);
    }
}