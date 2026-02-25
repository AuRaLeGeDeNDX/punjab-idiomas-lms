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
        Schema::create('content_block_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_block_id')->constrained('contents')->onDelete('cascade');
            $table->integer('version_number');
            $table->json('version_data');
            $table->enum('action_type', ['create', 'update', 'reorder', 'move_section', 'visibility_change', 'delete']);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index(['content_block_id', 'version_number'], 'idx_content_versions');
            $table->index('created_at', 'idx_version_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_block_versions');
    }
};