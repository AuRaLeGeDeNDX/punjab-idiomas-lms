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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->enum('assignment_type', ['quiz', 'essay', 'project', 'file_upload']);
            $table->decimal('max_score', 8, 2);
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('allow_late_submission')->default(false);
            $table->boolean('auto_grade')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['course_id', 'due_date']);
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
