<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->enum('assignment_type', ['homework', 'project', 'quiz', 'exam', 'essay']);
            $table->enum('submission_type', ['text', 'file', 'both']);
            $table->decimal('max_score', 8, 2);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('is_public');
            $table->index(['teacher_id', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_templates');
    }
};
