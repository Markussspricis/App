<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
                'dob' => 'required|date_format:Y-m-d',
            ]);

            if (User::where('username', $request->username)->exists()) {
                throw ValidationException::withMessages(['username' => 'Username already exists']);
            }

            // Check if email already exists
            if (User::where('email', $request->email)->exists()) {
                throw ValidationException::withMessages(['email' => 'Email already exists']);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dob' => $request->dob,
            ]);

            // Generate an API token for the registered user
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json(['message' => 'User registered successfully', 'token' => $token], 201);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during user registration
            return response()->json(['error' => 'User registration failed: ' . $e->getMessage()], 500);
        }
    }
}
