<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // Use this to reset the database after each test
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\Hash;

class TeacherApiTest extends TestCase
{
    // This trait is essential. It runs all our migrations before each test
    // and rolls them back after, so each test has a clean, empty database.
    use RefreshDatabase;

    /**
     * Test 1: A new teacher can successfully register.
     */
    public function test_a_new_teacher_can_register(): void
    {
        $registrationData = [
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act: Send a POST request to the registration endpoint
        $response = $this->postJson('/api/teachers/register', $registrationData);

        // Assert: Check that the response is correct
        $response
            ->assertStatus(201) // 201 = "Created"
            ->assertJsonStructure([ // Check if the JSON response has this structure
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'access_token',
            ])
            ->assertJsonFragment(['role' => 'teacher']); // Check that the role is 'teacher'

        // Assert: Check that the data was saved to the database correctly
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.com',
            'role' => 'teacher',
        ]);

        // Find the user we just created
        $user = User::where('email', 'teacher@example.com')->first();

        // Assert: Check that the associated teacher profile was also created
        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test 2: The public can view a list of *verified* teachers.
     */
    public function test_public_can_view_verified_teachers_list(): void
    {
        // Arrange: Create 2 verified teachers and 1 unverified teacher
        TeacherProfile::factory()->count(2)->create(['verified' => true]);
        TeacherProfile::factory()->count(1)->create(['verified' => false]);

        // Act: Call the public endpoint
        $response = $this->getJson('/api/teachers');

        // Assert
        $response->assertStatus(200);

        // The controller paginates, so the results are in the 'data' key.
        // We should only see the 2 verified teachers.
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test 3: The public can view a single verified teacher's profile.
     */
    public function test_public_can_view_single_verified_teacher_profile(): void
    {
        // Arrange: Create one verified teacher
        $profile = TeacherProfile::factory()->create(['verified' => true]);

        // Act
        $response = $this->getJson('/api/teachers/' . $profile->id);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $profile->id]); // Check that we got the right profile
    }

    /**
     * Test 4: A logged-in teacher can get their *own* profile.
     */
    public function test_authenticated_teacher_can_get_their_profile(): void
    {
        // Arrange: Create a teacher and their profile
        $teacherUser = User::factory()->teacher()->create();
        $profile = TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        // Act: Use 'actingAs' to simulate this user being logged in
        $response = $this->actingAs($teacherUser)
            ->getJson('/api/teacher/profile');

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['user_id' => $teacherUser->id]);
    }

    /**
     * Test 5: A logged-in teacher can update their *own* profile.
     */
    public function test_authenticated_teacher_can_update_their_profile(): void
    {
        // Arrange: Create a teacher with an old bio
        $teacherUser = User::factory()->teacher()->create();
        $profile = TeacherProfile::factory()->create(['user_id' => $teacherUser->id, 'bio' => 'Old bio']);
        $updateData = ['bio' => 'This is my new bio'];

        // Act: Send a PUT request as the logged-in teacher
        $response = $this->actingAs($teacherUser)
            ->putJson('/api/teacher/profile', $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['bio' => 'This is my new bio']); // Check JSON response

        // Assert: Check that the database was also updated
        $this->assertDatabaseHas('teacher_profiles', [
            'id' => $profile->id,
            'bio' => 'This is my new bio',
        ]);
    }

    /**
     * Test 6: An unauthenticated user cannot access protected routes.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/teacher/profile')
            ->assertStatus(401); // 401 = "Unauthorized"

        $this->putJson('/api/teacher/profile')
            ->assertStatus(401);
    }

    /**
     * Test 7: A student cannot access protected *teacher* routes.
     * This tests our 'role:teacher' middleware.
     */
    public function test_student_cannot_access_teacher_protected_routes(): void
    {
        // Arrange: Create a user with the 'student' role
        $studentUser = User::factory()->create(['role' => 'student']);

        // Act: Log in as the student and try to access the teacher profile
        $response = $this->actingAs($studentUser)
            ->getJson('/api/teacher/profile');

        // Assert
        $response->assertStatus(403); // 403 = "Forbidden"
    }
}