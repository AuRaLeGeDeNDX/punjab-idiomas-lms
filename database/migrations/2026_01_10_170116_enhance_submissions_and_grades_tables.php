<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Enhance submissions table if it exists
        if (Schema::hasTable('submissions')) {
            Schema::table('submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('submissions', 'submission_type')) {
                    $table->enum('submission_type', ['text', 'file', 'both'])->default('text')->after('status');
                }
                if (!Schema::hasColumn('submissions', 'word_count')) {
                    $table->integer('word_count')->nullable()->after('submission_type');
                }
                if (!Schema::hasColumn('submissions', 'file_size_bytes')) {
                    $table->bigInteger('file_size_bytes')->nullable()->after('word_count');
                }
            });
            
            // Add indexes - skip if they already exist
            // Note: ['assignment_id', 'status'] already exists from create_submissions_table
            // Note: ['user_id', 'submitted_at'] already exists from create_submissions_table
            try {
                Schema::table('submissions', function (Blueprint $table) {
                    $table->index(['user_id', 'assignment_id']);
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
            
            try {
                Schema::table('submissions', function (Blueprint $table) {
                    $table->index('submitted_at');
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
        }

        // Enhance grades table if it exists
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                if (!Schema::hasColumn('grades', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('is_published');
                }
                if (!Schema::hasColumn('grades', 'rubric_scores')) {
                    $table->json('rubric_scores')->nullable()->after('published_at');
                }
                if (!Schema::hasColumn('grades', 'grade_letter')) {
                    $table->string('grade_letter', 2)->nullable()->after('rubric_scores');
                }
            });
            
            // Add indexes - skip if they already exist
            try {
                Schema::table('grades', function (Blueprint $table) {
                    $table->index(['submission_id', 'is_published']);
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
            
            try {
                Schema::table('grades', function (Blueprint $table) {
                    $table->index('graded_at');
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
            
            try {
                Schema::table('grades', function (Blueprint $table) {
                    $table->index('published_at');
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('submissions')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn(['submission_type', 'word_count', 'file_size_bytes']);
                $table->dropIndex(['assignment_id', 'status']);
                $table->dropIndex(['user_id', 'assignment_id']);
                $table->dropIndex(['submitted_at']);
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropColumn(['published_at', 'rubric_scores', 'grade_letter']);
                $table->dropIndex(['submission_id', 'is_published']);
                $table->dropIndex(['graded_at']);
                $table->dropIndex(['published_at']);
            });
        }
    }
};