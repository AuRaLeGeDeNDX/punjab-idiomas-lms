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
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // For MySQL, we need to alter the ENUM column
            DB::statement("ALTER TABLE assignments MODIFY COLUMN assignment_type ENUM('homework', 'project', 'quiz', 'exam', 'essay') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support ENUM or MODIFY COLUMN
            // The column is already a string type, so no changes needed
            // Just ensure the values are valid in the application layer
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // Revert to original ENUM values
            DB::statement("ALTER TABLE assignments MODIFY COLUMN assignment_type ENUM('quiz', 'essay', 'project', 'file_upload') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support ENUM or MODIFY COLUMN
            // No changes needed
        }
    }
};
