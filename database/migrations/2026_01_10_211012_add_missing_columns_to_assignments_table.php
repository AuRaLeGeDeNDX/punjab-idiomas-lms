<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Add subpage relationship if not exists
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
    }

    public function down()
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'subpage_id')) {
                $table->dropForeign(['subpage_id']);
                $table->dropColumn('subpage_id');
            }
            if (Schema::hasColumn('assignments', 'submission_type')) {
                $table->dropColumn('submission_type');
            }
            if (Schema::hasColumn('assignments', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('assignments', 'order_index')) {
                $table->dropColumn('order_index');
            }
            if (Schema::hasColumn('assignments', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('assignments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};