<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purge>
 */
class PurgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => fake()->unique()->numberBetween(1, 1000000),
            'posted_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'text' => fake()->optional(0.9)->sentence(20),
        ];
    }
}
