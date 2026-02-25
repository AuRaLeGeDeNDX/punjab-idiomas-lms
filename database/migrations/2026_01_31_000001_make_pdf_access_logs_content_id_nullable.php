<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make content_id nullable in pdf_access_logs table to support
     * logging access attempts even when content doesn't exist or is invalid.
     * 
     * This allows us to log security events like:
     * - Attempts to access non-existent content
     * - Attempts to access wrong content types
     * - Invalid signed URL attempts
     */
    public function up(): void
    {
        Schema::table('pdf_access_logs', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['content_id']);
            
            // Make content_id nullable
            $table->foreignId('content_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('content_id')
                  ->references('id')
                  ->on('contents')
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
            $table->dropForeign(['content_id']);
            
            // Make content_id NOT nullable again
            $table->foreignId('content_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('content_id')
                  ->references('id')
                  ->on('contents')
                  ->onDelete('cascade');
        });
    }
};
