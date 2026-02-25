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
        // Create rubrics table
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('assignment_id');
        });

        // Create rubric_criteria table
        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained()->onDelete('cascade');
            $table->string('criterion_name');
            $table->text('criterion_description')->nullable();
            $table->decimal('max_points', 8, 2);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index('rubric_id');
            $table->index(['rubric_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');
    }
};
