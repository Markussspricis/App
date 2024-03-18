<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Retrieve the user based on the provided email
            $user = User::where('email', $request->email)->first();

            // If user doesn't exist or password doesn't match, return error
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Email is not registered'],
                ]);
            }

            if (!password_verify($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Incorrect password'],
                ]);
            }

            // Create a new token for the user
            $token = $user->createToken('authToken')->plainTextToken;

            // Return the token along with any additional user data
            return response()->json(['message' => 'Login successful', 'token' => $token, 'user' => $user], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 401);
        } catch (Exception $e) {
            // Log the error
            Log::error('Login error: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'Login failed. Please try again later.'], 500);
        }
    }
}
