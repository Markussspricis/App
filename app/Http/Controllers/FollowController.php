<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function follow($userToFollowId)
    {
        $user = auth()->user();
        $userToFollow = User::find($userToFollowId);
        $EditedUserTag = substr($userToFollow->UserTag, 1);

    
        if ($userToFollow) {
            $user->follows()->attach($userToFollow->UserID);
            if ($user->UserID != $userToFollow->UserID){
                $existingNotification = Notification::where('SenderID', $user->UserID)
                    ->where('ReceiverID', $userToFollow->UserID)
                    ->where('NotificationType', 'follow')
                    ->where('NotificationLink', '/profile/' . $EditedUserTag)
                    ->first();
                if (!$existingNotification) {
                    $notification = new Notification([
                        'SenderID' => $user->UserID,
                        'ReceiverID' => $userToFollow->UserID,
                        'NotificationType' => 'follow',
                        'NotificationText' => ' started following you',
                        'NotificationLink' => '/profile/' . $EditedUserTag,
                        'Read' => false, 
                    ]);
                    $notification->save();
                }
            }
            return response()->json(['message' => 'You are now following this user.'.$userToFollow->UserID]);
        } else {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }
    
    public function unfollow($userToUnfollowId)
    {
        $user = auth()->user();
        $userToUnfollow = User::find($userToUnfollowId);
    
        if ($userToUnfollow) {
            $user->follows()->detach($userToUnfollow->UserID);
            return response()->json(['message' => 'You have unfollowed this user.']);
        } else {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    public function topFollowedUsers()
    {
        if (auth()->check()) {
            $user1 = auth()->user();
    
            $topFollowedUsers = User::withCount('followers')
                ->where('UserID', '!=', $user1->UserID)
                ->orderBy('followers_count', 'desc')
                ->get();
    
            $followed = collect();
            $nonfollowed = collect();
    
            foreach ($topFollowedUsers as $user) {
                $user->isFollowedByMe = $this->checkIfFollowedByUser($user1, $user);
                if ($this->checkIfFollowedByUser($user1, $user)){
                    $followed->push($user);
                } else {
                    $nonfollowed->push($user);
                }
            }
            Log::info($followed);
            Log::info($nonfollowed);
            $sortedUsers = $nonfollowed->concat($followed)->take(8);
    
            return response()->json($sortedUsers);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getAllUsersExceptMe()
    {
        if (auth()->check()) {
            $user1 = auth()->user();
            
            $Users = User::select('users.*')
                ->selectSub(function ($query) {
                    $query->selectRaw('count(*)')
                        ->from('follows')
                        ->whereRaw('(follows.FollowerID = users.UserID OR follows.FollowingID = users.UserID)');
                }, 'followers_count')
                ->where('UserID', '!=', $user1->UserID)
                ->orderByDesc(DB::raw("IF((SELECT COUNT(*) FROM follows WHERE (FollowerID = $user1->UserID AND FollowingID = users.UserID) OR (FollowingID = $user1->UserID AND FollowerID = users.UserID)) > 0, 1, 0)"))
                ->orderBy('followers_count', 'desc')
                ->get();
    
            foreach ($Users as $user2) {
                $user2->isFollowedByMe = $this->checkIfFollowedByUser($user1, $user2);
            }
    
            return response()->json($Users);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function countFollowersAndFollowing($userID)
    {
        $user = User::find($userID);

        if ($user) {
            $followersCount = $user->followers()->count();
            $followingCount = $user->following()->count();

            return response()->json([
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ]);
        } else {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    private function checkIfFollowedByUser($user1, $user2)
    {
        return $user2->followers->contains('UserID', $user1->UserID);
    }

    public function getFollowingUsers($userTag)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $tag = '@' . ltrim($userTag, '@');
            $user2 = User::where('UserTag', $tag)->first();
            if (!$user2) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $userID = $user2->UserID;
            $following = User::select('users.*')
                ->join('follows', 'users.UserID', '=', 'follows.FollowingID')
                ->where('follows.FollowerID', $user2->UserID)
                ->get();
            foreach ($following as $user3) {
                $user3->isFollowedByMe = $this->checkIfFollowedByUser($user, $user3);
            }
            return response()->json($following);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getFollowers($userTag)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $tag = '@' . ltrim($userTag, '@');
            $user2 = User::where('UserTag', $tag)->first();
            if (!$user2) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $userID = $user2->UserID;
            $followers = User::select('users.*')
                ->join('follows', 'users.UserID', '=', 'follows.FollowerID')
                ->where('follows.FollowingID', $user2->UserID)
                ->get();
            foreach ($followers as $user3) {
                $user3->isFollowedByMe = $this->checkIfFollowedByUser($user, $user3);
            }
            return response()->json($followers);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }
}