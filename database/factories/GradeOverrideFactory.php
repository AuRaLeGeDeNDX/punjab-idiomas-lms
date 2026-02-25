<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GradeOverride>
 */
class GradeOverrideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalScore = fake()->randomFloat(2, 0, 100);
        $newScore = fake()->randomFloat(2, 0, 100);

        return [
            'grade_id' => \App\Models\Grade::factory(),
            'admin_id' => \App\Models\User::factory()->create(['role' => 'Admin']),
            'original_score' => $originalScore,
            'new_score' => $newScore,
            'reason' => fake()->sentence(10),
            'overridden_at' => now(),
        ];
    }
}
