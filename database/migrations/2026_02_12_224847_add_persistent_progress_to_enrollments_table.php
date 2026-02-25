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
        Schema::table('enrollments', function (Blueprint $table) {
            // Add is_completed boolean for faster filtering/reporting
            if (!Schema::hasColumn('enrollments', 'is_completed')) {
                $table->boolean('is_completed')->default(false)->after('status');
            }
            
            // Add indexes for performance optimization
            $table->index('progress_percentage', 'enrollments_progress_percentage_index');
            $table->index('status', 'enrollments_status_index');
            $table->index('is_completed', 'enrollments_is_completed_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_progress_percentage_index');
            $table->dropIndex('enrollments_status_index');
            $table->dropIndex('enrollments_is_completed_index');
            $table->dropColumn('is_completed');
        });
    }
};
