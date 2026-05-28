<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title_en');
            $table->string('title_mk');
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_mk')->nullable();
            $table->string('difficulty')->default('beginner')->index();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('points_per_question')->default(10);
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['category_id', 'is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
