<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherProfileController extends Controller
{
    /**
     * PUBLIC: Get a list of all verified teachers.
     * This is for the student search/discovery page.
     */
    public function index()
    {
        // Only show verified teachers, load their user data (name), and paginate
        $teachers = TeacherProfile::where('verified', true)
            ->with('user:id,name') // Only get the id and name from the user table
            ->paginate(15);

        return response()->json($teachers);
    }

    /**
     * PUBLIC: Get a single teacher's public profile.
     */
    public function show($id)
    {
        $profile = TeacherProfile::with('user:id,name')
            ->where('verified', true)
            ->findOrFail($id);

        // We could also add: ->load('reviews') later

        return response()->json($profile);
    }

    /**
     * PROTECTED: Get the *currently logged-in* teacher's profile.
     * Used for the "Edit Profile" page.
     */
    public function edit(Request $request)
    {
        $user = $request->user(); // Get the authenticated user

        // Find the profile, or create a new one if it somehow doesn't exist
        $profile = $user->teacherProfile()->firstOrCreate(
            ['user_id' => $user->id]
        );

        return response()->json($profile);
    }

    /**
     * PROTECTED: Update the *currently logged-in* teacher's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user(); // Get the authenticated user

        $validator = Validator::make($request->all(), [
            'bio' => ['sometimes', 'string', 'nullable', 'max:2000'],
            'availability' => ['sometimes', 'json', 'nullable'],
            // Add rules for other fields later (e.g., subjects, location)
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the profile and update it
        $profile = $user->teacherProfile;
        $profile->update($validator->validated());

        return response()->json([
            'message' => 'Profile updated successfully!',
            'profile' => $profile,
        ]);
    }
}