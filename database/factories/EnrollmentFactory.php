<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $enrolledAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $progressPercentage = $this->faker->numberBetween(0, 100);
        
        return [
            'user_id' => \App\Models\User::factory(),
            'course_id' => \App\Models\Course::factory(),
            'enrolled_at' => $enrolledAt,
            'completed_at' => $progressPercentage >= 100 ? $this->faker->dateTimeBetween($enrolledAt, 'now') : null,
            'progress_percentage' => $progressPercentage,
            'last_accessed_at' => $this->faker->dateTimeBetween($enrolledAt, 'now'),
            'status' => $progressPercentage >= 100 ? 'completed' : $this->faker->randomElement(['active', 'active', 'active', 'dropped']),
        ];
    }
}
