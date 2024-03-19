<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserController extends Controller
{
    public function checkEmail(Request $request)
    {
        $user = User::where('Email', $request->Email)->first();
        if ($user) {
            $success = true;
            $message = 'Email is registered.';
        } else {
            $success = false;
            $message = 'Email not found.';
        }
        $response = [
            'success' => $success,
            'message' => $message,
        ];
        return response()->json($response);
    }

    public function login(Request $request)
    {
        $credentials = [
            'Email' => $request->Email,
            'Password' => $request->Password
        ];

        $user = User::where('Email', $request->Email)->first();

        if ($user) {
            // Check the password
            if (Hash::check($request->Password, $user->Password)) {
                $token = $user->createToken('authToken')->plainTextToken; // Create and get the user token
                $user->token = $token;

                $success = true;
                $message = 'User logged in successfully';
            } else {
                $success = false;
                $message = 'Login failed. Invalid password.';
            }
        } else {
            $success = false;
            $message = "Email isn't registered.";
        }
        $response = [
            'success' => $success,
            'message' => $message,
            'user' => $user,
        ];
        return response()->json($response);
    }

    public function register(Request $request)
    {
        try {
            $existingUserTag = User::where('UserTag', '@' . $request->UserTag)->first();
            if ($existingUserTag) {
                $success = false;
                $message = 'Username is already taken.';
                $user= null;
            } else {
                $existingEmail = User::where('Email', strtolower($request->Email))->first();
                if ($existingEmail) {
                    $success = false;
                    $message = 'Email is already taken.';
                    $user= null;
                } else {
                    $user = new User();
                    $user->Name = $request->Name;

                    $correctTag = '@' . ltrim($request->UserTag, '@');
                    $user->UserTag = $correctTag;
                    $user->Email = strtolower($request->Email);
                    $user->Password = Hash::make($request->Password);
                    $user->ProfilePicture = 'profile_pictures/DefaultPFP.jpeg';

                    $dob = Carbon::parse($request->DOB)->format('Y-m-d');
                    $user->DOB = $dob;
                    $user->save();

                    $success = true;
                    $message = 'User created successfully';
                    $token = $user->createToken('authToken')->plainTextToken; // Create and get the user token
                    $user->token = $token;
                }
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
            $message = 'An error occurred while creating the user.';
        }

        $response = [
            'success' => $success,
            'message' => $message,
            'user' => $user
        ];
        return response()->json($response);
    }

    public function resetPassword(Request $request)
    {
        // Validate the input data (email and new password)
        $request->validate([
            'email' => 'required|email',
            'newPassword' => 'required|min:8',
        ]);

        $email = $request->input('email');
        $newPassword = $request->input('newPassword');

        // Find the user by their email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Hash the new password securely
        $hashedPassword = Hash::make($newPassword);

        // Update the user's password in the database
        $user->password = $hashedPassword;
        $user->save();

        return response()->json(['message' => 'Password reset successful']);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete(); // Revoke user tokens
            $success = true;
            $message = 'User logged out successfully';
        } catch (\Exception $ex) {
            $success = false;
            $message = 'An error occurred while logging out.';
        }
        $response = [
            'success' => $success,
            'message' => $message
        ];
        return response()->json($response);
    }

    public function updateFollowerCount($userID)
    {
        $user = User::find($userID);
        $updatedFollowCount = [
            'follower_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
        ];
        return response()->json($updatedFollowCount);
    }
    public function getUserById($id)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $user2 = User::find($id);
            if (!$user2) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user2->isFollowedByMe = $this->checkIfFollowedByUser($user, $user2);
            return response()->json(['user' => $user2]);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getUserByTag($tag)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $tag = '@' . ltrim($tag, '@');
            $user2 = User::where('UserTag', $tag)->first();
            if (!$user2) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user2->create_date =  'Joined ' . $user2->created_at->format('F Y');
            $user2->follower_count = $user2->followers()->count();
            $user2->following_count = $user2->following()->count();
            $user2->isFollowedByMe = $this->checkIfFollowedByUser($user, $user2);
            return response()->json(['user' => $user2]);
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    }

    public function getAllUsers(){
        $users = User::all();
        return response()->json(['users' => $users]);
    }

    private function checkIfFollowedByUser($user1, $user2)
    {
        return $user2->followers->contains('UserID', $user1->UserID);
    }

    public function getUser()
    {
       return Auth::user();
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $newName = $request->input('Name');
        $newDescription = $request->input('Description');
        $newProfilePicture = $request->file('profile_picture');
        $newBannerPicture = $request->file('banner_picture');

        $success = true;
        $messages = [];

        if ($newName) {
            $user->Name = $newName;
            $messages[] = 'Name updated successfully';
        } else {
            $success = false;
            $messages[] = 'Name cannot be empty';
        }

        if ($newDescription) {
            $user->description = $newDescription;
            $messages[] = 'Description updated successfully';
        } else {
            $user->description = '';
        }

        if ($newProfilePicture) {
            $image = $request->file('profile_picture');
            $path = $image->store('profile_pictures', 'public');
            if ($user->ProfilePicture) {
                $imagePath = public_path('storage/' . $user->ProfilePicture);
    
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                    Log::info('Image deleted: ' . $imagePath); // Log the image deletion
                } else {
                    Log::info('Image not found: ' . $imagePath); // Log if the image was not found
                }
            }
            $user->ProfilePicture = $path;
            $messages[] = 'Profile picture uploaded successfully';
        }
        if ($newBannerPicture) {
            $image = $request->file('banner_picture');
            $path = $image->store('banner_pictures', 'public');
            if ($user->Banner) {
                $imagePath = public_path('storage/' . $user->Banner);
    
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                    Log::info('Image deleted: ' . $imagePath); // Log the image deletion
                } else {
                    Log::info('Image not found: ' . $imagePath); // Log if the image was not found
                }
            }
            $user->Banner = $path;
            $messages[] = 'Banner uploaded successfully';
        }
        if (!$success) {
            $messages[] = 'Please select a valid image file.';
        }

        $user->save();

        $response = [
            'success' => $success,
            'messages' => $messages,
        ];

        return response()->json($response);
    }

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

    public function getUserMessages()
    {
        $user = Auth::user();

        $sentMessages = $user->messagesSent()->orderBy('created_at', 'desc')->get();
        $receivedMessages = $user->messagesReceived()->orderBy('created_at', 'desc')->get();

        $now = Carbon::now();

        foreach ($sentMessages as $message) {
            $message->sent_ago = $this->formatTimeAgo($message->created_at, $now);
        }

        foreach ($receivedMessages as $message) {
            $message->received_ago = $this->formatTimeAgo($message->created_at, $now);
        }
        Log::info($sentMessages);
        Log::info($receivedMessages);

        return response()->json(['sent_messages' => $sentMessages, 'received_messages' => $receivedMessages]);
    }

    public function deleteMessage($id)
    {
        $message = Messages::find($id);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }
        
        // Delete associated data like mentions, likes, etc., if applicable
        // Adjust these based on your message schema
        // $message->likes()->delete();
        // $message->mentions()->delete();
        // ...

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }
}
