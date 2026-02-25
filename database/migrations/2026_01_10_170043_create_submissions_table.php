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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->json('file_paths')->nullable(); // Array of file paths
            $table->timestamp('submitted_at')->useCurrent();
            $table->boolean('is_late')->default(false);
            $table->unsignedInteger('attempt_number')->default(1);
            $table->enum('status', ['draft', 'submitted', 'graded'])->default('submitted');
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['assignment_id', 'user_id', 'attempt_number']);
            $table->index(['assignment_id', 'status']);
            $table->index(['user_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
