<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip if assignments table doesn't exist yet
        if (!Schema::hasTable('assignments')) {
            return;
        }

        Schema::table('assignments', function (Blueprint $table) {
            // Add subpage relationship
            if (!Schema::hasColumn('assignments', 'subpage_id')) {
                $table->foreignId('subpage_id')->nullable()->after('module_id')->constrained()->onDelete('cascade');
            }
            
            // Add additional fields for enhanced functionality
            if (!Schema::hasColumn('assignments', 'submission_type')) {
                $table->enum('submission_type', ['text', 'file', 'both'])->default('text')->after('assignment_type');
            }
            
            if (!Schema::hasColumn('assignments', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_published');
            }
            
            if (!Schema::hasColumn('assignments', 'order_index')) {
                $table->integer('order_index')->default(0)->after('is_active');
            }
            
            if (!Schema::hasColumn('assignments', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
            
            if (!Schema::hasColumn('assignments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Add indexes if table exists
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->index(['subpage_id', 'is_active']);
                $table->index(['subpage_id', 'order_index']);
                $table->index('due_date');
                $table->index('published_at');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropForeign(['subpage_id']);
                $table->dropColumn([
                    'subpage_id', 
                    'submission_type', 
                    'is_active', 
                    'order_index', 
                    'published_at'
                ]);
                $table->dropSoftDeletes();
                $table->dropIndex(['subpage_id', 'is_active']);
                $table->dropIndex(['subpage_id', 'order_index']);
                $table->dropIndex(['due_date']);
                $table->dropIndex(['published_at']);
            });
        }
    }
};