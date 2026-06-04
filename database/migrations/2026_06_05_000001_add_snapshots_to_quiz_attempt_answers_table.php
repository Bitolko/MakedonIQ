<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table): void {
            $table->string('question_type_snapshot')->nullable()->after('points_awarded');
            $table->string('translation_direction_snapshot')->nullable()->after('question_type_snapshot');
            $table->json('question_metadata_snapshot')->nullable()->after('translation_direction_snapshot');
            $table->text('question_en_snapshot')->nullable()->after('question_metadata_snapshot');
            $table->text('question_mk_snapshot')->nullable()->after('question_en_snapshot');
            $table->text('explanation_en_snapshot')->nullable()->after('question_mk_snapshot');
            $table->text('explanation_mk_snapshot')->nullable()->after('explanation_en_snapshot');
            $table->text('selected_answer_en_snapshot')->nullable()->after('explanation_mk_snapshot');
            $table->text('selected_answer_mk_snapshot')->nullable()->after('selected_answer_en_snapshot');
            $table->text('correct_answer_en_snapshot')->nullable()->after('selected_answer_mk_snapshot');
            $table->text('correct_answer_mk_snapshot')->nullable()->after('correct_answer_en_snapshot');
            $table->unsignedInteger('question_points_snapshot')->nullable()->after('correct_answer_mk_snapshot');
        });

        DB::table('quiz_attempt_answers')
            ->leftJoin('questions', 'quiz_attempt_answers.question_id', '=', 'questions.id')
            ->leftJoin('answers as selected_answers', 'quiz_attempt_answers.answer_id', '=', 'selected_answers.id')
            ->leftJoin('answers as correct_answers', function ($join): void {
                $join->on('correct_answers.question_id', '=', 'questions.id')
                    ->where('correct_answers.is_correct', true);
            })
            ->select([
                'quiz_attempt_answers.id',
                'questions.question_type',
                'questions.translation_direction',
                'questions.metadata',
                'questions.question_en',
                'questions.question_mk',
                'questions.explanation_en',
                'questions.explanation_mk',
                'questions.points',
                'selected_answers.answer_en as selected_answer_en',
                'selected_answers.answer_mk as selected_answer_mk',
                'correct_answers.answer_en as correct_answer_en',
                'correct_answers.answer_mk as correct_answer_mk',
            ])
            ->orderBy('quiz_attempt_answers.id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('quiz_attempt_answers')
                        ->where('id', $row->id)
                        ->update([
                            'question_type_snapshot' => $row->question_type,
                            'translation_direction_snapshot' => $row->translation_direction,
                            'question_metadata_snapshot' => $row->metadata,
                            'question_en_snapshot' => $row->question_en,
                            'question_mk_snapshot' => $row->question_mk,
                            'explanation_en_snapshot' => $row->explanation_en,
                            'explanation_mk_snapshot' => $row->explanation_mk,
                            'selected_answer_en_snapshot' => $row->selected_answer_en,
                            'selected_answer_mk_snapshot' => $row->selected_answer_mk,
                            'correct_answer_en_snapshot' => $row->correct_answer_en,
                            'correct_answer_mk_snapshot' => $row->correct_answer_mk,
                            'question_points_snapshot' => $row->points,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table): void {
            $table->dropColumn([
                'question_type_snapshot',
                'translation_direction_snapshot',
                'question_metadata_snapshot',
                'question_en_snapshot',
                'question_mk_snapshot',
                'explanation_en_snapshot',
                'explanation_mk_snapshot',
                'selected_answer_en_snapshot',
                'selected_answer_mk_snapshot',
                'correct_answer_en_snapshot',
                'correct_answer_mk_snapshot',
                'question_points_snapshot',
            ]);
        });
    }
};
