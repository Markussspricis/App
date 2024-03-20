<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Retweet;
use App\Models\Tweet;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class RetweetController extends Controller
{
    public function retweetTweet(Request $request)
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

        $existingRetweet = Retweet::where('UserID', $user->UserID)
            ->where('TweetID', $tweet->TweetID)
            ->first();

        if ($existingRetweet) {
            return response()->json(['message' => 'You have already retweeted this tweet'], 400);
        }

        $retweet = new Retweet();
        $retweet->UserID = $user->UserID;
        $retweet->TweetID = $tweet->TweetID;
        $retweet->save();
        if ($tweet->UserID != $user->UserID) {
            $existingNotification = Notification::where('SenderID', $user->UserID)
                ->where('ReceiverID', $tweet->UserID)
                ->where('NotificationType', 'retweet')
                ->where('NotificationLink', '/tweet/' . $tweet->TweetID)
                ->first();
            if (!$existingNotification) {
                $notification = new Notification([
                    'SenderID' => $user->UserID,
                    'ReceiverID' => $tweet->UserID,
                    'NotificationType' => 'retweet',
                    'NotificationText' => ' retweeted your tweet',
                    'NotificationLink' => '/tweet/' . $tweet->TweetID,
                    'Read' => false,
                ]);
                $notification->save();
            }
        }

        return response()->json(['message' => 'Retweeted successfully', 'retweet' => $retweet], 201);
    }

    public function unretweetTweet($tweetId)
    {
        $user = Auth::user();
        $tweet = Tweet::find($tweetId);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }

        $existingRetweet = Retweet::where('UserID', $user->UserID)
            ->where('TweetID', $tweet->TweetID)
            ->first();

        if (!$existingRetweet) {
            return response()->json(['message' => 'You have not retweeted this tweet'], 400);
        }

        $existingRetweet->delete();

        return response()->json(['message' => 'Unretweeted successfully']);
    }
}