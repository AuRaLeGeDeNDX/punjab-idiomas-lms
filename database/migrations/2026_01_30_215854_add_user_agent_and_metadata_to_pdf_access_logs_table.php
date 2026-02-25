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
        Schema::table('pdf_access_logs', function (Blueprint $table) {
            // Add user_agent column if it doesn't exist
            if (!Schema::hasColumn('pdf_access_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
            
            // Add metadata column if it doesn't exist
            if (!Schema::hasColumn('pdf_access_logs', 'metadata')) {
                $table->json('metadata')->nullable()->after('user_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_access_logs', function (Blueprint $table) {
            if (Schema::hasColumn('pdf_access_logs', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            
            if (Schema::hasColumn('pdf_access_logs', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
