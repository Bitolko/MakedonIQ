<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolkloreSoundQuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_folklore_lesson_loads_lyrics_translation_and_context(): void
    {
        $this->seed();

        $this->getJson('/api/lessons/folklore-song-jovano-jovanke')
            ->assertOk()
            ->assertJsonPath('data.title_en', 'Jovano, Jovanke')
            ->assertJsonPath('data.related_quizzes.0.slug', 'sound-quiz-jovano-jovanke')
            ->assertSee('Read & Remember', false)
            ->assertSee('Macedonian lyric excerpt', false)
            ->assertSee('English translation', false)
            ->assertSee('The Vardar appears', false);
    }

    public function test_public_sound_questions_hide_correct_answer_data(): void
    {
        $this->seed();

        $response = $this->getJson('/api/quizzes/sound-quiz-jovano-jovanke/questions')
            ->assertOk()
            ->assertJsonPath('data.quiz.has_sound_questions', true)
            ->assertJsonPath('data.questions.0.question_type', 'sound_choice')
            ->assertJsonPath('data.questions.0.metadata.audio_path', '/audio/lessons/song_001.mp3')
            ->assertJsonCount(4, 'data.questions.0.answers');

        $response->assertDontSee('"is_correct"', false);
        $response->assertDontSee('"correct_answer"', false);
    }

    public function test_guest_can_preview_only_one_demo_sound_quiz_per_session(): void
    {
        $this->seed();

        Quiz::where('slug', 'sound-quiz-biljana-platno-belese')->update(['is_demo' => true]);

        $this->getJson('/api/quizzes/sound-quiz-jovano-jovanke/questions')
            ->assertOk();

        $this->getJson('/api/quizzes/sound-quiz-biljana-platno-belese/questions')
            ->assertForbidden()
            ->assertJsonPath('message', 'Create a free account to unlock more sound quizzes.');
    }

    public function test_guest_cannot_access_non_demo_sound_quizzes(): void
    {
        $this->seed();

        $this->getJson('/api/quizzes/sound-quiz-biljana-platno-belese/questions')
            ->assertForbidden()
            ->assertJsonPath('message', 'Create a free account to unlock this quiz.');
    }

    public function test_logged_in_user_can_access_seeded_sound_quizzes(): void
    {
        $this->seed();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/quizzes/sound-quiz-biljana-platno-belese/questions')
            ->assertOk()
            ->assertJsonPath('data.questions.0.metadata.audio_path', '/audio/lessons/song_002.mp3');
    }

    public function test_admin_can_create_sound_choice_question_with_audio_path(): void
    {
        $this->seed();

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $quiz = Quiz::where('slug', 'macedonian-food-and-music-basics')->firstOrFail();
        $lesson = Lesson::where('slug', 'macedonian-food-and-music-basics')->firstOrFail();
        $quiz->update(['lesson_id' => $lesson->id]);

        $payload = [
            'question_type' => 'sound_choice',
            'metadata' => [
                'audio_path' => '/audio/lessons/song_099.mp3',
            ],
            'question_en' => 'Listen and choose the song title.',
            'question_mk' => 'Слушни и избери го насловот.',
            'explanation_en' => 'The audio path is stored as sound metadata.',
            'explanation_mk' => 'Аудио патеката е зачувана како звучна метаподаточна вредност.',
            'sort_order' => 99,
            'points' => null,
            'is_published' => true,
            'answers' => [
                ['answer_en' => 'Jovano, Jovanke', 'answer_mk' => 'Јовано, Јованке', 'is_correct' => true],
                ['answer_en' => 'Biljana Platno Beleshe', 'answer_mk' => 'Билјана платно белеше', 'is_correct' => false],
                ['answer_en' => 'Oj Devojche, Devojche', 'answer_mk' => 'Ој девојче, девојче', 'is_correct' => false],
                ['answer_en' => 'Ajde Slushaj, Kalesh Bre Angjo', 'answer_mk' => 'Ајде слушај, калеш бре Анѓо', 'is_correct' => false],
            ],
        ];

        $this->actingAs($admin)
            ->postJson("/api/admin/quizzes/{$quiz->id}/questions", $payload)
            ->assertCreated()
            ->assertJsonPath('data.question_type', 'sound_choice')
            ->assertJsonPath('data.metadata.audio_path', '/audio/lessons/song_099.mp3')
            ->assertJsonPath('data.correct_answer_en', 'Jovano, Jovanke')
            ->assertJsonPath('data.answers.0.is_correct', true);
    }
}
