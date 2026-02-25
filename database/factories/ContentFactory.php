<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition(): array
    {
        $types = ['text', 'image', 'pdf', 'audio', 'video'];
        $type = $this->faker->randomElement($types);
        
        return [
            'subpage_id' => Subpage::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'type' => $type,
            'content' => $type === 'text' ? $this->faker->paragraphs(3, true) : null,
            'file_path' => $type !== 'text' ? $this->faker->optional()->filePath() : null,
            'file_name' => $type !== 'text' ? $this->faker->optional()->word() . '.pdf' : null,
            'file_size' => $type !== 'text' ? $this->faker->optional()->numberBetween(1000, 10000000) : null,
            'mime_type' => $type !== 'text' ? $this->faker->optional()->mimeType() : null,
            'metadata' => [],
            'external_url' => $this->faker->optional(0.2)->url(),
            'alt_text' => $type === 'image' ? $this->faker->optional()->sentence() : null,
            'settings' => [],
            'visibility' => $this->faker->randomElement(['student', 'teacher_only']),
            'order_index' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(80),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Create a text content block.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
            'content' => $this->faker->paragraphs(3, true),
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'mime_type' => null,
            'external_url' => null,
            'alt_text' => null,
        ]);
    }

    /**
     * Create an image content block.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'content' => null,
            'file_path' => 'content/images/' . $this->faker->uuid() . '.jpg',
            'file_name' => $this->faker->word() . '.jpg',
            'file_size' => $this->faker->numberBetween(100000, 5000000),
            'mime_type' => 'image/jpeg',
            'alt_text' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create a PDF content block.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pdf',
            'content' => null,
            'file_path' => 'content/pdfs/' . $this->faker->uuid() . '.pdf',
            'file_name' => $this->faker->word() . '.pdf',
            'file_size' => $this->faker->numberBetween(500000, 50000000),
            'mime_type' => 'application/pdf',
            'external_url' => null,
            'alt_text' => null,
        ]);
    }

    /**
     * Create an audio content block.
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'audio',
            'content' => null,
            'file_path' => 'content/audio/' . $this->faker->uuid() . '.mp3',
            'file_name' => $this->faker->word() . '.mp3',
            'file_size' => $this->faker->numberBetween(1000000, 100000000),
            'mime_type' => 'audio/mpeg',
        ]);
    }

    /**
     * Create a video content block.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'content' => null,
            'file_path' => 'content/videos/' . $this->faker->uuid() . '.mp4',
            'file_name' => $this->faker->word() . '.mp4',
            'file_size' => $this->faker->numberBetween(10000000, 500000000),
            'mime_type' => 'video/mp4',
        ]);
    }

    /**
     * Create content visible to students.
     */
    public function visibleToStudents(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'student',
            'is_active' => true,
            'published_at' => now()->subHours(1),
        ]);
    }

    /**
     * Create content visible to teachers only.
     */
    public function teachersOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'teacher_only',
        ]);
    }

    /**
     * Create published content.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create draft content.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Create content with specific order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_index' => $order,
        ]);
    }
}