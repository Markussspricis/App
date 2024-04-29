<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Tweet;
use App\Models\User;

class TweetController extends Controller
{
    public function createTweet(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $tweet = new Tweet();
    
            if ($request->has('tweetText')) {
                $tweet->TweetText = $request->input('tweetText');
            }
    
            if ($request->hasFile('tweetImage')) {
                $image = $request->file('tweetImage');
                $path = $image->store('tweet_pictures', 'public');
                $tweet->TweetImage = $path;
            }

            $user->tweets()->save($tweet);
    
            $tweet->user = $user;
            $tweet->created_ago = 'now';
            $tweet->like_count = 0;
            $tweet->comment_count = 0;
            $tweet->retweet_count = 0;
            $tweet->isLiked = $this->checkIfLikedByUser($tweet, $user);
            $tweet->isRetweeted = $this->checkIfRetweetedByUser($tweet, $user);
            $tweet->isBookmarked = $this->checkIfBookmarkedByUser($tweet, $user);
    
            return response()->json(['message' => 'Tweet created successfully', 'tweet' => $tweet], 201);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function updateTweetStats(Request $request)
    {
        $tweetIds = $request->input('tweetIds', []);
    
        $tweets = Tweet::whereIn('TweetID', $tweetIds)->get();
    
        $updatedStats = [];
    
        foreach ($tweets as $tweet) {
            $now = Carbon::now();
            $updatedStats[$tweet->TweetID] = [
                'like_count' => $tweet->likes()->count(),
                'comment_count' => $tweet->comments()->count(),
                'retweet_count' => $tweet->retweets()->count(),
                'created_ago' => $this->formatTimeAgo($tweet->created_at, $now),
            ];
        }
    
        return response()->json($updatedStats);
    }

    public function getNewTweetCount($type)
    {
        $user = auth()->user();
        $lastCheckedAt = now()->subSeconds(10);

        $newTweets = Tweet::where('created_at', '>', $lastCheckedAt)
            ->where('UserID', '!=', $user->UserID)
            ->orderBy('created_at', 'desc')
            ->get();
        $tweetIDs = $newTweets->pluck('TweetID');

        $newFollowingTweets = Tweet::where('created_at', '>', $lastCheckedAt)
            ->whereIn('UserID', $user->follows->pluck('UserID')->push($user->UserID))
            ->whereNotIn('UserID', [$user->UserID])
            ->orderBy('created_at', 'desc')
            ->get();
        $following_tweetIDs = $newFollowingTweets->pluck('TweetID');
        return response()->json(['tweetIDs' => $tweetIDs, 'following_tweetIDs' => $following_tweetIDs]);
    }

    public function loadNewTweets(Request $request)
    {
        $Ids = $request->input('Ids');
        $type = $request->input('type');
        $user = auth()->user();

        if (empty($Ids)) {
            return response()->json(['message' => 'No new tweets found.'], 404);
        }
        if ($type == 'all') {
            $newTweets = Tweet::whereIn('TweetID', $Ids)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        if ($type == 'following') {
            $newTweets = Tweet::whereIn('TweetID', $Ids)
                ->whereIn('UserID', $user->follows->pluck('UserID')->push($user->UserID))
                ->orderBy('created_at', 'desc')
                ->get();
        }
        foreach ($newTweets as $tweet) {
            $now = Carbon::now();
            $tweet->created_ago = $this->formatTimeAgo($tweet->created_at, $now);
            $tweet->like_count = $tweet->likes()->count();
            $tweet->comment_count = $tweet->comments()->count();
            $tweet->retweet_count = $tweet->retweets()->count();
            $tweet->user = User::find($tweet->UserID);
        }
        return response()->json(['newTweets' => $newTweets]);
    }

    public function getTweets($type, $page)
    {
        if (auth()->check()) {
            $user = auth()->user();
    
            $query = Tweet::with('user')
                ->select('tweets.*')
                ->selectSub(function ($query) {
                    $query->from('comments')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('comments.TweetID', 'tweets.TweetID');
                }, 'comment_count')
                ->selectSub(function ($query) {
                    $query->from('likes')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('likes.TweetID', 'tweets.TweetID');
                }, 'like_count')
                ->selectSub(function ($query) {
                    $query->from('retweets')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('retweets.TweetID', 'tweets.TweetID');
                }, 'retweet_count')
                ->orderBy('created_at', 'desc');
    
                if ($type === 'following') {
                    $query->whereIn('UserID', $user->follows->pluck('UserID')->push($user->UserID))->whereNotIn('UserID', [$user->UserID]);
                } elseif ($type === 'my') {
                    $query->where('UserID', $user->UserID);
                } elseif ($type === 'liked') {
                    $query->whereIn('tweets.TweetID', function ($subquery) use ($user) {
                        $subquery->select('TweetID')
                            ->from('likes')
                            ->where('UserID', $user->UserID);
                    });
                } elseif ($type === 'bookmark') {
                    $query->whereIn('tweets.TweetID', function ($subquery) use ($user) {
                        $subquery->select('TweetID')
                            ->from('bookmarks')
                            ->where('UserID', $user->UserID);
                    });
                }
            
            $tweets = $query->paginate(5, ['*'], 'page', $page);
    
            foreach ($tweets as $tweet) {
                $now = Carbon::now();
                $tweet->created_ago = $this->formatTimeAgo($tweet->created_at, $now);
                $tweet->isLiked = $this->checkIfLikedByUser($tweet, $user);
                $tweet->isRetweeted = $this->checkIfRetweetedByUser($tweet, $user);
                $tweet->isBookmarked = $this->checkIfBookmarkedByUser($tweet, $user);
            }
            $totalPages = $tweets->lastPage();
    
            return response()->json(['tweets' => $tweets, 'total_pages' => $totalPages]);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getUserTweetsByType($userTag, $type, $page)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $tag = '@' . ltrim($userTag, '@');
            $user2 = User::where('UserTag', $tag)->first();
    
            if (!$user2) {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            $userID = $user2->UserID;
    
            switch ($type) {
                case 'replies':
                    $tweets = $this->getUserCommentedTweets($userID, $user2, $page);
                    break;
    
                case 'likes':
                    $tweets = $this->getUserLikedTweets($userID, $page);
                    break;
    
                case 'tweets':
                    $tweets = $this->getUserTweets($userID, $user2, $page);
                    break;

                default:
                    return response()->json(['error' => 'Invalid tweet type.'], 400);
            }

            return $tweets;
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getUserLikedTweets($userID, $page)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $tweets = Tweet::with('user')
                ->join('likes', function($join) use ($userID) {
                    $join->on('tweets.TweetID', '=', 'likes.TweetID')
                        ->where('likes.UserID', $userID);
                })
                ->where(function ($q) use ($userID) {
                    $q->where('tweets.UserID', $userID)
                        ->orWhere('likes.UserID', $userID);
                })
                ->select('tweets.*', 'likes.created_at as likes_created_at')
                ->selectSub(function ($query) {
                    $query->from('comments')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('comments.TweetID', 'tweets.TweetID');
                }, 'comment_count')
                ->selectSub(function ($query) {
                    $query->from('likes')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('likes.TweetID', 'tweets.TweetID');
                }, 'like_count')
                ->selectSub(function ($query) {
                    $query->from('retweets')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('retweets.TweetID', 'tweets.TweetID');
                }, 'retweet_count')
                ->orderByRaw('IF(likes.created_at IS NOT NULL, likes.created_at, tweets.created_at) DESC');

            $liked_tweet_count = $tweets->count();
            $tweets = $tweets->paginate(5, ['*'], 'page', $page);
    
            foreach ($tweets as $tweet) {
                $now = Carbon::now();
                $tweet->created_ago = $this->formatTimeAgo($tweet->created_at, $now);
                $tweet->isLiked = $this->checkIfLikedByUser($tweet, $user);
                $tweet->isRetweeted = $this->checkIfRetweetedByUser($tweet, $user);
                $tweet->isBookmarked = $this->checkIfBookmarkedByUser($tweet, $user);
            }
            $totalPages = $tweets->lastPage();
    
            return response()->json(['tweets' => $tweets, 'liked_tweet_count' => $liked_tweet_count, 'total_pages' => $totalPages]);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getUserCommentedTweets($userID, $user2, $page)
    {
        if (auth()->check()) {
            $user = auth()->user();
    
            $tweets = Tweet::with(['user', 'comments' => function ($query) use ($userID) {
                $query->where('UserID', $userID)->orderBy('created_at', 'desc');
            }])
            ->whereHas('comments', function ($query) use ($userID) {
                $query->where('UserID', $userID);
            })
            ->select('tweets.*')
            ->selectSub(function ($query) {
                $query->from('comments')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('comments.TweetID', 'tweets.TweetID');
            }, 'comment_count')
            ->selectSub(function ($query) {
                $query->from('likes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('likes.TweetID', 'tweets.TweetID');
            }, 'like_count')
            ->selectSub(function ($query) {
                $query->from('retweets')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('retweets.TweetID', 'tweets.TweetID');
            }, 'retweet_count')
            ->orderBy('tweets.created_at', 'desc');


            $commented_tweet_count = $tweets->count();
            $tweets=$tweets->paginate(5, ['*'], 'page', $page);

            foreach ($tweets as $tweet) {
                $now = Carbon::now();
                $tweet->created_ago = $this->formatTimeAgo($tweet->created_at, $now);
                $tweet->isLiked = $this->checkIfLikedByUser($tweet, $user);
                $tweet->isRetweeted = $this->checkIfRetweetedByUser($tweet, $user);
                $tweet->isBookmarked = $this->checkIfBookmarkedByUser($tweet, $user);
                foreach ($tweet->comments as $comment) {
                    $comment->created_ago = $this->formatTimeAgo($comment->created_at, $now);
                }
            }
            $totalPages = $tweets->lastPage();
    
            return response()->json(['tweets' => $tweets, 'commented_tweet_count' => $commented_tweet_count, 'total_pages' => $totalPages]);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getUserTweets($userID, $user2, $page)
    {
        if (auth()->check()) {
            $user = auth()->user();
    
            $tweets = Tweet::with('user')
                ->leftJoin('retweets', function($join) use ($userID) {
                    $join->on('tweets.TweetID', '=', 'retweets.TweetID')
                        ->where('retweets.UserID', $userID);
                })
                ->where(function ($q) use ($userID) {
                    $q->where('tweets.UserID', $userID)
                        ->orWhere('retweets.UserID', $userID);
                })
                ->select('tweets.*', 'retweets.created_at as retweet_created_at')
                ->selectSub(function ($query) use ($user2) {
                    $query->from('retweets')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('retweets.TweetID', 'tweets.TweetID')
                        ->where('retweets.UserID', $user2->UserID);
                }, 'isRetweet')
                ->selectSub(function ($query) {
                    $query->from('comments')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('comments.TweetID', 'tweets.TweetID');
                }, 'comment_count')
                ->selectSub(function ($query) {
                    $query->from('likes')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('likes.TweetID', 'tweets.TweetID');
                }, 'like_count')
                ->selectSub(function ($query) {
                    $query->from('retweets')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('retweets.TweetID', 'tweets.TweetID');
                }, 'retweet_count')
                ->orderByRaw('IF(retweets.created_at IS NOT NULL, retweets.created_at, tweets.created_at) DESC');

            $tweet_count = $tweets->count();
            $tweets=$tweets->paginate(5, ['*'], 'page', $page);
    
            foreach ($tweets as $tweet) {
                $now = Carbon::now();
                $tweet->created_ago = $this->formatTimeAgo($tweet->created_at, $now);
                $tweet->isLiked = $this->checkIfLikedByUser($tweet, $user);
                $tweet->isRetweeted = $this->checkIfRetweetedByUser($tweet, $user);
                $tweet->isBookmarked = $this->checkIfBookmarkedByUser($tweet, $user);
            }
    
            $totalPages = $tweets->lastPage();
    
            return response()->json(['tweets' => $tweets, 'tweet_count' => $tweet_count, 'total_pages' => $totalPages]);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
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

    public function deleteTweet($id)
    {
        $tweet = Tweet::find($id);
    
        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }
    
        $comments = $tweet->comments()->get();
    
        foreach ($comments as $comment) {
            $comment->delete();
        }
    
        $tweet->likes()->delete();
        $tweet->retweets()->delete();
        $tweet->bookmarks()->delete();
        $tweet->delete();
    
        return response()->json(['message' => 'Tweet deleted successfully']);
    }

    public function getTweetData($id)
    {
        $tweet = Tweet::with('user')
            ->select('tweets.*')
            ->selectSub(function ($query) {
                $query->from('comments')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('comments.TweetID', 'tweets.TweetID');
            }, 'comment_count')
            ->selectSub(function ($query) {
                $query->from('likes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('likes.TweetID', 'tweets.TweetID');
            }, 'like_count')
            ->selectSub(function ($query) {
                $query->from('retweets')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('retweets.TweetID', 'tweets.TweetID');
            }, 'retweet_count')
            ->find($id);
    
        if (!$tweet) {
            return response()->json(['message' => 'Tweet not found'], 404);
        }
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        } else {
            $tweet->isLiked = $this->checkIfLikedByUser($tweet, $user);
            $tweet->isRetweeted = $this->checkIfRetweetedByUser($tweet, $user);
            $tweet->isBookmarked = $this->checkIfBookmarkedByUser($tweet, $user);
        }
        return response()->json(['tweet' => $tweet]);
    }

    private function checkIfLikedByUser($tweet, $user)
    {
        return $tweet->likes->contains('UserID', $user->UserID);
    }

    private function checkIfRetweetedByUser($tweet, $user)
    {
        return $tweet->retweets->contains('UserID', $user->UserID);
    }

    private function checkIfBookmarkedByUser($tweet, $user)
    {
        return $tweet->bookmarks->contains('UserID', $user->UserID);
    }
}