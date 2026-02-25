<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subpage_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('question'); // Visible to students
            $table->longText('answer'); // ONLY visible to teachers/admins
            $table->text('instructions')->nullable();
            $table->enum('submission_type', ['text', 'file', 'both'])->default('text');
            $table->integer('max_score')->default(100);
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subpage_id', 'is_active']);
            $table->index(['subpage_id', 'order_index']);
            $table->index('due_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('exercises');
    }
};