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
            // Add additional fields for enhanced content builder
            $table->json('metadata')->nullable(); // For storing additional content-specific data
            $table->string('external_url')->nullable(); // For video embeds, external links
            $table->text('alt_text')->nullable(); // For accessibility
            $table->json('settings')->nullable(); // For content-specific settings
            $table->timestamp('published_at')->nullable(); // For scheduling content
            $table->foreignId('created_by')->nullable()->constrained('users'); // Track who created
            $table->foreignId('updated_by')->nullable()->constrained('users'); // Track who updated
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'metadata',
                'external_url', 
                'alt_text',
                'settings',
                'published_at',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
