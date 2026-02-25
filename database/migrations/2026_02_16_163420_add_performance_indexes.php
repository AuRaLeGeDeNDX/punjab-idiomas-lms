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
        Schema::table('contents', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        
        Schema::table('modules', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        
        Schema::table('submissions', function (Blueprint $table) {
            // Optimizes student dashboard queries looking for their submissions by status
            $table->index(['user_id', 'status']);
        });
        
        Schema::table('grades', function (Blueprint $table) {
            // Optimizes teacher dashboard "Recent Grades Published" query
            $table->index(['grader_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });
        
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['grader_id', 'is_published']);
        });
    }
};
