<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class StudentAuthController extends Controller
{
    /**
     * Handle a new student or parent registration request.
     */
    public function register(Request $request)
    {
        // 1. Determine the role based on the route
        $role = $request->routeIs('parents.register') ? 'parent' : 'student';

        // 2. Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 3. Create the User with the correct role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role, // Set role to 'student' or 'parent'
        ]);

        // 4. Create a token for the new user
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Return the new user and token
        return response()->json([
            'message' => ucfirst($role) . ' registered successfully!', // e.g., "Student registered..."
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}