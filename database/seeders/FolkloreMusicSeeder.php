<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class FolkloreMusicSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'folklore-songs'],
            [
                'name_en' => 'Folklore Songs',
                'name_mk' => 'Народни песни',
                'description_en' => 'Learn traditional Macedonian songs through lyrics, meaning, vocabulary, and cultural context.',
                'description_mk' => 'Учи македонски народни песни преку текст, значење, зборови и културен контекст.',
                'icon' => 'FS',
                'sort_order' => 7,
                'is_published' => true,
            ],
        );

        $lessonsBySlug = [];

        foreach ($this->songs() as $index => $song) {
            $lessonsBySlug[$song['slug']] = Lesson::updateOrCreate(
                ['slug' => $song['slug']],
                [
                    'category_id' => $category->id,
                    'title_en' => $song['title_en'],
                    'title_mk' => $song['title_mk'],
                    'summary_en' => $song['summary_en'],
                    'summary_mk' => $song['summary_mk'],
                    'content_en' => $this->lessonContentEn($song),
                    'content_mk' => $this->lessonContentMk($song),
                    'difficulty' => $song['difficulty'],
                    'estimated_minutes' => $song['estimated_minutes'],
                    'sort_order' => 90 + $index,
                    'is_published' => true,
                    'is_demo' => $index === 0,
                ],
            );
        }

        $quiz = $category->quizzes()->updateOrCreate(
            ['slug' => 'guess-the-macedonian-folk-song'],
            [
                'lesson_id' => $lessonsBySlug['folklore-song-makedonsko-devojche']->id ?? null,
                'title_en' => 'Guess the Macedonian Folk Song',
                'title_mk' => 'Погоди ја македонската народна песна',
                'description_en' => 'Listen to the audio clue and choose the correct traditional song. Original audio clips will be added later.',
                'description_mk' => 'Слушни го аудио знакот и избери ја точната народна песна. Оригинални аудио исечоци ќе бидат додадени подоцна.',
                'difficulty' => 'beginner',
                'estimated_minutes' => 5,
                'points_per_question' => 10,
                'sort_order' => 90,
                'is_published' => true,
                'is_demo' => false,
            ],
        );

        $this->syncSoundQuizQuestions($quiz);
        $this->normalizeLegacySoundPlaceholders();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function songs(): array
    {
        return [
            [
                'slug' => 'folklore-song-makedonsko-devojche',
                'title_en' => 'Makedonsko Devojche',
                'title_mk' => 'Македонско девојче',
                'summary_en' => 'A well-known song title used here as a careful beginner lesson about identity, beauty, and cultural memory.',
                'summary_mk' => 'Познат наслов на песна што овде се користи како внимателна почетна лекција за идентитет, убавина и културно сеќавање.',
                'safe_phrase_mk' => 'Македонско девојче',
                'safe_phrase_en' => 'Macedonian girl',
                'meaning_en' => 'The title points to a Macedonian girl and often invites learners to talk about identity, affection, and homeland imagery.',
                'meaning_mk' => 'Насловот упатува на македонско девојче и отвора разговор за идентитет, нежност и слики од татковината.',
                'context_en' => 'Because modern recordings and arrangements may have separate rights, this lesson uses only a short title phrase and original educational explanation. It is still useful for learning because the title contains clear identity vocabulary.',
                'context_mk' => 'Бидејќи современите снимки и аранжмани можат да имаат посебни права, оваа лекција користи само кратка фраза од насловот и оригинално образовно објаснување. Сепак е корисна за учење затоа што насловот носи јасни зборови за идентитет.',
                'fact_en' => 'The phrase is easy to recognise by ear, which makes it a good future sound-quiz prompt after original MakedonIQ audio is recorded.',
                'fact_mk' => 'Фразата лесно се препознава по слух, па е добар иден звучен квиз откако ќе се сними оригинално аудио од MakedonIQ.',
                'vocabulary' => [
                    ['term' => 'македонско', 'detail_en' => 'Macedonian', 'detail_mk' => 'македонско'],
                    ['term' => 'девојче', 'detail_en' => 'girl', 'detail_mk' => 'девојче'],
                    ['term' => 'песна', 'detail_en' => 'song', 'detail_mk' => 'песна'],
                    ['term' => 'убавина', 'detail_en' => 'beauty', 'detail_mk' => 'убавина'],
                ],
                'difficulty' => 'beginner',
                'estimated_minutes' => 8,
            ],
            [
                'slug' => 'folklore-song-jovano-jovanke',
                'title_en' => 'Jovano, Jovanke',
                'title_mk' => 'Јовано, Јованке',
                'summary_en' => 'A traditional song title that helps learners hear direct address, names, and river imagery.',
                'summary_mk' => 'Народен наслов што им помага на учениците да слушнат обраќање, имиња и слика на река.',
                'safe_phrase_mk' => 'Јовано, Јованке',
                'safe_phrase_en' => 'Jovana, dear Jovana',
                'meaning_en' => 'The song is often remembered as a tender address to Jovana, with place imagery that connects melody and landscape.',
                'meaning_mk' => 'Песната често се памети како нежно обраќање кон Јована, со слики од место што ја поврзуваат мелодијата со пејзажот.',
                'context_en' => 'Folk songs often feel like conversations. This lesson focuses on how a repeated name can become a memory anchor for learners.',
                'context_mk' => 'Народните песни често звучат како разговори. Оваа лекција се фокусира на тоа како повторено име може да стане потпора за паметење.',
                'fact_en' => 'Names in songs can preserve older forms of address and make vocabulary feel personal.',
                'fact_mk' => 'Имињата во песните можат да зачуваат постари начини на обраќање и да направат зборовите да звучат лично.',
                'vocabulary' => [
                    ['term' => 'име', 'detail_en' => 'name', 'detail_mk' => 'име'],
                    ['term' => 'река', 'detail_en' => 'river', 'detail_mk' => 'река'],
                    ['term' => 'обраќање', 'detail_en' => 'addressing someone', 'detail_mk' => 'обраќање'],
                    ['term' => 'нежно', 'detail_en' => 'tenderly', 'detail_mk' => 'нежно'],
                ],
                'difficulty' => 'beginner',
                'estimated_minutes' => 9,
            ],
            [
                'slug' => 'folklore-song-nazad-nazad-kalino-mome',
                'title_en' => 'Nazad, Nazad Kalino Mome',
                'title_mk' => 'Назад, назад Калино моме',
                'summary_en' => 'A memorable folk title for practising repeated words, movement, and character names.',
                'summary_mk' => 'Запаметлив народен наслов за вежбање повторени зборови, движење и имиња на ликови.',
                'safe_phrase_mk' => 'Назад, назад Калино моме',
                'safe_phrase_en' => 'Back, back, Kalina girl',
                'meaning_en' => 'The repeated word suggests movement and urgency, while the name Kalina gives the line a clear character.',
                'meaning_mk' => 'Повторениот збор сугерира движење и итност, а името Калина и дава јасен лик на фразата.',
                'context_en' => 'Repetition is one reason folk songs are powerful learning tools. Learners can hear the repeated word before they understand every detail.',
                'context_mk' => 'Повторувањето е една причина зошто народните песни се силни алатки за учење. Учениците можат да го слушнат повторениот збор пред да го разберат секој детал.',
                'fact_en' => 'Repeated commands or calls are useful for listening practice because they create a predictable rhythm.',
                'fact_mk' => 'Повторените повици или заповеди се корисни за слушање затоа што создаваат предвидлив ритам.',
                'vocabulary' => [
                    ['term' => 'назад', 'detail_en' => 'back', 'detail_mk' => 'назад'],
                    ['term' => 'моме', 'detail_en' => 'maiden, girl', 'detail_mk' => 'девојка'],
                    ['term' => 'движење', 'detail_en' => 'movement', 'detail_mk' => 'движење'],
                    ['term' => 'ритам', 'detail_en' => 'rhythm', 'detail_mk' => 'ритам'],
                ],
                'difficulty' => 'beginner',
                'estimated_minutes' => 9,
            ],
            [
                'slug' => 'folklore-song-ja-izlezi-gjurgo',
                'title_en' => 'Ja Izlezi Gjurgo',
                'title_mk' => 'Ја излези Ѓурѓо',
                'summary_en' => 'A song title that introduces learners to invitation, action words, and personal address.',
                'summary_mk' => 'Наслов што ги воведува учениците во покана, глаголи и лично обраќање.',
                'safe_phrase_mk' => 'Ја излези Ѓурѓо',
                'safe_phrase_en' => 'Come out, Gjurgo',
                'meaning_en' => 'The title sounds like a call to someone named Gjurgo, so learners can practise an action phrase and a name together.',
                'meaning_mk' => 'Насловот звучи како повик кон некој по име Ѓурѓо, па учениците можат заедно да вежбаат глаголска фраза и име.',
                'context_en' => 'Many folk songs begin with a call, invitation, or short dramatic moment. That makes them useful for noticing verbs and social language.',
                'context_mk' => 'Многу народни песни почнуваат со повик, покана или краток драматичен момент. Затоа се корисни за забележување глаголи и општествен јазик.',
                'fact_en' => 'Action words in song titles help learners connect grammar with gesture and sound.',
                'fact_mk' => 'Глаголите во насловите на песните им помагаат на учениците да ја поврзат граматиката со гест и звук.',
                'vocabulary' => [
                    ['term' => 'излези', 'detail_en' => 'come out', 'detail_mk' => 'излези'],
                    ['term' => 'повик', 'detail_en' => 'call', 'detail_mk' => 'повик'],
                    ['term' => 'име', 'detail_en' => 'name', 'detail_mk' => 'име'],
                    ['term' => 'покана', 'detail_en' => 'invitation', 'detail_mk' => 'покана'],
                ],
                'difficulty' => 'beginner',
                'estimated_minutes' => 8,
            ],
            [
                'slug' => 'folklore-song-ushti-ushti-baba',
                'title_en' => 'Ushti, Ushti Baba',
                'title_mk' => 'Ушти, ушти баба',
                'summary_en' => 'A playful traditional title for hearing repetition, family vocabulary, and rhythm.',
                'summary_mk' => 'Разигран народен наслов за слушање повторување, семејни зборови и ритам.',
                'safe_phrase_mk' => 'Ушти, ушти баба',
                'safe_phrase_en' => 'Again, again, grandmother',
                'meaning_en' => 'The repeated phrase gives learners a rhythmic hook, while baba introduces a familiar family word.',
                'meaning_mk' => 'Повторената фраза им дава на учениците ритмичка потпора, а зборот баба внесува познат семеен збор.',
                'context_en' => 'Playful songs can make family vocabulary easy to remember. Learners can clap the rhythm and say the key word aloud.',
                'context_mk' => 'Разиграните песни можат лесно да ги врежат семејните зборови во паметењето. Учениците можат да плескаат ритам и да го кажат клучниот збор на глас.',
                'fact_en' => 'Family words are often among the first heritage words children recognise.',
                'fact_mk' => 'Семејните зборови често се меѓу првите наследни зборови што децата ги препознаваат.',
                'vocabulary' => [
                    ['term' => 'баба', 'detail_en' => 'grandmother', 'detail_mk' => 'баба'],
                    ['term' => 'повторно', 'detail_en' => 'again', 'detail_mk' => 'повторно'],
                    ['term' => 'семејство', 'detail_en' => 'family', 'detail_mk' => 'семејство'],
                    ['term' => 'глас', 'detail_en' => 'voice', 'detail_mk' => 'глас'],
                ],
                'difficulty' => 'beginner',
                'estimated_minutes' => 8,
            ],
        ];
    }

    private function lessonContentEn(array $song): string
    {
        $vocabulary = collect($song['vocabulary'])
            ->map(fn (array $item): string => "- {$item['term']} = {$item['detail_en']}")
            ->implode("\n");

        return <<<TEXT
Introduction:
{$song['title_en']} is presented as a folklore song lesson for language, memory, and cultural context. This lesson uses a short title phrase and original explanatory text so learners can study safely while full lyric permissions are confirmed.

Read & Remember:
Macedonian lyric focus:
Title phrase: {$song['safe_phrase_mk']}

English meaning:
{$song['safe_phrase_en']}. {$song['meaning_en']}

Key Points:
- Learn the title in Macedonian and English: {$song['title_mk']} / {$song['title_en']}.
- Notice repetition, names, and short phrases before trying to understand a full song.
- Traditional songs help connect language with rhythm, memory, and identity.

Key vocabulary:
{$vocabulary}

Explanation/Context:
{$song['context_en']}

Interesting facts:
- {$song['fact_en']}
- Full lyric text is intentionally not seeded until public-domain or permission status is confirmed.
- No commercial recordings or external audio are used in this lesson.

Examples:
- Read the title phrase aloud slowly.
- Find one vocabulary word in the phrase or context.
- Explain the song idea in your own words without needing a recording.

Practice tasks:
- Read the Macedonian title phrase three times.
- Pick three new words and say their English meanings.
- Later, use the sound quiz when original MakedonIQ recordings are added.

Source/public reference note:
Traditional Macedonian folk song title/reference. Lyrics and context are written or adapted for MakedonIQ learning purposes. Add source attribution where required.

Source notes:
Use only public-domain/traditional lyrics or permitted sources. Do not use copyrighted recordings. Future sound quiz clips should be original MakedonIQ recordings or properly licensed audio.
TEXT;
    }

    private function lessonContentMk(array $song): string
    {
        $vocabulary = collect($song['vocabulary'])
            ->map(fn (array $item): string => "- {$item['term']} = {$item['detail_mk']}")
            ->implode("\n");

        return <<<TEXT
Вовед:
{$song['title_mk']} е претставена како лекција за народна песна, јазик, сеќавање и културен контекст. Лекцијата користи кратка фраза од насловот и оригинално образовно објаснување за учениците да учат безбедно додека се потврдува статусот на целосниот текст.

Прочитај и запомни:
Македонски фокус:
Фраза од насловот: {$song['safe_phrase_mk']}

Англиско значење:
{$song['safe_phrase_en']}. {$song['meaning_mk']}

Клучни точки:
- Научи го насловот на македонски и англиски: {$song['title_mk']} / {$song['title_en']}.
- Забележи повторување, имиња и кратки фрази пред да се обидеш да разбереш цела песна.
- Народните песни го поврзуваат јазикот со ритам, сеќавање и идентитет.

Клучни зборови:
{$vocabulary}

Објаснување/контекст:
{$song['context_mk']}

Интересни факти:
- {$song['fact_mk']}
- Целосниот текст намерно не е внесен додека не се потврди јавен домен или дозвола.
- Во оваа лекција нема комерцијални снимки или надворешно аудио.

Примери:
- Прочитај ја фразата од насловот полека на глас.
- Најди еден збор од речникот во фразата или контекстот.
- Објасни ја идејата на песната со свои зборови без да ти треба снимка.

Вежбање:
- Прочитај ја македонската фраза три пати.
- Избери три нови зборови и кажи го нивното значење.
- Подоцна, користи го звучниот квиз кога ќе се додадат оригинални MakedonIQ снимки.

Јавна белешка за извор:
Традиционален македонски народен наслов/референца. Текстот и контекстот се напишани или адаптирани за учење во MakedonIQ. Додај атрибуција кога е потребно.

Белешки за извор:
Користи само народни/јавнодоменски текстови или извори со дозвола. Не користи заштитени снимки. Идните звучни исечоци треба да бидат оригинални MakedonIQ снимки или правилно лиценцирано аудио.
TEXT;
    }

    private function syncSoundQuizQuestions(Quiz $quiz): void
    {
        foreach ($this->soundQuestions() as $index => $questionData) {
            $question = $quiz->questions()
                ->where('question_type', 'sound_choice')
                ->where('sort_order', $index + 1)
                ->first();

            $attributes = [
                'question_type' => 'sound_choice',
                'translation_direction' => null,
                'metadata' => [
                    'audio_path' => null,
                    'audio_alt_en' => 'Folklore audio clue',
                    'audio_alt_mk' => 'Аудио загатка од народна песна',
                    'audio_type' => 'folklore',
                    'audio_credit' => 'Placeholder. Original MakedonIQ recording to be added later.',
                ],
                'question_en' => 'Which song is this?',
                'question_mk' => 'Која песна е ова?',
                'explanation_en' => "The placeholder clue is reserved for {$questionData['correct_en']}. Original audio will be added later.",
                'explanation_mk' => "Овој привремен звучен знак е резервиран за {$questionData['correct_mk']}. Оригинално аудио ќе биде додадено подоцна.",
                'sort_order' => $index + 1,
                'points' => null,
                'is_published' => true,
            ];

            if ($question) {
                $question->update($attributes);
            } else {
                $question = $quiz->questions()->create($attributes);
            }

            if ($question->attemptAnswers()->exists()) {
                continue;
            }

            $question->answers()->delete();

            foreach ($questionData['options'] as $optionIndex => $option) {
                $question->answers()->create([
                    'answer_en' => $option['en'],
                    'answer_mk' => $option['mk'],
                    'is_correct' => $option['en'] === $questionData['correct_en'],
                    'sort_order' => $optionIndex + 1,
                ]);
            }
        }
    }

    private function normalizeLegacySoundPlaceholders(): void
    {
        Question::query()
            ->where('question_type', 'sound_choice')
            ->get()
            ->each(function (Question $question): void {
                $metadata = $question->metadata ?? [];
                $audioPath = (string) ($metadata['audio_path'] ?? '');

                if (str_starts_with($audioPath, '/audio/lessons/')) {
                    $metadata['audio_path'] = null;
                }

                $metadata['audio_alt_en'] ??= 'Folklore audio clue';
                $metadata['audio_alt_mk'] ??= 'Аудио загатка од народна песна';
                $metadata['audio_type'] ??= 'folklore';
                $metadata['audio_credit'] ??= 'Placeholder. Original MakedonIQ recording to be added later.';

                $question->update(['metadata' => $metadata]);
            });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function soundQuestions(): array
    {
        return [
            [
                'correct_en' => 'Makedonsko Devojche',
                'correct_mk' => 'Македонско девојче',
                'options' => [
                    ['en' => 'Makedonsko Devojche', 'mk' => 'Македонско девојче'],
                    ['en' => 'Jovano, Jovanke', 'mk' => 'Јовано, Јованке'],
                    ['en' => 'Nazad, Nazad Kalino Mome', 'mk' => 'Назад, назад Калино моме'],
                    ['en' => 'Ja Izlezi Gjurgo', 'mk' => 'Ја излези Ѓурѓо'],
                ],
            ],
            [
                'correct_en' => 'Jovano, Jovanke',
                'correct_mk' => 'Јовано, Јованке',
                'options' => [
                    ['en' => 'Jovano, Jovanke', 'mk' => 'Јовано, Јованке'],
                    ['en' => 'Makedonsko Devojche', 'mk' => 'Македонско девојче'],
                    ['en' => 'Ushti, Ushti Baba', 'mk' => 'Ушти, ушти баба'],
                    ['en' => 'Nazad, Nazad Kalino Mome', 'mk' => 'Назад, назад Калино моме'],
                ],
            ],
            [
                'correct_en' => 'Nazad, Nazad Kalino Mome',
                'correct_mk' => 'Назад, назад Калино моме',
                'options' => [
                    ['en' => 'Nazad, Nazad Kalino Mome', 'mk' => 'Назад, назад Калино моме'],
                    ['en' => 'Ja Izlezi Gjurgo', 'mk' => 'Ја излези Ѓурѓо'],
                    ['en' => 'Makedonsko Devojche', 'mk' => 'Македонско девојче'],
                    ['en' => 'Jovano, Jovanke', 'mk' => 'Јовано, Јованке'],
                ],
            ],
        ];
    }
}
