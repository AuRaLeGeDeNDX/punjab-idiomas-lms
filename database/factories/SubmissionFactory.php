<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $submittedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $isGraded = $this->faker->boolean(60);
        
        return [
            'assignment_id' => \App\Models\Assignment::factory(),
            'user_id' => \App\Models\User::factory(),
            'content' => $this->faker->paragraph(5),
            'file_paths' => $this->faker->boolean(40) ? [
                'uploads/submissions/' . $this->faker->uuid() . '.pdf',
                'uploads/submissions/' . $this->faker->uuid() . '.docx'
            ] : null,
            'submitted_at' => $submittedAt,
            'is_late' => $this->faker->boolean(20),
            'attempt_number' => $this->faker->numberBetween(1, 3),
            'status' => $isGraded ? 'graded' : $this->faker->randomElement(['draft', 'submitted', 'submitted']),
        ];
    }
}
