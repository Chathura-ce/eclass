<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory(), // Create a new student by default
            'teacher_id' => User::factory()->teacher(), // Create a new teacher by default
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
            'price' => fake()->randomElement([1000.00, 1500.00, 2000.00]),
        ];
    }
}
