<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subpage_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'pdf', 'image', 'audio', 'video', 'file']);
            $table->longText('content')->nullable(); // For text content
            $table->string('file_path')->nullable(); // For file-based content
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->enum('visibility', ['student', 'teacher_only'])->default('student');
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subpage_id', 'order_index']);
            $table->index(['subpage_id', 'is_active']);
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->index(['type', 'visibility']);
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('contents');
    }
};