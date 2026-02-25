<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make user_id nullable in pdf_access_logs table to support
     * signed URL access without active session authentication.
     * 
     * Requirement 4.1: Signed URLs should work without active session authentication
     */
    public function up(): void
    {
        Schema::table('pdf_access_logs', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
            
            // Make user_id nullable
            $table->foreignId('user_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_access_logs', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Make user_id NOT nullable again
            $table->foreignId('user_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
