<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title_en');
            $table->string('title_mk')->nullable();
            $table->string('slug')->unique();
            $table->text('summary_en')->nullable();
            $table->text('summary_mk')->nullable();
            $table->longText('content_en');
            $table->longText('content_mk')->nullable();
            $table->string('difficulty')->default('beginner')->index();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();

            $table->index(['category_id', 'is_published', 'sort_order']);
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->foreignId('lesson_id')
                ->nullable()
                ->after('category_id')
                ->constrained('lessons')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn('lesson_id');
        });

        Schema::dropIfExists('lessons');
    }
};
