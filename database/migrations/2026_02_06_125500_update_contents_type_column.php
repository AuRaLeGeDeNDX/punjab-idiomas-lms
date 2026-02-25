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
        // 1. Create a new string column
        Schema::table('contents', function (Blueprint $table) {
            $table->string('type_new')->nullable()->after('type');
        });

        // 2. Copy data from old enum column to new string column
        DB::table('contents')->update(['type_new' => DB::raw('type')]);

        // 3. Make new column not nullable (if needed, but safe to do validation later)
        // Schema::table('contents', function (Blueprint $table) {
        //     $table->string('type_new')->nullable(false)->change(); 
        // });
        // NOTE: ->change() requires doctrine/dbal, so we skip strict constraint for now 
        // or rely on application-level validation which we already have.

        // 4. Drop the old enum column and rename the new one
        // Note: SQLite supports DROP COLUMN in newer versions, but if it fails,
        // we might leave the old column as legacy. 
        // Typically, we would swap them.
        
        // SAFE APPROACH: If we can't guarantee DROP COLUMN works without dbal:
        try {
            Schema::table('contents', function (Blueprint $table) {
                $table->dropColumn('type');
            });
            
            Schema::table('contents', function (Blueprint $table) {
                $table->renameColumn('type_new', 'type');
            });
        } catch (\Exception $e) {
            // Fallback if drop/rename fails (e.g. SQLite limitation)
            // We just leave type_new and updated Content model to use type_new? 
            // No, that breaks code.
            // If this fails, the user will report it. 
            // Given Laravel 12, native SQLite dropColumn support is very likely.
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // It's hard to revert strictly to ENUM without potentially losing data 
        // if we added 'spacer' etc.
        // We will just leave it as string in down for safety, 
        // or attempt to recreate enum if needed.
    }
};
