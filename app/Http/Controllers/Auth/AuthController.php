<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a login request for any user role.
     */
    public function login(Request $request)
    {
        // 1. Validate the request
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Find the user by email
        $user = User::where('email', $request->email)->first();

        // 3. Check if user exists and password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Throw a standard validation exception
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 4. Create and return a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'user' => $user, // Return user data (name, email, role)
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully!'], 200);
    }
}
