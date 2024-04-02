<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Messages;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        $sentMessages = $user->messagesSent()->with('receiver')->orderBy('created_at', 'desc')->get();
        $receivedMessages = $user->messagesReceived()->orderBy('created_at', 'desc')->get();

        $now = Carbon::now();

        foreach ($sentMessages as $message) {
            $message->sent_ago = $this->formatTimeAgo($message->created_at, $now);
        }

        foreach ($receivedMessages as $message) {
            $message->received_ago = $this->formatTimeAgo($message->created_at, $now);
        }

        return response()->json(['sent_messages' => $sentMessages, 'received_messages' => $receivedMessages]);
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

    public function getConversation(Request $request, User $user)
    {
        $authUser = Auth::user();

        // Fetch messages exchanged between authenticated user and the specified user
        $messages = Messages::where(function ($query) use ($authUser, $user) {
            $query->where('SenderID', $authUser->id)
                ->where('ReceiverID', $user->id);
        })->orWhere(function ($query) use ($authUser, $user) {
            $query->where('SenderID', $user->id)
                ->where('ReceiverID', $authUser->id);
        })->orderBy('created_at', 'asc')->get();

        return response()->json(['user' => $user, 'messages' => $messages]);
    }
}
