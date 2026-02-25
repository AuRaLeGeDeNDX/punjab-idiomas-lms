<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if course_modules table exists and modules doesn't
        if (Schema::hasTable('course_modules') && !Schema::hasTable('modules')) {
            Schema::rename('course_modules', 'modules');
        }
        
        // If neither table exists, skip this migration (modules will be created later)
        if (!Schema::hasTable('modules')) {
            return;
        }

        // Update modules table structure only if columns don't exist
        Schema::table('modules', function (Blueprint $table) {
            // Add type column if it doesn't exist
            if (!Schema::hasColumn('modules', 'type')) {
                $table->enum('type', ['reading', 'listening', 'writing', 'oral_interaction'])
                      ->after('description')
                      ->default('reading');
            }

            // Add is_active column if it doesn't exist
            if (!Schema::hasColumn('modules', 'is_active')) {
                $table->boolean('is_active')->after('order_index')->default(true);
            }

            // Add soft deletes if it doesn't exist
            if (!Schema::hasColumn('modules', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Note: Indexes are already created by create_course_modules_table migration
        // No need to add them again
    }

    public function down()
    {
        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn(['type', 'is_active']);
                $table->dropSoftDeletes();
                $table->dropIndex(['course_id', 'type']);
                $table->dropIndex(['is_active']);
            });
        }
    }
};