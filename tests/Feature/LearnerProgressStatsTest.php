<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnerProgressStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_attempts_count_only_best_points_in_learner_progress_surfaces(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'name_en' => 'Macedonian Language',
            'slug' => 'macedonian-language',
            'sort_order' => 1,
        ]);

        $quiz = $this->createQuiz($category, 'Basic Macedonian Greetings', 'basic-macedonian-greetings', 1);

        for ($index = 2; $index <= 4; $index++) {
            $this->createQuiz($category, "Language Practice {$index}", "language-practice-{$index}", $index);
        }

        foreach ([[10, 20], [20, 40], [20, 40], [30, 60], [40, 80], [50, 100], [30, 60]] as [$score, $percentage]) {
            $this->createAttempt($user, $quiz, $score, $percentage);
        }

        $dashboard = $this->actingAs($user)
            ->getJson('/api/me/dashboard')
            ->assertOk()
            ->json('data');

        $dashboardCategory = collect($dashboard['category_progress'])->firstWhere('slug', 'macedonian-language');

        $this->assertSame(50, $dashboard['summary']['total_points']);
        $this->assertSame(1, $dashboard['summary']['completed_quizzes_count']);
        $this->assertSame(7, $dashboard['summary']['total_attempts_count']);
        $this->assertEquals(100.0, $dashboard['summary']['best_percentage']);
        $this->assertCount(5, $dashboard['recent_attempts']);
        $this->assertSame(50, $dashboardCategory['total_points']);
        $this->assertSame(1, $dashboardCategory['completed_quizzes']);
        $this->assertEquals(25.0, $dashboardCategory['progress_percentage']);
        $this->assertEquals(100.0, $dashboardCategory['best_percentage']);

        $progress = $this->actingAs($user)
            ->getJson('/api/me/progress')
            ->assertOk()
            ->json('data');

        $progressCategory = collect($progress['category_progress'])->firstWhere('slug', 'macedonian-language');

        $this->assertSame(50, $progress['overall']['total_points']);
        $this->assertSame(7, $progress['overall']['total_attempts']);
        $this->assertSame(1, $progress['overall']['completed_quizzes_count']);
        $this->assertCount(7, $progress['quiz_history']);
        $this->assertSame(50, $progressCategory['total_points']);
        $this->assertSame(1, $progressCategory['completed_quizzes']);

        $categoryPage = $this->actingAs($user)
            ->getJson('/api/categories/macedonian-language')
            ->assertOk()
            ->json('data');

        $quizPayload = collect($categoryPage['quizzes'])->firstWhere('slug', 'basic-macedonian-greetings');

        $this->assertSame(50, $categoryPage['user_progress']['total_points']);
        $this->assertSame(7, $categoryPage['user_progress']['total_attempts']);
        $this->assertSame(1, $categoryPage['user_progress']['completed_quizzes']);
        $this->assertSame(4, $categoryPage['user_progress']['total_quizzes']);
        $this->assertEquals(25.0, $categoryPage['user_progress']['progress_percentage']);
        $this->assertSame(7, $quizPayload['user_progress']['attempts_count']);
        $this->assertEquals(100.0, $quizPayload['user_progress']['best_percentage']);
    }

    public function test_total_points_add_best_points_from_each_completed_quiz(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'macedonian-language',
            'sort_order' => 1,
        ]);

        $quizA = $this->createQuiz($category, 'Quiz A', 'quiz-a', 1);
        $quizB = $this->createQuiz($category, 'Quiz B', 'quiz-b', 2);

        $this->createAttempt($user, $quizA, 10, 20);
        $this->createAttempt($user, $quizA, 50, 100);
        $this->createAttempt($user, $quizB, 40, 80);
        $this->createAttempt($user, $quizB, 20, 40);

        $dashboard = $this->actingAs($user)
            ->getJson('/api/me/dashboard')
            ->assertOk()
            ->json('data');

        $dashboardCategory = collect($dashboard['category_progress'])->firstWhere('slug', 'macedonian-language');

        $this->assertSame(90, $dashboard['summary']['total_points']);
        $this->assertSame(2, $dashboard['summary']['completed_quizzes_count']);
        $this->assertSame(4, $dashboard['summary']['total_attempts_count']);
        $this->assertSame(90, $dashboardCategory['total_points']);
        $this->assertSame(2, $dashboardCategory['completed_quizzes']);
    }

    private function createQuiz(Category $category, string $title, string $slug, int $sortOrder): Quiz
    {
        return Quiz::factory()->create([
            'category_id' => $category->id,
            'title_en' => $title,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'is_published' => true,
        ]);
    }

    private function createAttempt(User $user, Quiz $quiz, int $score, float $percentage): QuizAttempt
    {
        return QuizAttempt::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => 5,
            'correct_answers' => (int) round($percentage / 20),
            'percentage' => $percentage,
            'passed' => $percentage >= 70,
            'started_at' => now()->subMinutes(10),
            'completed_at' => now(),
        ]);
    }
}
