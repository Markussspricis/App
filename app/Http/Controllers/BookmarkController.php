<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookmark;
use App\Models\Tweet;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function createBookmark(Request $request)
    {

        $user = Auth::user();
        $tweetId = $request->input('tweetId');

        // $data = $request->validate([
        //     'UserID' => 'required',
        //     'TweetID' => 'required',
        // ]);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tweet = Tweet::find($tweetId);

        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }

        // $bookmark = Bookmark::create($data);

        // return response()->json($bookmark, 201);

        $existingBookmark = Bookmark::where('UserID', $user->UserID)
            ->where('TweetID', $tweet->TweetID)
            ->first();

        if ($existingBookmark) {
            return response()->json(['message' => 'You have already bookmarked this tweet'], 400);
        }

        $bookamrk = new Bookmark();
        $bookamrk->UserID = $user->UserID;
        $bookamrk->TweetID = $tweet->TweetID;
        $bookamrk->save();

        return response()->json(['message' => 'Bookmarked successfully', 'bookmark' => $bookamrk], 201);
    }

    public function removeBookmark($tweetId)
    {
        // $bookmark = Bookmark::find($bookmarkID);
        $user = Auth::user();
        $tweet = Tweet::find($tweetId);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }

        $existingBookmark = Bookmark::where('UserID', $user->UserID)
        ->where('TweetID', $tweet->TweetID)
        ->first();

        if (!$existingBookmark) {
            return response()->json(['message' => 'You have not bookmarked this tweet'], 400);
        }

        $existingBookmark->delete();

        // return response()->json(null, 204);
        return response()->json(['message' => 'Unbookmarked successfully']);
    }
}