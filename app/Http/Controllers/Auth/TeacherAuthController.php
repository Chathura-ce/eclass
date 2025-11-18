<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Carbon;

class TeacherAuthController extends Controller
{
    /**
     * Handle a new teacher registration request.
     * This creates a User and a TeacherProfile.
     */
    public function register(Request $request)
    {
        // 1. Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Create the User with the 'teacher' role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher', // Set role to 'teacher'
        ]);

        // 3. Create the associated TeacherProfile
        // This gives them the "free starter promo" as per the MVP plan
        TeacherProfile::create([
            'user_id' => $user->id,
            'free_promo_ends_at' => Carbon::now()->addWeeks(2), // 2-week free promo
        ]);

        // 4. Create a token for the new teacher
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Return the new user and token
        return response()->json([
            'message' => 'Teacher registered successfully!',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}