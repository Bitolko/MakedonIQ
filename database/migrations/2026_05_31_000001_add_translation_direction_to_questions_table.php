<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->string('translation_direction')->nullable()->after('question_type')->index();
        });

        $quizId = DB::table('quizzes')
            ->where('slug', 'basic-macedonian-greetings')
            ->value('id');

        if (! $quizId) {
            return;
        }

        $questionUpdates = [
            'What does “добро утро” mean?' => 'Што значи „добро утро“ на англиски?',
            'What does “благодарам” mean?' => 'Што значи „благодарам“ на англиски?',
            'What does “пријатно” mean?' => 'Што значи „пријатно“ на англиски?',
            'What does “како си?” mean?' => 'Што значи „како си?“ на англиски?',
            'What does “добра ноќ” mean?' => 'Што значи „добра ноќ“ на англиски?',
        ];

        foreach ($questionUpdates as $questionEn => $questionMk) {
            DB::table('questions')
                ->where('quiz_id', $quizId)
                ->where('question_en', $questionEn)
                ->update([
                    'question_mk' => $questionMk,
                    'translation_direction' => 'mk_to_en',
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropIndex(['translation_direction']);
            $table->dropColumn('translation_direction');
        });
    }
};
