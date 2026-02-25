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
            // Add assignment tracking fields
            $table->foreignId('assigned_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            $table->text('assignment_notes')->nullable()->after('assigned_at');
            
            // Add index for assignment tracking
            $table->index(['assigned_by', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropIndex(['assigned_by', 'assigned_at']);
            $table->dropColumn(['assigned_by', 'assigned_at', 'assignment_notes']);
        });
    }
};