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
            // Make session_token nullable and add default value
            $table->string('session_token')->nullable()->default(null)->change();
            
            // Also ensure access_granted has a default
            $table->boolean('access_granted')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_access_logs', function (Blueprint $table) {
            // Revert changes if needed
            $table->string('session_token')->nullable(false)->change();
        });
    }
};
