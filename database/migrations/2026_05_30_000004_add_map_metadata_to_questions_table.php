<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->string('question_type')->default('multiple_choice')->after('quiz_id')->index();
            $table->json('metadata')->nullable()->after('question_type');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropIndex(['question_type']);
            $table->dropColumn(['question_type', 'metadata']);
        });
    }
};
