<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordController extends Controller
{
    public function update(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Retrieve the user based on the provided email
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email is not registered'],
            ]);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Create a new token for the user
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['message' => 'Password updated successfully', 'token' => $token], 200);
    }
}
