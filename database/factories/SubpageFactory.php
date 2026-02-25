<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Subpage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subpage>
 */
class SubpageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subpage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'order_index' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the subpage is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}