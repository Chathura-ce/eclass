<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $started = fake()->dateTimeThisMonth();
        return [
            'booking_id' => Booking::factory(), // Create a new booking
            'started_at' => $started,
            'ended_at' => (clone $started)->modify('+1 hour'),
            'status' => 'completed',
            'video_link' => 'https://meet.example.com/' . fake()->uuid(),
            'teacher_notes' => fake()->sentence(),
        ];
    }
}
