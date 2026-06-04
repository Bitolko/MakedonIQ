<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttemptManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_filter_and_open_attempt_results(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $learner = User::factory()->create([
            'name' => 'Elena Learner',
            'email' => 'elena@example.com',
        ]);
        $category = Category::factory()->create([
            'name_en' => 'Macedonian Language',
            'slug' => 'macedonian-language',
        ]);
        $quiz = Quiz::factory()->create([
            'category_id' => $category->id,
            'title_en' => 'Basic Macedonian Greetings',
            'slug' => 'basic-macedonian-greetings',
        ]);
        $question = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'question_type' => 'multiple_choice',
            'translation_direction' => 'mk_to_en',
            'question_en' => 'Live question after edit?',
            'question_mk' => 'Живо прашање по измена?',
            'explanation_en' => 'Live explanation after edit.',
            'explanation_mk' => 'Живо објаснување по измена.',
            'points' => 10,
        ]);
        $wrongAnswer = Answer::factory()->create([
            'question_id' => $question->id,
            'answer_en' => 'Edited learner answer',
            'answer_mk' => 'Изменет одговор',
            'is_correct' => false,
        ]);
        $correctAnswer = Answer::factory()->correct()->create([
            'question_id' => $question->id,
            'answer_en' => 'Edited correct answer',
            'answer_mk' => 'Изменет точен одговор',
        ]);
        $attempt = QuizAttempt::factory()->create([
            'user_id' => $learner->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'total_questions' => 1,
            'correct_answers' => 0,
            'percentage' => 0,
            'passed' => false,
        ]);

        QuizAttemptAnswer::factory()->create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'answer_id' => $wrongAnswer->id,
            'is_correct' => false,
            'points_awarded' => 0,
            'question_type_snapshot' => 'multiple_choice',
            'translation_direction_snapshot' => 'mk_to_en',
            'question_metadata_snapshot' => ['source' => 'snapshot'],
            'question_en_snapshot' => 'Snapshot question?',
            'question_mk_snapshot' => 'Снимка прашање?',
            'explanation_en_snapshot' => 'Snapshot explanation.',
            'explanation_mk_snapshot' => 'Снимка објаснување.',
            'selected_answer_en_snapshot' => 'Snapshot learner answer',
            'selected_answer_mk_snapshot' => 'Снимка одговор',
            'correct_answer_en_snapshot' => 'Snapshot correct answer',
            'correct_answer_mk_snapshot' => 'Снимка точен одговор',
            'question_points_snapshot' => 10,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/attempts?search=elena&status=review')
            ->assertOk()
            ->assertJsonPath('data.0.id', $attempt->id)
            ->assertJsonPath('data.0.user_email', 'elena@example.com')
            ->assertJsonPath('data.0.admin_result_url', "/admin/attempts/{$attempt->id}")
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('filters.categories.0.name_en', 'Macedonian Language')
            ->assertJsonPath('filters.quizzes.0.title_en', 'Basic Macedonian Greetings');

        $this->actingAs($admin)
            ->getJson("/api/admin/attempts/{$attempt->id}")
            ->assertOk()
            ->assertJsonPath('data.learner.name', 'Elena Learner')
            ->assertJsonPath('data.quiz.title_en', 'Basic Macedonian Greetings')
            ->assertJsonPath('data.category.name_en', 'Macedonian Language')
            ->assertJsonPath('data.attempt.score', 0)
            ->assertJsonPath('data.learner_quiz_stats.total_attempts', 1)
            ->assertJsonPath('data.answers.0.question.question_en', 'Snapshot question?')
            ->assertJsonPath('data.answers.0.question.question_mk', 'Снимка прашање?')
            ->assertJsonPath('data.answers.0.selected_answer.answer_en', 'Snapshot learner answer')
            ->assertJsonPath('data.answers.0.correct_answer.answer_en', 'Snapshot correct answer');

        $this->actingAs($learner)
            ->getJson("/api/quiz-attempts/{$attempt->id}")
            ->assertOk()
            ->assertJsonPath('data.answers.0.question.question_en', 'Snapshot question?')
            ->assertJsonPath('data.answers.0.selected_answer.answer_en', 'Snapshot learner answer')
            ->assertJsonPath('data.answers.0.correct_answer.answer_en', 'Snapshot correct answer');

        $this->assertSame('Edited correct answer', $correctAnswer->fresh()->answer_en);
    }

    public function test_non_admin_users_cannot_access_admin_attempt_pages_or_api(): void
    {
        $learner = User::factory()->create();
        $attempt = QuizAttempt::factory()->create();

        $this->actingAs($learner)
            ->get('/admin/attempts')
            ->assertForbidden();

        $this->actingAs($learner)
            ->getJson('/api/admin/attempts')
            ->assertForbidden();

        $this->actingAs($learner)
            ->getJson("/api/admin/attempts/{$attempt->id}")
            ->assertForbidden();
    }
}
