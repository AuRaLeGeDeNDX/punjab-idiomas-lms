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
        Schema::table('announcements', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['course_id']);
            
            // Modify the course_id column to be nullable
            $table->foreignId('course_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Drop the nullable foreign key constraint
            $table->dropForeign(['course_id']);
            
            // Revert course_id to be non-nullable
            $table->foreignId('course_id')->nullable(false)->change();
            
            // Re-add the non-nullable foreign key constraint
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }
};
