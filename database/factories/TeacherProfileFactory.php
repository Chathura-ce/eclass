<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(), // Automatically create a 'teacher' user
            'bio' => fake()->paragraph(3),
            'verified' => fake()->boolean(70), // 70% chance of being verified
            'free_promo_ends_at' => now()->addWeeks(2), // Free promo for 2 weeks [cite: 22]
            'availability' => json_encode([ // Sample availability data
                'monday' => ['09:00-11:00', '14:00-16:00'],
                'tuesday' => ['10:00-12:00'],
            ]),
        ];
    }
}
