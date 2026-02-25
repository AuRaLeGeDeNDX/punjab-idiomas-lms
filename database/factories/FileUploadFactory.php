<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileUpload>
 */
class FileUploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalName = $this->faker->word() . '.' . $this->faker->randomElement(['pdf', 'docx', 'jpg', 'png', 'mp4']);
        $storedName = $this->faker->uuid() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'mp4' => 'video/mp4'
        ];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        
        return [
            'user_id' => \App\Models\User::factory(),
            'course_id' => $this->faker->boolean(70) ? \App\Models\Course::factory() : null,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => 'uploads/' . $storedName,
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'mime_type' => $mimeTypes[$extension] ?? 'application/octet-stream',
            'file_hash' => hash('sha256', $this->faker->text()),
            'is_public' => $this->faker->boolean(30),
            'uploaded_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
