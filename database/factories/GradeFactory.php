<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxScore = $this->faker->numberBetween(50, 100);
        $score = $this->faker->numberBetween(0, $maxScore);
        
        return [
            'submission_id' => \App\Models\Submission::factory(),
            'grader_id' => \App\Models\User::factory(),
            'score' => $score,
            'feedback' => $this->faker->paragraph(2),
            'graded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'is_published' => $this->faker->boolean(80),
        ];
    }
}
