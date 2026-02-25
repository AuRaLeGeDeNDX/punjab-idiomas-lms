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
            // Add secure storage fields for enhanced file security
            $table->string('original_filename')->nullable()->comment('Original filename as uploaded by user');
            $table->string('file_hash', 64)->nullable()->comment('SHA256 hash of file content for integrity verification');
            $table->string('storage_disk', 50)->default('public')->comment('Storage disk used (public, protected, etc.)');
            
            // Add index on file_hash for deduplication queries
            $table->index('file_hash', 'contents_file_hash_index');
            
            // Add index on storage_disk for filtering
            $table->index('storage_disk', 'contents_storage_disk_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('contents_file_hash_index');
            $table->dropIndex('contents_storage_disk_index');
            
            // Drop columns
            $table->dropColumn(['original_filename', 'file_hash', 'storage_disk']);
        });
    }
};