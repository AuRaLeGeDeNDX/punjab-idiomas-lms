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
        // Update existing content blocks to have proper section and visibility values
        DB::table('contents')->whereNull('section')->update([
            'section' => 'main_content',
            'section_order' => DB::raw('order_index'),
        ]);

        // Ensure visibility is properly set for existing content
        DB::table('contents')->where('visibility', '!=', 'student')
                            ->where('visibility', '!=', 'teacher_only')
                            ->update(['visibility' => 'student']);

        // Set section_order for content that doesn't have it
        DB::table('contents')->where('section_order', 0)->update([
            'section_order' => DB::raw('order_index'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for data migration only, no schema changes to reverse
    }
};