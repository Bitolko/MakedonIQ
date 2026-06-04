<?php

namespace App\Models;

use Database\Factories\QuizAttemptAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    /** @use HasFactory<QuizAttemptAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'answer_id',
        'is_correct',
        'points_awarded',
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
    ];

    protected function casts(): array
    {
        return [
            'quiz_attempt_id' => 'integer',
            'question_id' => 'integer',
            'answer_id' => 'integer',
            'is_correct' => 'boolean',
            'points_awarded' => 'integer',
            'question_metadata_snapshot' => 'array',
            'question_points_snapshot' => 'integer',
        ];
    }

    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'answer_id');
    }
}
