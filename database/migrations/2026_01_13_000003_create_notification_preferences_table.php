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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('database_notifications')->default(true);
            $table->boolean('course_announcement')->default(true);
            $table->boolean('assignment_reminder')->default(true);
            $table->boolean('grade_published')->default(true);
            $table->boolean('direct_message')->default(true);
            $table->boolean('system_alert')->default(true);
            $table->boolean('forum_reply')->default(true);
            $table->boolean('assignment_published')->default(true);
            $table->boolean('course_update')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};