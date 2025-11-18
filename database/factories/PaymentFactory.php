<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $booking = Booking::factory()->create(['status' => 'confirmed']); // Payments are for confirmed bookings

        return [
            'booking_id' => $booking->id,
            'student_id' => $booking->student_id,
            'amount' => $booking->price,
            'status' => fake()->randomElement(['paid', 'released']), // Assume most are paid or released
            'gateway' => 'payhere',
            'gateway_ref' => 'ph_'. fake()->bothify('??#######'),
            'paid_at' => now()->subDays(fake()->numberBetween(1, 10)),
            'released_at' => fake()->optional(0.7)->dateTime(), // 70% chance it's been released
        ];
    }
}
