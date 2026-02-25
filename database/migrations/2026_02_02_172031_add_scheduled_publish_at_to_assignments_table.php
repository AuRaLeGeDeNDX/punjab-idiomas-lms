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
        Schema::table('assignments', function (Blueprint $table) {
            $table->timestamp('scheduled_publish_at')->nullable()->after('published_at');
            $table->index('scheduled_publish_at', 'idx_scheduled_publish');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_scheduled_publish');
            $table->dropColumn('scheduled_publish_at');
        });
    }
};
