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

        $message = new Messages();
        $message->SenderID = $user->UserID;
        $message->ReceiverID = $request->input('ReceiverID');
        $message->Content = $request->input('Content');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('tweet_pictures', 'public');
            $message->Image = $path;
        }

        $message->save();

        $conversation = Conversation::where(function ($query) use ($user, $request) {
            $query->where('user1_id', $user->UserID)
                ->where('user2_id', $request->input('ReceiverID'));
        })->orWhere(function ($query) use ($user, $request) {
            $query->where('user1_id', $request->input('ReceiverID'))
                ->where('user2_id', $user->UserID);
        })->first();
    
        if (!$conversation) {
            // Create a new conversation
            $conversation = new Conversation();
            $conversation->user1_id = $user->UserID;
            $conversation->user2_id = $request->input('ReceiverID');
            $conversation->save();
        }    

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

    public function getUserMessages($userID)
    {
        // Fetch user based on the provided user ID
        $user = User::find($userID);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Fetch sent and received messages for the specified user
        $sentMessages = $user->messagesSent()->with('receiver')->get();
        $receivedMessages = $user->messagesReceived()->with('sender')->get();

        // Merge sent and received messages into a single array
        $messages = [];

        foreach ($sentMessages as $message) {
            $message->time_ago = $this->formatTimeAgo($message->created_at, Carbon::now());
            $message->type = 'sent';
            $messages[] = $message;
        }

        foreach ($receivedMessages as $message) {
            $message->time_ago = $this->formatTimeAgo($message->created_at, Carbon::now());
            $message->type = 'received';
            $messages[] = $message;
        }

        // Sort the messages by created_at timestamp
        usort($messages, function ($a, $b) {
            return $a->created_at <=> $b->created_at;
        });

        return response()->json(['messages' => $messages]);
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

    // public function getConversation(Request $request, User $user)
    // {
    //     $authUser = Auth::user();

    //     // Fetch messages exchanged between authenticated user and the specified user
    //     $messages = Messages::where(function ($query) use ($authUser, $user) {
    //         $query->where('SenderID', $authUser->id)
    //             ->where('ReceiverID', $user->id);
    //     })->orWhere(function ($query) use ($authUser, $user) {
    //         $query->where('SenderID', $user->id)
    //             ->where('ReceiverID', $authUser->id);
    //     })->orderBy('created_at', 'asc')->get();

    //     return response()->json(['user' => $user, 'messages' => $messages]);
    // }
    public function getConversations(Request $request)
    {
        $authUser = Auth::user();

        // Fetch all unique users with whom the authenticated user has exchanged messages
        $conversations = DB::table('messages')
            ->select('ReceiverID as UserID')
            ->where('SenderID', $authUser->UserID)
            ->union(DB::table('messages')
                ->select('SenderID as UserID')
                ->where('ReceiverID', $authUser->UserID))
            ->distinct()
            ->get();

        // Retrieve additional user information for each conversation
        $conversationsWithUserInfo = [];
        foreach ($conversations as $conversation) {
            $user = User::find($conversation->UserID);
            if ($user) {
                $conversationsWithUserInfo[] = [
                    'UserID' => $user->UserID,
                    'Name' => $user->Name,
                    'UserTag' => $user->UserTag,
                    'ProfilePicture' => $user->ProfilePicture,
                ];
            }
        }

        return response()->json(['conversations' => $conversationsWithUserInfo]);
    }
}
