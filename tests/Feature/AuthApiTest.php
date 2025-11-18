<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: A user can log in with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Act: Attempt to log in
        $response = $this->postJson('/api/login', $loginData);

        // Assert
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'access_token',
            ])
            ->assertJsonFragment(['email' => 'test@example.com']);
    }

    /**
     * Test 2: A user cannot log in with an incorrect password.
     */
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrong-password', // Incorrect password
        ];

        // Act: Attempt to log in
        $response = $this->postJson('/api/login', $loginData);

        // Assert: Should be a 422 Unprocessable Entity (validation) error
        $response
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]); // Check for the specific error
    }

    /**
     * Test 3: A logged-in user can log out.
     */
    public function test_authenticated_user_can_logout(): void
    {
        // Arrange: Create a user and a token for them
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Act: Call the logout endpoint *with* the token in the header
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // Assert
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logged out successfully!']);

        // Assert: Verify the token was actually deleted from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => 'test-token',
            'tokenable_id' => $user->id
        ]);
    }
}