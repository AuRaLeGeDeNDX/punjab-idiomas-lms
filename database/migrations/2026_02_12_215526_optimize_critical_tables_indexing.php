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
        // Add composite index to courses if it doesn't exist
        if (!Schema::hasIndex('courses', 'courses_teacher_published_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index(['teacher_id', 'is_published'], 'courses_teacher_published_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('courses', 'courses_teacher_published_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropIndex('courses_teacher_published_index');
            });
        }
    }
};
