<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assignmentType = $this->faker->randomElement(['quiz', 'essay', 'project', 'file_upload']);
        $submissionType = $this->faker->randomElement(['text', 'file', 'both']);
        
        return [
            'course_id' => \App\Models\Course::factory(),
            'module_id' => \App\Models\Module::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(2),
            'instructions' => $this->faker->paragraph(3),
            'assignment_type' => $assignmentType,
            'submission_type' => $submissionType,
            'max_score' => $this->faker->numberBetween(10, 100),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'is_published' => $this->faker->boolean(75),
            'is_active' => true,
            'order_index' => 0,
            'allow_late_submission' => $this->faker->boolean(30),
            'auto_grade' => $assignmentType === 'quiz' ? $this->faker->boolean(80) : false,
        ];
    }

    /**
     * Indicate that the assignment is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
