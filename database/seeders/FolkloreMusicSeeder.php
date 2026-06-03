<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class FolkloreMusicSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'food-and-music'],
            [
                'name_en' => 'Food and Music',
                'name_mk' => 'Храна и музика',
                'description_en' => 'Explore dishes, songs, instruments, and celebrations.',
                'description_mk' => 'Истражи јадења, песни, инструменти и прослави.',
                'icon' => 'FM',
                'sort_order' => 6,
                'is_published' => true,
            ],
        );

        $songs = $this->songs();

        foreach ($songs as $index => $song) {
            $lesson = Lesson::updateOrCreate(
                ['slug' => $song['lesson_slug']],
                [
                    'category_id' => $category->id,
                    'title_en' => $song['title_en'],
                    'title_mk' => $song['title_mk'],
                    'summary_en' => $song['summary_en'],
                    'summary_mk' => $song['summary_mk'],
                    'content_en' => $this->lessonContentEn($song),
                    'content_mk' => $this->lessonContentMk($song),
                    'difficulty' => 'beginner',
                    'estimated_minutes' => 7,
                    'sort_order' => 90 + $index,
                    'is_published' => true,
                    'is_demo' => $index === 0,
                ],
            );

            $quiz = $category->quizzes()->updateOrCreate(
                ['slug' => $song['quiz_slug']],
                [
                    'lesson_id' => $lesson->id,
                    'title_en' => "{$song['title_en']} Sound Quiz",
                    'title_mk' => "Звучен квиз: {$song['title_mk']}",
                    'description_en' => 'Listen to the MP3 clue and choose the matching Macedonian folklore song title.',
                    'description_mk' => 'Слушни го MP3 примерот и избери го точниот наслов на македонската народна песна.',
                    'difficulty' => 'beginner',
                    'estimated_minutes' => 3,
                    'points_per_question' => 10,
                    'is_published' => true,
                    'is_demo' => $index === 0,
                    'sort_order' => 90 + $index,
                ],
            );

            $this->syncSoundQuestion($quiz, $song, $songs);
        }
    }

    private function syncSoundQuestion(Quiz $quiz, array $song, array $songs): void
    {
        $question = $quiz->questions()
            ->where('question_type', 'sound_choice')
            ->where('question_en', 'Listen to the audio. Which folklore song is this?')
            ->first();

        $attributes = [
            'question_type' => 'sound_choice',
            'translation_direction' => null,
            'metadata' => [
                'audio_path' => $song['audio_path'],
            ],
            'question_en' => 'Listen to the audio. Which folklore song is this?',
            'question_mk' => 'Слушни го звукот. Која народна песна е ова?',
            'explanation_en' => "The sound clue is from {$song['title_en']}.",
            'explanation_mk' => "Звучниот пример е од „{$song['title_mk']}“.",
            'sort_order' => 1,
            'points' => null,
            'is_published' => true,
        ];

        if ($question) {
            $question->update($attributes);
        } else {
            $question = $quiz->questions()->create($attributes);
        }

        if ($question->attemptAnswers()->exists()) {
            return;
        }

        $question->answers()->delete();

        foreach ($this->answerOptions($song, $songs) as $index => $answer) {
            $question->answers()->create([
                'answer_en' => $answer['title_en'],
                'answer_mk' => $answer['title_mk'],
                'is_correct' => $answer['lesson_slug'] === $song['lesson_slug'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function answerOptions(array $song, array $songs): array
    {
        $offset = abs(crc32($song['lesson_slug'])) % count($songs);

        return array_merge(array_slice($songs, $offset), array_slice($songs, 0, $offset));
    }

    private function lessonContentEn(array $song): string
    {
        return <<<TEXT
Read & Remember:
Macedonian lyric excerpt:
{$song['lyrics_mk']}

English translation:
{$song['lyrics_en']}

Key Points:
- Remember the title in both Macedonian and English: {$song['title_mk']} / {$song['title_en']}.
- Notice the repeating words and images; repetition helps folk songs stay memorable.
- Listen for the melody first, then connect the sound to the title.

Explanation/Context:
{$song['context_en']}

Interesting facts:
- {$song['fact_en']}
- The seeded sound quiz uses the neutral audio path {$song['audio_path']}.
- Different families and ensembles may sing slightly different versions.

Examples:
- Say the title aloud before listening to the audio clue.
- Point to one word in the Macedonian excerpt and match it with the English translation.
- After the quiz, return to this lesson and read the lyric excerpt again.

Practice tasks:
- Play the related sound quiz and choose the song title by ear.
- Read one Macedonian line slowly, then read its English meaning.
- Ask a family member whether they know another version of this song.
TEXT;
    }

    private function lessonContentMk(array $song): string
    {
        return <<<TEXT
Прочитај и запомни:
Македонски стихови:
{$song['lyrics_mk']}

Англиски превод:
{$song['lyrics_en']}

Клучни точки:
- Запомни го насловот на македонски и англиски: {$song['title_mk']} / {$song['title_en']}.
- Забележи ги повторувањата и сликите во стиховите.
- Прво слушни ја мелодијата, потоа поврзи го звукот со насловот.

Објаснување/контекст:
{$song['context_mk']}

Интересни факти:
- {$song['fact_mk']}
- Звучниот квиз ја користи неутралната патека {$song['audio_path']}.
- Различни семејства и ансамбли можат да пеат малку различни верзии.

Примери:
- Кажи го насловот на глас пред да го слушнеш звукот.
- Избери еден збор од македонските стихови и поврзи го со англискиот превод.
- По квизот, врати се на лекцијата и прочитај го извадокот повторно.

Вежбање:
- Отвори го поврзаниот звучен квиз и избери го насловот според слух.
- Прочитај еден македонски стих полека, па неговото англиско значење.
- Прашај член од семејството дали знае друга верзија на песната.
TEXT;
    }

    private function songs(): array
    {
        return [
            [
                'lesson_slug' => 'folklore-song-jovano-jovanke',
                'quiz_slug' => 'sound-quiz-jovano-jovanke',
                'title_en' => 'Jovano, Jovanke',
                'title_mk' => 'Јовано, Јованке',
                'summary_en' => 'A beloved Macedonian folk song often connected with the Vardar river and tender courtship imagery.',
                'summary_mk' => 'Позната македонска народна песна поврзана со Вардар и нежни љубовни слики.',
                'lyrics_mk' => "Јовано, Јованке,\nкрај Вардарот седеше,\nбело платно белеше,\nна високо гледаше.",
                'lyrics_en' => "Jovana, dear Jovana,\nyou sat beside the Vardar,\nyou washed white cloth,\nand looked toward the heights.",
                'context_en' => 'This song is often taught as a gentle introduction to Macedonian folk melody because the title is easy to recognise and the river image gives learners a place to remember. The Vardar appears in many cultural memories as a symbol of movement, home, and landscape.',
                'context_mk' => 'Оваа песна често се учи како нежен вовед во македонска народна мелодија, бидејќи насловот лесно се препознава, а сликата со Вардар помага да се запомни местото. Вардар често се јавува како симбол на движење, дом и пејзаж.',
                'fact_en' => 'The name Jovana appears in vocative form, which makes the song feel like a direct address.',
                'fact_mk' => 'Името Јована е употребено во обраќање, па песната звучи како директен повик.',
                'audio_path' => '/audio/lessons/song_001.mp3',
            ],
            [
                'lesson_slug' => 'folklore-song-biljana-platno-belese',
                'quiz_slug' => 'sound-quiz-biljana-platno-belese',
                'title_en' => 'Biljana Platno Beleshe',
                'title_mk' => 'Билјана платно белеше',
                'summary_en' => 'An Ohrid-associated folk song with water, cloth, and everyday work turned into poetry.',
                'summary_mk' => 'Народна песна поврзана со Охрид, каде вода, платно и секојдневна работа стануваат поезија.',
                'lyrics_mk' => "Билјана платно белеше,\nна охридските извори,\nтаа си тивко пееше,\nкрај вода бистра студена.",
                'lyrics_en' => "Biljana was whitening cloth,\nby the springs of Ohrid,\nshe was singing softly,\nbeside clear, cool water.",
                'context_en' => 'The song places a familiar task beside the springs of Ohrid, turning daily work into a memorable musical scene. It is useful for learners because it connects a person, an action, and a famous place.',
                'context_mk' => 'Песната ја поставува секојдневната работа покрај охридските извори и ја претвора во запаметлива музичка слика. Корисна е за учење бидејќи поврзува личност, дејство и познато место.',
                'fact_en' => 'Ohrid songs often carry strong place imagery, which helps learners connect music with geography.',
                'fact_mk' => 'Охридските песни често носат силни слики за место, што помага музиката да се поврзе со географија.',
                'audio_path' => '/audio/lessons/song_002.mp3',
            ],
            [
                'lesson_slug' => 'folklore-song-ajde-slusaj-kalesh-bre-angjo',
                'quiz_slug' => 'sound-quiz-ajde-slusaj-kalesh-bre-angjo',
                'title_en' => 'Ajde Slushaj, Kalesh Bre Angjo',
                'title_mk' => 'Ајде слушај, калеш бре Анѓо',
                'summary_en' => 'A dramatic folk song title that helps learners hear address, rhythm, and character in song.',
                'summary_mk' => 'Драматичен народен наслов што им помага на учениците да слушнат обраќање, ритам и лик во песна.',
                'lyrics_mk' => "Ајде слушај, калеш бре Анѓо,\nшто ти збори младо момче,\nсо тивок глас те повикува,\nда го слушнеш неговиот збор.",
                'lyrics_en' => "Come, listen, dark-eyed Angja,\nwhat the young lad says to you,\nwith a quiet voice he calls,\nfor you to hear his words.",
                'context_en' => 'This lesson focuses on listening for address and character. Folk songs often sound like conversations, and this title gives learners a clear name and repeated call to recognise by ear.',
                'context_mk' => 'Оваа лекција се фокусира на слушање обраќање и лик. Народните песни често звучат како разговори, а овој наслов дава јасно име и повик што лесно се препознава по слух.',
                'fact_en' => 'The word kalеsh is often used in songs as a poetic description for dark-haired or dark-eyed beauty.',
                'fact_mk' => 'Зборот калеш често се користи во песни како поетски опис за темнокоса или темноока убавина.',
                'audio_path' => '/audio/lessons/song_003.mp3',
            ],
            [
                'lesson_slug' => 'folklore-song-oj-devojche-devojche',
                'quiz_slug' => 'sound-quiz-oj-devojche-devojche',
                'title_en' => 'Oj Devojche, Devojche',
                'title_mk' => 'Ој девојче, девојче',
                'summary_en' => 'A simple call-and-response style folk song title for practising repeated words and melody recognition.',
                'summary_mk' => 'Едноставен народен наслов со повторување, корисен за вежбање зборови и препознавање мелодија.',
                'lyrics_mk' => "Ој девојче, девојче,\nубаво мало момиче,\nкажи ми една песна,\nсо глас како утринска роса.",
                'lyrics_en' => "Oh girl, dear girl,\nbeautiful young maiden,\nsing me one song,\nwith a voice like morning dew.",
                'context_en' => 'Repeated address makes this song approachable for beginners. Learners can practise the word devojche and notice how folk lyrics often use affectionate repetition to carry rhythm.',
                'context_mk' => 'Повтореното обраќање ја прави песната пристапна за почетници. Учениците можат да го вежбаат зборот девојче и да забележат како народните стихови користат нежно повторување за ритам.',
                'fact_en' => 'Short repeated titles are helpful in sound quizzes because learners can recognise the rhythm before they know every word.',
                'fact_mk' => 'Кратките повторени наслови се корисни во звучни квизови бидејќи ритамот се препознава пред да се знае секој збор.',
                'audio_path' => '/audio/lessons/song_004.mp3',
            ],
        ];
    }
}
