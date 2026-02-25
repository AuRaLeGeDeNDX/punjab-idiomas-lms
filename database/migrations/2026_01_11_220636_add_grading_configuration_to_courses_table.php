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
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'grading_scheme')) {
                $table->json('grading_scheme')->nullable()->after('prerequisites');
            }
            if (!Schema::hasColumn('courses', 'passing_grade')) {
                $table->decimal('passing_grade', 5, 2)->default(60.00)->after('grading_scheme');
            }
            if (!Schema::hasColumn('courses', 'auto_publish_grades')) {
                $table->boolean('auto_publish_grades')->default(false)->after('passing_grade');
            }
        });

        // Note: These columns are already added by enhance_submissions_and_grades_tables migration
        // Only add if they don't exist
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
            if (!Schema::hasColumn('grades', 'rubric_scores')) {
                $table->json('rubric_scores')->nullable()->after('feedback');
            }
            if (!Schema::hasColumn('grades', 'grade_letter')) {
                $table->string('grade_letter', 2)->nullable()->after('score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['grading_scheme', 'passing_grade', 'auto_publish_grades']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'rubric_scores', 'grade_letter']);
        });
    }
};