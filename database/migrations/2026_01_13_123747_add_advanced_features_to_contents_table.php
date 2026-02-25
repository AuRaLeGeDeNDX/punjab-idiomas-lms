<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop the index first in a separate schema operations
        Schema::table('contents', function (Blueprint $table) {
             if (DB::getDriverName() !== 'sqlite') {
                 $table->dropIndex(['type', 'visibility']);
             }
        });

        // 2. Perform column modifications
        Schema::table('contents', function (Blueprint $table) {
            // Add section management
            $table->string('section', 50)->default('main_content')->after('type');
            $table->integer('section_order')->default(0)->after('section');
            
            // Enhanced visibility control (using change)
            // Skip change() on SQLite to avoid complex table rebuild issues with indexes
            if (DB::getDriverName() !== 'sqlite') {
                $table->enum('visibility', ['student', 'teacher_only'])->default('student')->change();
            }
        });

        // 3. Re-add indexes
        Schema::table('contents', function (Blueprint $table) {
            // Revert the index 
            if (DB::getDriverName() !== 'sqlite') {
                $table->index(['type', 'visibility']);
            }

            // Add composite index for performance
            $table->index(['subpage_id', 'section', 'section_order'], 'idx_subpage_section_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex('idx_subpage_section_order');
            $table->dropColumn(['section', 'section_order']);
            
            // Revert visibility to original type if needed
            $table->string('visibility')->nullable()->change();
        });
    }
};