<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class StudentApiTest extends TestCase
{
    use RefreshDatabase; // Resets the database for each test

    /**
     * Test 1: A new student can successfully register.
     */
    public function test_a_new_student_can_register(): void
    {
        $registrationData = [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act: Send a POST request to the student registration endpoint
        $response = $this->postJson('/api/students/register', $registrationData);

        // Assert: Check that the response is correct
        $response
            ->assertStatus(201) // 201 = "Created"
            ->assertJsonFragment([
                'message' => 'Student registered successfully!',
                'role' => 'student'
            ])
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'access_token',
            ]);

        // Assert: Check that the user was saved to the database with the correct role
        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'role' => 'student',
        ]);
    }

    /**
     * Test 2: A new parent can successfully register.
     */
    public function test_a_new_parent_can_register(): void
    {
        $registrationData = [
            'name' => 'Test Parent',
            'email' => 'parent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act: Send a POST request to the parent registration endpoint
        $response = $this->postJson('/api/parents/register', $registrationData);

        // Assert: Check that the response is correct
        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Parent registered successfully!',
                'role' => 'parent'
            ]);

        // Assert: Check that the user was saved to the database with the correct role
        $this->assertDatabaseHas('users', [
            'email' => 'parent@example.com',
            'role' => 'parent',
        ]);
    }

    /**
     * Test 3: Registration fails if the email is already taken.
     */
    public function test_registration_fails_with_a_duplicate_email(): void
    {
        // Arrange: Create a user first
        User::factory()->create(['email' => 'taken@example.com']);

        $registrationData = [
            'name' => 'Test Student',
            'email' => 'taken@example.com', // This email is already used
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act: Try to register with the same email
        $response = $this->postJson('/api/students/register', $registrationData);

        // Assert: Check for a 422 Unprocessable Entity status (validation error)
        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['email'] // Check that the response specifies an error with the email
            ]);
    }
}