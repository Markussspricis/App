<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RetweetController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;

Route::middleware('auth:sanctum')->get('user', [UserController::class, 'getUser']);
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::post('check-email', [UserController::class, 'checkEmail']);
Route::post('reset-password', [UserController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->get('/get-user/{id}', [UserController::class, 'getUserById']);
Route::post('updateName', [UserController::class, 'updateName']);
Route::post('updateDesc', [UserController::class, 'updateDescription']);
Route::post('updatePFP', [UserController::class, 'updateProfilePicture']);
Route::post('updateBanner', [UserController::class, 'updateBanner']);
Route::middleware('auth:sanctum')->post('/update-profile', [UserController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->get('/get-user-tag/{tag}', [UserController::class, 'getUserByTag']);
Route::middleware('auth:sanctum')->get('/update-follower-count/{userID}', [UserController::class, 'updateFollowerCount']);

Route::middleware('auth:sanctum')->post('/send-message', [MessageController::class, 'sendMessage']);
Route::middleware('auth:sanctum')->get('/conversation-messages/{conversationID}', [MessageController::class, 'getConversationMessages']);
Route::delete('/messages/{id}', [MessageController::class, 'deleteMessage']);
Route::middleware('auth:sanctum')->get('/conversations/{userId}', [MessageController::class, 'getConversation']);
Route::middleware('auth:sanctum')->post('/conversations', [MessageController::class, 'createConversation']);

Route::middleware('auth:sanctum')->post('tweets', [TweetController::class, 'createTweet']);
Route::middleware('auth:sanctum')->get('/tweet_type/{type}/{page}', [TweetController::class, 'getTweets']);
Route::middleware('auth:sanctum')->get('/user_tweets/{userTag}/{type}/{page}', [TweetController::class, 'getUserTweetsByType']);
Route::middleware('auth:sanctum')->get('/tweetdata/{id}', [TweetController::class, 'getTweetData']);
Route::delete('/tweets/{id}',[TweetController::class, 'deleteTweet']);
Route::middleware('auth:sanctum')->get('/get-new-tweet-count/{type}', [TweetController::class, 'getNewTweetCount']);
Route::middleware('auth:sanctum')->get('/load-new-tweets', [TweetController::class, 'loadNewTweets']);
Route::middleware('auth:sanctum')->get('/update-stats', [TweetController::class, 'updateTweetStats']);

Route::middleware('auth:sanctum')->post('/create-comments', [CommentController::class, 'createComment']);
Route::get('/comments/{tweetId}', [CommentController::class, 'getCommentsByTweet']);
Route::delete('/delete-comments/{id}',[CommentController::class, 'deleteComment']);

Route::middleware('auth:sanctum')->post('tweets/like', [LikeController::class, 'likeTweet']);
Route::middleware('auth:sanctum')->delete('tweets/unlike/{tweetId}', [LikeController::class, 'unlikeTweet']);

Route::middleware('auth:sanctum')->post('tweets/retweet', [RetweetController::class, 'retweetTweet']);
Route::middleware('auth:sanctum')->delete('tweets/unretweet/{tweetId}', [RetweetController::class, 'unretweetTweet']);

Route::middleware('auth:sanctum')->post('/follow/{userId}', [FollowController::class, 'follow']);
Route::middleware('auth:sanctum')->post('/unfollow/{userId}', [FollowController::class, 'unfollow']);
Route::middleware('auth:sanctum')->get('/topFollowedUsers', [FollowController::class, 'topFollowedUsers']);
Route::middleware('auth:sanctum')->get('/allusers', [FollowController::class, 'getAllUsersExceptMe']);
// Route::middleware('auth:sanctum')->get('/all-users-mention', [FollowController::class, 'getAllUsersMention']);
Route::get('/countFollowersAndFollowing/{userID}', [FollowController::class, 'countFollowersAndFollowing']);
Route::middleware('auth:sanctum')->get('/following/{tag}', [FollowController::class, 'getFollowingUsers']);
Route::middleware('auth:sanctum')->get('/followers/{tag}', [FollowController::class, 'getFollowers']);

Route::middleware('auth:sanctum')->post('tweets/bookmark', [BookmarkController::class, 'createBookmark']);
Route::middleware('auth:sanctum')->delete('tweets/unbookmark/{tweetId}', [BookmarkController::class, 'removeBookmark']);

Route::middleware('auth:sanctum')->get('/get-notifications/{type}', [NotificationController::class, 'getNotifications']);
Route::middleware('auth:sanctum')->get('/get-new-notifications/{type}', [NotificationController::class, 'getNewNotifications']);
Route::middleware('auth:sanctum')->get('/get-unread-notification-count', [NotificationController::class, 'getUnreadNotificationCount']);
Route::middleware('auth:sanctum')->post('/delete-selected-notifications', [NotificationController::class, 'deleteSelectedNotifications']);
Route::middleware('auth:sanctum')->post('/mark-selected-as-read-notifications', [NotificationController::class, 'markSelectedAsReadNotifications']);