<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            if (! Schema::hasColumn('quizzes', 'is_demo')) {
                $table->boolean('is_demo')->default(false)->index();
            }
        });

        Schema::table('lessons', function (Blueprint $table): void {
            if (! Schema::hasColumn('lessons', 'is_demo')) {
                $table->boolean('is_demo')->default(false)->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            if (Schema::hasColumn('lessons', 'is_demo')) {
                $table->dropColumn('is_demo');
            }
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            if (Schema::hasColumn('quizzes', 'is_demo')) {
                $table->dropColumn('is_demo');
            }
        });
    }
};
