<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Like;
use App\Models\Tweet;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likeTweet(Request $request)
    {
        $user = Auth::user();
        $tweetId = $request->input('tweetId');

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tweet = Tweet::find($tweetId);

        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }

        // Check if the user has already liked the tweet
        $existingLike = Like::where('UserID', $user->UserID)
            ->where('TweetID', $tweet->TweetID)
            ->first();

        if ($existingLike) {
            return response()->json(['message' => 'You have already liked this tweet'], 400);
        }

        // Create a new like
        $like = new Like();
        $like->UserID = $user->UserID;
        $like->TweetID = $tweet->TweetID;
        $like->save();
        if ($tweet->UserID != $user->UserID) {
            $existingNotification = Notification::where('SenderID', $user->UserID)
                ->where('ReceiverID', $tweet->UserID)
                ->where('NotificationType', 'like')
                ->where('NotificationLink', '/tweet/' . $tweet->TweetID)
                ->first();
            if (!$existingNotification) {
                $notification = new Notification([
                    'SenderID' => $user->UserID,
                    'ReceiverID' => $tweet->UserID,
                    'NotificationType' => 'like',
                    'NotificationText' => ' liked your tweet',
                    'NotificationLink' => '/tweet/' . $tweet->TweetID,
                    'Read' => false,
                ]);
                $notification->save();
            }

        }
        return response()->json(['message' => 'Liked successfully', 'like' => $like, 'tweet' => $tweet], 201);
    }

    public function unlikeTweet($tweetId)
    {
        $user = Auth::user();
        $tweet = Tweet::find($tweetId);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }

        // Check if the user has already liked the tweet
        $existingLike = Like::where('UserID', $user->UserID)
            ->where('TweetID', $tweet->TweetID)
            ->first();

        if (!$existingLike) {
            return response()->json(['message' => 'You have not liked this tweet'], 400);
        }

        // Delete the existing like
        $existingLike->delete();

        return response()->json(['message' => 'Unliked successfully']);
    }
}