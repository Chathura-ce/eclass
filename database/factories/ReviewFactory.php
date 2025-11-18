<?php

namespace Database\Factories;

use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create a session and get its linked student/teacher
        $session = Session::factory()->create();

        return [
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->paragraph(2),
        ];
    }
}
