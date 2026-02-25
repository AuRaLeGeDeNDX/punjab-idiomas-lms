<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'teacher_id' => \App\Models\User::factory(),
            'category' => $this->faker->randomElement(['Programming', 'Mathematics', 'Science', 'Literature', 'History']),
            'difficulty_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration_hours' => $this->faker->numberBetween(10, 200),
            'max_students' => $this->faker->numberBetween(10, 100),
            'enrollment_start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'enrollment_end_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'course_start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'course_end_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'is_published' => $this->faker->boolean(70),
            'is_featured' => $this->faker->boolean(20),
        ];
    }
}
