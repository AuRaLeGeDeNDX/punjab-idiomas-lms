<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => \App\Models\Course::factory(),
            'title' => $this->faker->sentence(2),
            'description' => $this->faker->paragraph(2),
            'content' => [
                'lessons' => [
                    [
                        'title' => $this->faker->sentence(3),
                        'content' => $this->faker->paragraph(5),
                        'type' => 'text'
                    ],
                    [
                        'title' => $this->faker->sentence(3),
                        'content' => $this->faker->paragraph(3),
                        'type' => 'video',
                        'url' => $this->faker->url()
                    ]
                ]
            ],
            'order_index' => $this->faker->numberBetween(1, 10),
            'is_published' => $this->faker->boolean(80),
        ];
    }
}
