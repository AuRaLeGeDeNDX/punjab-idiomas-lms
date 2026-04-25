<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing broken table if it exists
        Schema::dropIfExists('course_teacher');

        Schema::create('course_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
        });

        // Migrate existing teacher_id data
        $courses = DB::table('courses')->whereNotNull('teacher_id')->get();
        foreach ($courses as $course) {
            DB::table('course_teacher')->insert([
                'course_id' => $course->id,
                'user_id' => $course->teacher_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_teacher');
    }
};
