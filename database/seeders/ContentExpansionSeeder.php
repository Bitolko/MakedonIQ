<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class ContentExpansionSeeder extends Seeder
{
    private const BEGINNER_MAP_KEYS = [
        'skopje',
        'ohrid',
        'bitola',
        'tetovo',
        'prilep',
        'kumanovo',
        'strumica',
        'lake-ohrid',
        'lake-prespa',
        'stip',
    ];

    private const EXTENDED_MAP_KEYS = [
        'veles',
        'gostivar',
        'struga',
        'kicevo',
        'kavadarci',
        'gevgelija',
        'kocani',
        'matka-canyon',
        'vodno',
        'mavrovo',
        'pelister',
    ];

    public function run(): void
    {
        $this->seedLessons();
        $this->seedQuizzes();
        $this->seedMapChallengeQuestions();
    }

    private function seedLessons(): void
    {
        $sortByCategory = [];

        foreach ($this->lessons() as $lessonData) {
            $category = Category::where('slug', $lessonData['category_slug'])->first();

            if (! $category) {
                continue;
            }

            $sortByCategory[$category->slug] = ($sortByCategory[$category->slug] ?? 0) + 1;

            $lesson = Lesson::updateOrCreate(
                ['slug' => $lessonData['slug']],
                [
                    'category_id' => $category->id,
                    'title_en' => $lessonData['title_en'],
                    'title_mk' => $lessonData['title_mk'],
                    'summary_en' => $lessonData['summary_en'],
                    'summary_mk' => $lessonData['summary_mk'],
                    'content_en' => $this->lessonContentEn($lessonData),
                    'content_mk' => $this->lessonContentMk($lessonData),
                    'difficulty' => 'beginner',
                    'estimated_minutes' => $lessonData['estimated_minutes'],
                    'sort_order' => $sortByCategory[$category->slug],
                    'is_published' => true,
                ],
            );

            if (! empty($lessonData['quiz_slug'])) {
                Quiz::where('slug', $lessonData['quiz_slug'])->update(['lesson_id' => $lesson->id]);
            }
        }
    }

    private function seedQuizzes(): void
    {
        foreach ($this->quizzes() as $quizData) {
            $category = Category::where('slug', $quizData['category_slug'])->first();

            if (! $category) {
                continue;
            }

            $lesson = Lesson::where('slug', $quizData['lesson_slug'])->first();

            $quiz = $category->quizzes()->updateOrCreate(
                ['slug' => $quizData['slug']],
                [
                    'lesson_id' => $lesson?->id,
                    'title_en' => $quizData['title_en'],
                    'title_mk' => $quizData['title_mk'],
                    'description_en' => $quizData['description_en'],
                    'description_mk' => $quizData['description_mk'],
                    'difficulty' => $quizData['difficulty'] ?? 'beginner',
                    'estimated_minutes' => $quizData['estimated_minutes'] ?? 8,
                    'points_per_question' => 10,
                    'is_published' => true,
                    'sort_order' => $quizData['sort_order'] ?? 20,
                ],
            );

            $this->syncQuestions($quiz, $quizData['questions']);
        }
    }

    private function seedMapChallengeQuestions(): void
    {
        $category = Category::where('slug', 'geography')->first();

        if (! $category) {
            return;
        }

        $lesson = Lesson::where('slug', 'macedonian-geography-basics')->first();
        $questionsByKey = collect($this->mapQuestions())->keyBy('map_key');

        $mainQuiz = $category->quizzes()->updateOrCreate(
            ['slug' => 'macedonia-map-challenge'],
            [
                'lesson_id' => $lesson?->id,
                'title_en' => 'Macedonia Map Challenge',
                'title_mk' => 'Мапа предизвик за Македонија',
                'description_en' => 'Look at the highlighted point on the map and choose the correct city, lake, or landmark.',
                'description_mk' => 'Погледни ја означената точка на мапата и избери го точниот град, езеро или место.',
                'difficulty' => 'beginner',
                'estimated_minutes' => 8,
                'points_per_question' => 10,
                'is_published' => true,
                'sort_order' => 2,
            ],
        );

        $extendedQuiz = $category->quizzes()->updateOrCreate(
            ['slug' => 'macedonia-map-challenge-extended'],
            [
                'lesson_id' => $lesson?->id,
                'title_en' => 'Macedonia Map Challenge: Extended',
                'title_mk' => 'Мапа предизвик за Македонија: проширено',
                'description_en' => 'Practise more Macedonian cities and landmarks after the beginner map challenge.',
                'description_mk' => 'Вежбај повеќе македонски градови и места по почетниот предизвик со мапа.',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'points_per_question' => 10,
                'is_published' => true,
                'sort_order' => 3,
            ],
        );

        $this->syncMapQuizQuestions(
            $mainQuiz,
            collect(self::BEGINNER_MAP_KEYS)
                ->map(fn (string $key) => $questionsByKey->get($key))
                ->filter()
                ->values()
                ->all(),
        );

        $this->syncMapQuizQuestions(
            $extendedQuiz,
            collect(self::EXTENDED_MAP_KEYS)
                ->map(fn (string $key) => $questionsByKey->get($key))
                ->filter()
                ->values()
                ->all(),
        );
    }

    private function syncMapQuizQuestions(Quiz $quiz, array $questions): void
    {
        $activeKeys = array_column($questions, 'map_key');

        foreach ($questions as $index => $questionData) {
            $sortOrder = $index + 1;
            $question = $quiz->questions()
                ->where('metadata->map_target_key', $questionData['map_key'])
                ->first();

            if (! $question) {
                $question = $quiz->questions()->create($this->mapQuestionAttributes($questionData, $sortOrder));
            } else {
                $question->update($this->mapQuestionAttributes($questionData, $sortOrder));
            }

            if (! $question->attemptAnswers()->exists()) {
                $this->syncAnswers($question, $questionData['answers']);
            }
        }

        $quiz->questions()
            ->where('question_type', 'map_guess')
            ->get()
            ->each(function ($question) use ($activeKeys): void {
                $targetKey = $question->metadata['map_target_key'] ?? null;

                if ($targetKey && ! in_array($targetKey, $activeKeys, true)) {
                    $question->update(['is_published' => false]);
                }
            });
    }

    private function syncQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $index => $questionData) {
            $question = $quiz->questions()->firstOrCreate(
                ['question_en' => $questionData['question_en']],
                $this->questionAttributes($questionData, $index + 1),
            );

            if ($question->attemptAnswers()->exists()) {
                continue;
            }

            $question->update($this->questionAttributes($questionData, $index + 1));
            $this->syncAnswers($question, $questionData['answers']);
        }
    }

    private function syncAnswers($question, array $answers): void
    {
        $question->answers()->delete();

        foreach ($answers as $index => $answerData) {
            $question->answers()->create([
                'answer_en' => $answerData['answer_en'],
                'answer_mk' => $answerData['answer_mk'] ?? null,
                'is_correct' => $answerData['is_correct'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function questionAttributes(array $questionData, int $sortOrder): array
    {
        return [
            'question_type' => $questionData['question_type'] ?? 'multiple_choice',
            'translation_direction' => $questionData['translation_direction'] ?? null,
            'metadata' => $questionData['metadata'] ?? null,
            'question_en' => $questionData['question_en'],
            'question_mk' => $questionData['question_mk'],
            'explanation_en' => $questionData['explanation_en'],
            'explanation_mk' => $questionData['explanation_mk'],
            'sort_order' => $sortOrder,
            'points' => null,
            'is_published' => true,
        ];
    }

    private function mapQuestionAttributes(array $questionData, int $sortOrder): array
    {
        return $this->questionAttributes([
            'question_type' => 'map_guess',
            'metadata' => [
                'map_target_key' => $questionData['map_key'],
                'map_target_label_en' => $questionData['answer_en'],
                'map_target_label_mk' => $questionData['answer_mk'],
                'map_x' => $questionData['x'],
                'map_y' => $questionData['y'],
                'target_type' => $questionData['target_type'],
            ],
            'question_en' => $questionData['target_type'] === 'lake'
                ? 'Which lake is highlighted on the map?'
                : ($questionData['target_type'] === 'landmark'
                    ? 'Which place is highlighted on the map?'
                    : 'Which city is highlighted on the map?'),
            'question_mk' => $questionData['target_type'] === 'lake'
                ? 'Кое езеро е означено на мапата?'
                : ($questionData['target_type'] === 'landmark'
                    ? 'Кое место е означено на мапата?'
                    : 'Кој град е означен на мапата?'),
            'explanation_en' => $questionData['explanation_en'],
            'explanation_mk' => $questionData['explanation_mk'],
        ], $sortOrder);
    }

    private function lessons(): array
    {
        return [
            $this->lesson('macedonian-language', 'basic-macedonian-greetings', 'basic-macedonian-greetings', 'Basic Greetings and Everyday Phrases', 'Основни поздрави и секојдневни фрази', 'Start speaking with warm greetings, polite words, and simple family phrases.', 'Почни со топли поздрави, љубезни зборови и едноставни семејни фрази.', 6, ['здраво = hello', 'добро утро = good morning', 'благодарам = thank you', 'како си? = how are you?'], ['Здраво = hello', 'Добро утро = good morning', 'Благодарам = thank you', 'Како си? = how are you?'], ['Say здраво when you meet someone.', 'Use благодарам when someone helps you.'], ['Кажи здраво кога ќе сретнеш некого.', 'Користи благодарам кога некој ти помага.'], 'Greet someone at home and ask how they are.', 'Поздрави некого дома и прашај како е.'),
            $this->lesson('macedonian-language', 'numbers-1-20', 'numbers-1-20-quiz', 'Numbers 1-20', 'Броеви од 1 до 20', 'Learn the first Macedonian numbers for age, counting, and games.', 'Научи ги првите македонски броеви за возраст, броење и игри.', 6, ['еден = one', 'два = two', 'пет = five', 'десет = ten', 'дваесет = twenty'], ['еден = one', 'два = two', 'пет = five', 'десет = ten', 'дваесет = twenty'], ['Јас имам десет книги means I have ten books.', 'Count family members from еден to десет.'], ['Јас имам десет книги значи I have ten books.', 'Број членови од семејството од еден до десет.'], 'Count five objects in Macedonian.', 'Изброј пет предмети на македонски.'),
            $this->lesson('macedonian-language', 'family-words', 'family-words-quiz', 'Family Words', 'Семејни зборови', 'Practise names for close family members and respectful everyday speech.', 'Вежбај имиња за блиски членови на семејството и љубезен говор.', 6, ['мајка = mother', 'татко = father', 'брат = brother', 'сестра = sister', 'баба = grandmother'], ['мајка = mother', 'татко = father', 'брат = brother', 'сестра = sister', 'баба = grandmother'], ['Мојата мајка means my mother.', 'Мојот брат means my brother.'], ['Мојата мајка значи my mother.', 'Мојот брат значи my brother.'], 'Name three family members in Macedonian.', 'Именувај три членови на семејството на македонски.'),
            $this->lesson('macedonian-language', 'days-months-and-time', null, 'Days, Months, and Time', 'Денови, месеци и време', 'Use simple words for today, tomorrow, days, months, and time.', 'Користи едноставни зборови за денес, утре, денови, месеци и време.', 7, ['денес = today', 'утре = tomorrow', 'понеделник = Monday', 'јануари = January', 'час = hour'], ['денес = today', 'утре = tomorrow', 'понеделник = Monday', 'јануари = January', 'час = hour'], ['Денес е понеделник means today is Monday.', 'Утре учиме means tomorrow we study.'], ['Денес е понеделник значи today is Monday.', 'Утре учиме значи tomorrow we study.'], 'Say today and tomorrow in Macedonian.', 'Кажи денес и утре на македонски.'),
            $this->lesson('macedonian-language', 'common-classroom-words', null, 'Common Classroom Words', 'Чести зборови во училница', 'Learn useful classroom words for reading, writing, and asking questions.', 'Научи корисни зборови од училница за читање, пишување и прашување.', 6, ['книга = book', 'молив = pencil', 'учител = teacher', 'прашање = question', 'одговор = answer'], ['книга = book', 'молив = pencil', 'учител = teacher', 'прашање = question', 'одговор = answer'], ['Имам книга means I have a book.', 'Одговор means answer.'], ['Имам книга значи I have a book.', 'Одговор значи answer.'], 'Point to a book and pencil and say the Macedonian words.', 'Покажи книга и молив и кажи ги зборовите.'),

            $this->lesson('macedonian-alphabet', 'introduction-to-macedonian-cyrillic-alphabet', 'cyrillic-alphabet-basics', 'Macedonian Cyrillic Alphabet Basics', 'Основи на македонската кирилична азбука', 'Meet the Cyrillic script and the 31 letters of modern Macedonian.', 'Запознај ја кирилицата и 31 буква во современиот македонски јазик.', 7, ['азбука = alphabet', 'буква = letter', 'звук = sound', 'збор = word'], ['азбука = alphabet', 'буква = letter', 'звук = sound', 'збор = word'], ['The letter М makes the m sound.', 'Македонија starts with М.'], ['Буквата М го дава звукот m.', 'Македонија почнува со М.'], 'Find three Macedonian letters you recognise.', 'Најди три македонски букви што ги препознаваш.'),
            $this->lesson('macedonian-alphabet', 'vowels-and-consonants', 'letter-recognition-quiz', 'Vowels and Consonants', 'Самогласки и согласки', 'Notice how vowel and consonant sounds build Macedonian words.', 'Забележи како самогласките и согласките градат македонски зборови.', 6, ['А = a', 'Е = e', 'И = i', 'О = o', 'У = u'], ['А = a', 'Е = e', 'И = i', 'О = o', 'У = u'], ['Мама uses the vowel А.', 'Ден uses Е.'], ['Мама ја користи самогласката А.', 'Ден ја користи Е.'], 'Say the five vowel sounds aloud.', 'Кажи ги петте самогласки на глас.'),
            $this->lesson('macedonian-alphabet', 'letters-that-look-familiar', null, 'Letters That Look Familiar', 'Букви што изгледаат познато', 'Compare letters that look familiar and letters that sound different.', 'Спореди букви што изгледаат познато и букви што звучат различно.', 6, ['А looks like A', 'М looks like M', 'Р sounds like r', 'В sounds like v'], ['А изгледа како A', 'М изгледа како M', 'Р звучи како r', 'В звучи како v'], ['The letter Р is not the English P sound.', 'В is closer to the English v sound.'], ['Буквата Р не е англиско P.', 'В е поблиску до англискиот звук v.'], 'Circle familiar-looking letters in a Macedonian word.', 'Заокружи познати букви во македонски збор.'),
            $this->lesson('macedonian-alphabet', 'reading-simple-macedonian-words', 'simple-word-reading-quiz', 'Reading Simple Macedonian Words', 'Читање едноставни македонски зборови', 'Blend letters slowly to read short Macedonian words.', 'Спојувај букви полека за да читаш кратки македонски зборови.', 7, ['мама = mum', 'дом = home', 'вода = water', 'ден = day'], ['мама = mum', 'дом = home', 'вода = water', 'ден = day'], ['Read мама as ма-ма.', 'Read вода as во-да.'], ['Читај мама како ма-ма.', 'Читај вода како во-да.'], 'Read one short word aloud three times.', 'Прочитај еден краток збор три пати.'),
            $this->lesson('macedonian-alphabet', 'writing-your-first-macedonian-words', null, 'Writing Your First Macedonian Words', 'Пишување на првите македонски зборови', 'Practise writing short words with careful letter shapes.', 'Вежбај пишување кратки зборови со внимателни форми на буквите.', 7, ['мама', 'дом', 'ден', 'вода'], ['мама', 'дом', 'ден', 'вода'], ['Write slowly and leave space between letters.', 'Say each sound while writing.'], ['Пиши полека и остави простор меѓу буквите.', 'Кажи го секој звук додека пишуваш.'], 'Copy the word мама three times.', 'Препиши го зборот мама три пати.'),

            $this->lesson('geography', 'macedonian-geography-basics', 'macedonian-geography-basics', 'Cities, Lakes, and Mountains', 'Градови, езера и планини', 'Learn the places and landscapes that shape everyday life.', 'Научи за местата и пејзажите што го обликуваат секојдневието.', 7, ['Скопје = capital city', 'Охрид = lake city', 'планина = mountain', 'езеро = lake'], ['Скопје = capital city', 'Охрид = lake city', 'планина = mountain', 'езеро = lake'], ['Skopje is the capital.', 'Ohrid is famous for its lake.'], ['Скопје е главен град.', 'Охрид е познат по езерото.'], 'Find Skopje and Ohrid on a map.', 'Најди ги Скопје и Охрид на мапа.'),
            $this->lesson('geography', 'skopje-ohrid-and-bitola', 'cities-of-macedonia-quiz', 'Skopje, Ohrid, and Bitola', 'Скопје, Охрид и Битола', 'Meet three important cities and what learners often remember about them.', 'Запознај три важни града и што најчесто се памети за нив.', 7, ['Скопје = capital', 'Охрид = lake and old town', 'Битола = southern city', 'град = city'], ['Скопје = capital', 'Охрид = lake and old town', 'Битола = southern city', 'град = city'], ['Skopje is a busy centre.', 'Bitola is known for city history and culture.'], ['Скопје е жив центар.', 'Битола е позната по градска историја и култура.'], 'Say one fact about each city.', 'Кажи по еден факт за секој град.'),
            $this->lesson('geography', 'lake-ohrid-and-lake-prespa', 'lakes-and-mountains-quiz', 'Lake Ohrid and Lake Prespa', 'Охридско Езеро и Преспанско Езеро', 'Learn why lakes are important for geography, travel, and memory.', 'Научи зошто езерата се важни за географија, патување и сеќавање.', 6, ['езеро = lake', 'Охридско Езеро', 'Преспанско Езеро', 'брег = shore'], ['езеро = lake', 'Охридско Езеро', 'Преспанско Езеро', 'брег = shore'], ['Lake Ohrid is very well known.', 'Lake Prespa is another important lake.'], ['Охридското Езеро е многу познато.', 'Преспанското Езеро е уште едно важно езеро.'], 'Name two lakes in Macedonian.', 'Именувај две езера на македонски.'),
            $this->lesson('geography', 'mountains-and-national-parks', null, 'Mountains and National Parks', 'Планини и национални паркови', 'Discover mountain places such as Mavrovo, Pelister, and Vodno.', 'Откриј планински места како Маврово, Пелистер и Водно.', 7, ['планина = mountain', 'парк = park', 'Маврово', 'Пелистер', 'Водно'], ['планина = mountain', 'парк = park', 'Маврово', 'Пелистер', 'Водно'], ['Vodno is near Skopje.', 'Pelister is associated with mountain nature.'], ['Водно е близу Скопје.', 'Пелистер е поврзан со планинска природа.'], 'Choose one mountain place to remember.', 'Избери едно планинско место за паметење.'),
            $this->lesson('geography', 'macedonian-regions-and-travel', null, 'Macedonian Regions and Travel', 'Македонски региони и патување', 'Use simple geography words to talk about travel and regions.', 'Користи едноставни географски зборови за патување и региони.', 7, ['регион = region', 'патување = travel', 'север = north', 'југ = south'], ['регион = region', 'патување = travel', 'север = north', 'југ = south'], ['Some cities are north, south, east, or west.', 'Travel helps connect places with family stories.'], ['Некои градови се на север, југ, исток или запад.', 'Патувањето ги поврзува местата со семејни приказни.'], 'Describe a trip using north, south, east, or west.', 'Опиши патување со север, југ, исток или запад.'),

            $this->lesson('history-of-macedonia', 'macedonia-history-basics', 'macedonia-history-basics', 'Learning Macedonian History', 'Учење македонска историја', 'Learn history through places, stories, family memory, and respectful questions.', 'Учи историја преку места, приказни, семејно сеќавање и внимателни прашања.', 7, ['историја = history', 'приказна = story', 'музеј = museum', 'семејство = family'], ['историја = history', 'приказна = story', 'музеј = museum', 'семејство = family'], ['History includes stories and places.', 'Families preserve memory in many ways.'], ['Историјата вклучува приказни и места.', 'Семејствата чуваат сеќавања на многу начини.'], 'Ask an older family member about one memory.', 'Прашај постар член од семејството за едно сеќавање.'),
            $this->lesson('history-of-macedonia', 'ohrid-as-a-cultural-centre', 'ohrid-and-skopje-quiz', 'Ohrid as a Cultural Centre', 'Охрид како културен центар', 'Learn why Ohrid is remembered for learning, churches, lake life, and culture.', 'Научи зошто Охрид се памети по учење, цркви, езеро и култура.', 7, ['Охрид', 'езеро = lake', 'култура = culture', 'учење = learning'], ['Охрид', 'езеро = lake', 'култура = culture', 'учење = learning'], ['Ohrid is linked with learning traditions.', 'The lake is part of the city identity.'], ['Охрид е поврзан со традиции на учење.', 'Езерото е дел од идентитетот на градот.'], 'Name one reason Ohrid is important.', 'Кажи една причина зошто Охрид е важен.'),
            $this->lesson('history-of-macedonia', 'skopje-through-time', null, 'Skopje Through Time', 'Скопје низ времето', 'See Skopje as a place where old and new stories meet.', 'Запознај го Скопје како место каде се среќаваат стари и нови приказни.', 7, ['Скопје', 'главен град = capital city', 'мост = bridge', 'пазар = market'], ['Скопје', 'главен град = capital city', 'мост = bridge', 'пазар = market'], ['Skopje is the capital city today.', 'A city can hold many layers of memory.'], ['Скопје денес е главен град.', 'Еден град може да има многу слоеви на сеќавање.'], 'Think of one old place and one new place in a city.', 'Помисли на едно старо и едно ново место во град.'),
            $this->lesson('history-of-macedonia', 'how-families-preserve-history', 'family-history-and-traditions-quiz', 'How Families Preserve History', 'Како семејствата ја чуваат историјата', 'Explore photos, recipes, songs, names, and stories as family history.', 'Истражи фотографии, рецепти, песни, имиња и приказни како семејна историја.', 6, ['фотографија = photograph', 'рецепт = recipe', 'песна = song', 'име = name'], ['фотографија = photograph', 'рецепт = recipe', 'песна = song', 'име = name'], ['A recipe can carry memory.', 'A song can remind families of a place.'], ['Рецепт може да носи сеќавање.', 'Песна може да потсети на место.'], 'Choose one family object and tell its story.', 'Избери еден семеен предмет и раскажи ја приказната.'),
            $this->lesson('history-of-macedonia', 'macedonian-migration-and-community-life-in-australia', null, 'Macedonian Migration and Community Life in Australia', 'Македонска миграција и живот во заедница во Австралија', 'Learn how language, food, sport, churches, and clubs help communities stay connected.', 'Научи како јазикот, храната, спортот, црквите и клубовите ги поврзуваат заедниците.', 8, ['Австралија', 'заедница = community', 'јазик = language', 'клуб = club'], ['Австралија', 'заедница = community', 'јазик = language', 'клуб = club'], ['Community events help families meet.', 'Language keeps generations connected.'], ['Настаните во заедницата им помагаат на семејствата да се сретнат.', 'Јазикот ги поврзува генерациите.'], 'Name one way families keep culture alive in Australia.', 'Кажи еден начин како семејствата ја чуваат културата во Австралија.'),

            $this->lesson('culture-and-traditions', 'macedonian-culture-basics', 'macedonian-culture-basics', 'Family, Oro, and Celebrations', 'Семејство, оро и прослави', 'Learn how family gatherings, dancing, and shared meals connect people.', 'Научи како семејните собири, оро и заеднички оброци ги поврзуваат луѓето.', 6, ['семејство = family', 'оро = circle dance', 'прослава = celebration', 'гости = guests'], ['семејство = family', 'оро = circle dance', 'прослава = celebration', 'гости = guests'], ['Oro brings people together.', 'Celebrations often include food and music.'], ['Оро ги зближува луѓето.', 'Прославите често имаат храна и музика.'], 'Ask someone when they last saw oro.', 'Прашај некого кога последен пат видел оро.'),
            $this->lesson('culture-and-traditions', 'macedonian-weddings', 'oro-and-weddings-quiz', 'Macedonian Weddings', 'Македонски свадби', 'Meet simple wedding words connected to music, dancing, family, and guests.', 'Запознај едноставни свадбени зборови поврзани со музика, оро, семејство и гости.', 6, ['свадба = wedding', 'невеста = bride', 'младоженец = groom', 'музика = music'], ['свадба = wedding', 'невеста = bride', 'младоженец = groom', 'музика = music'], ['Weddings often include music and dancing.', 'Guests celebrate with the family.'], ['Свадбите често имаат музика и оро.', 'Гостите слават со семејството.'], 'Learn the words свадба and гости.', 'Научи ги зборовите свадба и гости.'),
            $this->lesson('culture-and-traditions', 'holidays-and-family-gatherings', 'traditions-and-celebrations-quiz', 'Holidays and Family Gatherings', 'Празници и семејни собири', 'Use simple words for holidays, visits, guests, and shared meals.', 'Користи едноставни зборови за празници, посети, гости и заеднички оброци.', 6, ['празник = holiday', 'гости = guests', 'ручек = lunch', 'посета = visit'], ['празник = holiday', 'гости = guests', 'ручек = lunch', 'посета = visit'], ['A holiday can bring relatives together.', 'Food is often shared at gatherings.'], ['Празник може да ги собере роднините.', 'Храната често се споделува на собири.'], 'Describe one family gathering in two words.', 'Опиши еден семеен собир со два збора.'),
            $this->lesson('culture-and-traditions', 'traditional-clothing-and-symbols', null, 'Traditional Clothing and Symbols', 'Традиционална облека и симболи', 'Notice colours, patterns, clothing, and symbols in respectful cultural learning.', 'Забележи бои, шари, облека и симболи во внимателно културно учење.', 6, ['облека = clothing', 'шара = pattern', 'боја = colour', 'симбол = symbol'], ['облека = clothing', 'шара = pattern', 'боја = colour', 'симбол = symbol'], ['Patterns can tell a local story.', 'Symbols should be learned with respect.'], ['Шарите можат да раскажат локална приказна.', 'Симболите треба да се учат со почит.'], 'Notice one colour or pattern at a community event.', 'Забележи една боја или шара на настан.'),
            $this->lesson('culture-and-traditions', 'macedonian-community-life-in-australia', 'macedonian-community-quiz', 'Macedonian Community Life in Australia', 'Македонски заеднички живот во Австралија', 'Learn how language schools, clubs, churches, and events support connection.', 'Научи како јазични училишта, клубови, цркви и настани ја поддржуваат поврзаноста.', 7, ['заедница = community', 'училиште = school', 'клуб = club', 'настан = event'], ['заедница = community', 'училиште = school', 'клуб = club', 'настан = event'], ['A community event can help children hear Macedonian.', 'Clubs and schools support belonging.'], ['Настан во заедницата им помага на децата да слушнат македонски.', 'Клубовите и училиштата помагаат за припадност.'], 'Name one place where Macedonian can be heard in Australia.', 'Именувај едно место каде може да се слушне македонски во Австралија.'),

            $this->lesson('food-and-music', 'macedonian-food-and-music-basics', 'macedonian-food-and-music-basics', 'Macedonian Food and Music', 'Македонска храна и музика', 'Explore how dishes and songs bring people together.', 'Истражи како јадењата и песните ги зближуваат луѓето.', 6, ['храна = food', 'музика = music', 'песна = song', 'трпеза = table meal'], ['храна = food', 'музика = music', 'песна = song', 'трпеза = table meal'], ['Food and music are part of celebrations.', 'A song can carry memory.'], ['Храната и музиката се дел од прослави.', 'Песна може да носи сеќавање.'], 'Name one food and one song style you know.', 'Именувај една храна и еден стил на песна што го знаеш.'),
            $this->lesson('food-and-music', 'tavce-gravce', 'ajvar-and-tavce-gravce-quiz', 'Tavče Gravče', 'Тавче гравче', 'Learn about a warm baked bean dish often shared at home.', 'Научи за топло јадење со грав што често се споделува дома.', 5, ['грав = beans', 'тавче = small baking dish', 'ручек = lunch', 'топло = warm'], ['грав = beans', 'тавче = small baking dish', 'ручек = lunch', 'топло = warm'], ['Tavče gravče is baked beans.', 'It is often served warm.'], ['Тавче гравче е печен грав.', 'Често се служи топло.'], 'Say грав and тавче aloud.', 'Кажи грав и тавче на глас.'),
            $this->lesson('food-and-music', 'ajvar-and-peppers', null, 'Ajvar and Peppers', 'Ајвар и пиперки', 'Learn simple words for peppers, roasting, bread, and shared meals.', 'Научи едноставни зборови за пиперки, печење, леб и заеднички оброци.', 5, ['ајвар = pepper spread', 'пиперка = pepper', 'леб = bread', 'печено = roasted'], ['ајвар = pepper spread', 'пиперка = pepper', 'леб = bread', 'печено = roasted'], ['Ajvar is often made with roasted peppers.', 'It can be eaten with bread.'], ['Ајвар често се прави со печени пиперки.', 'Може да се јаде со леб.'], 'Point to red peppers and say пиперки.', 'Покажи црвени пиперки и кажи пиперки.'),
            $this->lesson('food-and-music', 'shopska-salad-and-shared-meals', 'food-basics-quiz', 'Shopska Salad and Shared Meals', 'Шопска салата и заеднички оброци', 'Use food words for vegetables, cheese, table, and sharing.', 'Користи зборови за зеленчук, сирење, трпеза и споделување.', 5, ['домат = tomato', 'краставица = cucumber', 'сирење = cheese', 'салата = salad'], ['домат = tomato', 'краставица = cucumber', 'сирење = cheese', 'салата = salad'], ['Shopska salad often has tomato, cucumber, and cheese.', 'Shared meals help families talk.'], ['Шопска салата често има домат, краставица и сирење.', 'Заеднички оброци им помагаат на семејствата да разговараат.'], 'Name two salad ingredients.', 'Именувај две состојки за салата.'),
            $this->lesson('food-and-music', 'folk-music-and-celebrations', 'music-and-celebrations-quiz', 'Folk Music and Celebrations', 'Народна музика и прослави', 'Hear how rhythm, songs, and instruments support dancing and memory.', 'Слушни како ритамот, песните и инструментите го поддржуваат орото и сеќавањето.', 6, ['песна = song', 'ритам = rhythm', 'оро = dance', 'хармоника = accordion'], ['песна = song', 'ритам = rhythm', 'оро = dance', 'хармоника = accordion'], ['Music can guide dancing.', 'An accordion may be heard at celebrations.'], ['Музиката може да го води орото.', 'Хармоника може да се слушне на прослави.'], 'Clap a simple rhythm and say оро.', 'Плесни едноставен ритам и кажи оро.'),
        ];
    }

    private function quizzes(): array
    {
        return [
            $this->translationQuiz('macedonian-language', 'basic-macedonian-greetings', 'basic-greetings-quiz', 'Basic Greetings Quiz', 'Квиз за основни поздрави', 'Practise greeting words and polite everyday phrases.', 'Вежбај поздрави и љубезни секојдневни фрази.', 21, [
                ['здраво', 'Hello', ['Good night', 'Please', 'Book']],
                ['добар ден', 'Good day', ['Good night', 'Thank you', 'Teacher']],
                ['добра вечер', 'Good evening', ['Good morning', 'Goodbye', 'Pencil']],
                ['благодарам', 'Thank you', ['Hello', 'Good night', 'Water']],
                ['како си?', 'How are you?', ['Where are you?', 'How old are you?', 'What is this?']],
            ]),
            $this->translationQuiz('macedonian-language', 'numbers-1-20', 'numbers-1-20-quiz', 'Numbers 1-20 Quiz', 'Квиз за броеви од 1 до 20', 'Practise the first Macedonian numbers.', 'Вежбај ги првите македонски броеви.', 22, [
                ['еден', 'One', ['Two', 'Five', 'Ten']],
                ['два', 'Two', ['Three', 'Seven', 'Twenty']],
                ['пет', 'Five', ['Four', 'Nine', 'Eleven']],
                ['десет', 'Ten', ['One', 'Twelve', 'Twenty']],
                ['дваесет', 'Twenty', ['Two', 'Ten', 'Fifteen']],
            ]),
            $this->translationQuiz('macedonian-language', 'family-words', 'family-words-quiz', 'Family Words Quiz', 'Квиз за семејни зборови', 'Practise close family words.', 'Вежбај зборови за блиско семејство.', 23, [
                ['мајка', 'Mother', ['Father', 'Sister', 'Grandfather']],
                ['татко', 'Father', ['Mother', 'Brother', 'Grandmother']],
                ['брат', 'Brother', ['Sister', 'Aunt', 'Mother']],
                ['сестра', 'Sister', ['Brother', 'Father', 'Uncle']],
                ['баба', 'Grandmother', ['Grandfather', 'Mother', 'Cousin']],
            ]),

            $this->factQuiz('macedonian-alphabet', 'introduction-to-macedonian-cyrillic-alphabet', 'cyrillic-alphabet-basics-quiz', 'Cyrillic Alphabet Basics Quiz', 'Квиз за основи на кирилицата', 'Review the Macedonian Cyrillic alphabet basics.', 'Повтори ги основите на македонската кирилица.', 21, [
                $this->factQuestion('How many letters are in the modern Macedonian alphabet?', 'Колку букви има современата македонска азбука?', 'The modern Macedonian alphabet has 31 letters.', 'Современата македонска азбука има 31 буква.', [['31', '31', true], ['26', '26', false], ['28', '28', false], ['33', '33', false]]),
                $this->factQuestion('Which letter makes the m sound?', 'Која буква го дава звукот m?', 'М makes the m sound.', 'М го дава звукот m.', [['М', 'М', true], ['А', 'А', false], ['О', 'О', false], ['Т', 'Т', false]]),
                $this->factQuestion('Which letter makes the a sound?', 'Која буква го дава звукот a?', 'А makes the a sound.', 'А го дава звукот a.', [['А', 'А', true], ['Е', 'Е', false], ['И', 'И', false], ['У', 'У', false]]),
                $this->factQuestion('What script does Macedonian use?', 'Кое писмо го користи македонскиот јазик?', 'Macedonian uses Cyrillic script.', 'Македонскиот јазик користи кирилица.', [['Cyrillic', 'Кирилица', true], ['Latin only', 'Само латиница', false], ['Greek only', 'Само грчко писмо', false], ['Arabic', 'Арапско писмо', false]]),
                $this->factQuestion('Which word starts with М?', 'Кој збор почнува со М?', 'Македонија starts with М.', 'Македонија почнува со М.', [['Македонија', 'Македонија', true], ['ден', 'ден', false], ['вода', 'вода', false], ['книга', 'книга', false]]),
            ]),
            $this->factQuiz('macedonian-alphabet', 'vowels-and-consonants', 'letter-recognition-quiz', 'Letter Recognition Quiz', 'Квиз за препознавање букви', 'Recognise common Macedonian letters and sounds.', 'Препознај чести македонски букви и звуци.', 22, [
                $this->factQuestion('Which letter makes the v sound?', 'Која буква го дава звукот v?', 'В makes the v sound.', 'В го дава звукот v.', [['В', 'В', true], ['Б', 'Б', false], ['Р', 'Р', false], ['П', 'П', false]]),
                $this->factQuestion('Which letter makes the b sound?', 'Која буква го дава звукот b?', 'Б makes the b sound.', 'Б го дава звукот b.', [['Б', 'Б', true], ['В', 'В', false], ['Д', 'Д', false], ['Л', 'Л', false]]),
                $this->factQuestion('Which letter is a vowel?', 'Која буква е самогласка?', 'О is a vowel.', 'О е самогласка.', [['О', 'О', true], ['Б', 'Б', false], ['М', 'М', false], ['К', 'К', false]]),
                $this->factQuestion('Which letter makes the d sound?', 'Која буква го дава звукот d?', 'Д makes the d sound.', 'Д го дава звукот d.', [['Д', 'Д', true], ['Т', 'Т', false], ['Н', 'Н', false], ['З', 'З', false]]),
                $this->factQuestion('Which letter makes the r sound?', 'Која буква го дава звукот r?', 'Р makes the r sound.', 'Р го дава звукот r.', [['Р', 'Р', true], ['П', 'П', false], ['В', 'В', false], ['Ф', 'Ф', false]]),
            ]),
            $this->translationQuiz('macedonian-alphabet', 'reading-simple-macedonian-words', 'simple-word-reading-quiz', 'Simple Word Reading Quiz', 'Квиз за читање едноставни зборови', 'Read short Macedonian words and match their meaning.', 'Читај кратки македонски зборови и поврзи ги со значење.', 23, [
                ['мама', 'Mum', ['Day', 'Water', 'Book']],
                ['дом', 'Home', ['School', 'Lake', 'Music']],
                ['вода', 'Water', ['Bread', 'City', 'Dance']],
                ['ден', 'Day', ['Night', 'Month', 'Pencil']],
                ['брат', 'Brother', ['Sister', 'Teacher', 'Guest']],
            ]),

            $this->factQuiz('geography', 'skopje-ohrid-and-bitola', 'cities-of-macedonia-quiz', 'Cities of Macedonia Quiz', 'Квиз за градови во Македонија', 'Practise important cities and simple geography facts.', 'Вежбај важни градови и едноставни географски факти.', 21, [
                $this->factQuestion('Which city is the capital of North Macedonia?', 'Кој град е главен град на Северна Македонија?', 'Skopje is the capital city.', 'Скопје е главен град.', [['Skopje', 'Скопје', true], ['Ohrid', 'Охрид', false], ['Bitola', 'Битола', false], ['Prilep', 'Прилеп', false]]),
                $this->factQuestion('Which city is famous for Lake Ohrid?', 'Кој град е познат по Охридското Езеро?', 'Ohrid is famous for its lake.', 'Охрид е познат по езерото.', [['Ohrid', 'Охрид', true], ['Veles', 'Велес', false], ['Kumanovo', 'Куманово', false], ['Kočani', 'Кочани', false]]),
                $this->factQuestion('Which city is in the south-west and near Pelister?', 'Кој град е на југозапад и близу Пелистер?', 'Bitola is near Pelister.', 'Битола е близу Пелистер.', [['Bitola', 'Битола', true], ['Tetovo', 'Тетово', false], ['Strumica', 'Струмица', false], ['Štip', 'Штип', false]]),
                $this->factQuestion('Which city is north-west of Skopje?', 'Кој град е северозападно од Скопје?', 'Tetovo is north-west of Skopje.', 'Тетово е северозападно од Скопје.', [['Tetovo', 'Тетово', true], ['Gevgelija', 'Гевгелија', false], ['Kavadarci', 'Кавадарци', false], ['Struga', 'Струга', false]]),
                $this->factQuestion('Which city is north-east of Skopje?', 'Кој град е североисточно од Скопје?', 'Kumanovo is north-east of Skopje.', 'Куманово е североисточно од Скопје.', [['Kumanovo', 'Куманово', true], ['Ohrid', 'Охрид', false], ['Bitola', 'Битола', false], ['Gostivar', 'Гостивар', false]]),
            ]),
            $this->factQuiz('geography', 'lake-ohrid-and-lake-prespa', 'lakes-and-mountains-quiz', 'Lakes and Mountains Quiz', 'Квиз за езера и планини', 'Review lakes, mountains, and nature places.', 'Повтори езера, планини и природни места.', 22, [
                $this->factQuestion('Which lake is famous near Ohrid?', 'Кое езеро е познато кај Охрид?', 'Lake Ohrid is famous near Ohrid.', 'Охридското Езеро е познато кај Охрид.', [['Lake Ohrid', 'Охридско Езеро', true], ['Lake Prespa', 'Преспанско Езеро', false], ['Mavrovo', 'Маврово', false], ['Matka Canyon', 'Кањон Матка', false]]),
                $this->factQuestion('Which lake is another important lake in the south-west?', 'Кое езеро е уште едно важно езеро на југозапад?', 'Lake Prespa is an important lake in the south-west.', 'Преспанското Езеро е важно езеро на југозапад.', [['Lake Prespa', 'Преспанско Езеро', true], ['Lake Ohrid', 'Охридско Езеро', false], ['Vardar', 'Вардар', false], ['Vodno', 'Водно', false]]),
                $this->factQuestion('Which mountain place is near Skopje?', 'Кое планинско место е близу Скопје?', 'Vodno is near Skopje.', 'Водно е близу Скопје.', [['Vodno', 'Водно', true], ['Pelister', 'Пелистер', false], ['Lake Prespa', 'Преспанско Езеро', false], ['Struga', 'Струга', false]]),
                $this->factQuestion('Which national park area is linked with mountain nature?', 'Кое место е поврзано со планинска природа?', 'Mavrovo is linked with mountain nature.', 'Маврово е поврзано со планинска природа.', [['Mavrovo', 'Маврово', true], ['Skopje', 'Скопје', false], ['Kavadarci', 'Кавадарци', false], ['Veles', 'Велес', false]]),
                $this->factQuestion('Which mountain is associated with Bitola?', 'Која планина е поврзана со Битола?', 'Pelister is associated with Bitola.', 'Пелистер е поврзан со Битола.', [['Pelister', 'Пелистер', true], ['Vodno', 'Водно', false], ['Matka', 'Матка', false], ['Tikvesh', 'Тиквеш', false]]),
            ]),

            $this->factQuiz('history-of-macedonia', 'macedonia-history-basics', 'macedonian-history-basics-quiz', 'Macedonian History Basics Quiz', 'Квиз за основи на македонската историја', 'Review beginner history ideas through places and memory.', 'Повтори почетни историски идеи преку места и сеќавања.', 21, [
                $this->factQuestion('What helps families preserve history?', 'Што им помага на семејствата да ја чуваат историјата?', 'Stories, photos, recipes, and songs can preserve history.', 'Приказни, фотографии, рецепти и песни можат да ја чуваат историјата.', [['Stories and photos', 'Приказни и фотографии', true], ['Forgetting names', 'Заборавање имиња', false], ['Hiding language', 'Криење јазик', false], ['Avoiding museums', 'Избегнување музеи', false]]),
                $this->factQuestion('Which city is the capital today?', 'Кој град е главен град денес?', 'Skopje is the capital city.', 'Скопје е главен град.', [['Skopje', 'Скопје', true], ['Ohrid', 'Охрид', false], ['Bitola', 'Битола', false], ['Struga', 'Струга', false]]),
                $this->factQuestion('Which place is known for lake and learning traditions?', 'Кое место е познато по езеро и традиции на учење?', 'Ohrid is known for lake and learning traditions.', 'Охрид е познат по езеро и традиции на учење.', [['Ohrid', 'Охрид', true], ['Kumanovo', 'Куманово', false], ['Kavadarci', 'Кавадарци', false], ['Gevgelija', 'Гевгелија', false]]),
                $this->factQuestion('History is only dates. Is this true?', 'Историјата е само датуми. Дали е тоа точно?', 'History also includes stories, places, and family memory.', 'Историјата вклучува и приказни, места и семејно сеќавање.', [['No', 'Не', true], ['Yes', 'Да', false], ['Only sometimes', 'Само понекогаш', false], ['Only in books', 'Само во книги', false]]),
                $this->factQuestion('What is a respectful way to learn history?', 'Кој е внимателен начин да се учи историја?', 'Ask questions and learn from places, people, and sources.', 'Поставувај прашања и учи од места, луѓе и извори.', [['Ask careful questions', 'Поставувај внимателни прашања', true], ['Argue first', 'Прво расправај се', false], ['Ignore family stories', 'Игнорирај семејни приказни', false], ['Use only one word', 'Користи само еден збор', false]]),
            ]),
            $this->factQuiz('history-of-macedonia', 'ohrid-as-a-cultural-centre', 'ohrid-and-skopje-quiz', 'Ohrid and Skopje Quiz', 'Квиз за Охрид и Скопје', 'Compare two important places in beginner-friendly ways.', 'Спореди две важни места на почетнички начин.', 22, [
                $this->factQuestion('Which city is known for Lake Ohrid?', 'Кој град е познат по Охридското Езеро?', 'Ohrid is known for Lake Ohrid.', 'Охрид е познат по Охридското Езеро.', [['Ohrid', 'Охрид', true], ['Skopje', 'Скопје', false], ['Veles', 'Велес', false], ['Štip', 'Штип', false]]),
                $this->factQuestion('Which city is the capital?', 'Кој град е главен град?', 'Skopje is the capital city.', 'Скопје е главен град.', [['Skopje', 'Скопје', true], ['Ohrid', 'Охрид', false], ['Prilep', 'Прилеп', false], ['Struga', 'Струга', false]]),
                $this->factQuestion('Which place is connected with learning traditions?', 'Кое место е поврзано со традиции на учење?', 'Ohrid is connected with learning traditions.', 'Охрид е поврзан со традиции на учење.', [['Ohrid', 'Охрид', true], ['Kavadarci', 'Кавадарци', false], ['Gevgelija', 'Гевгелија', false], ['Kočani', 'Кочани', false]]),
                $this->factQuestion('What can a capital city include?', 'Што може да има главен град?', 'A capital can include government, schools, transport, and culture.', 'Главен град може да има институции, училишта, транспорт и култура.', [['Government and culture', 'Институции и култура', true], ['Only forests', 'Само шуми', false], ['Only beaches', 'Само плажи', false], ['No people', 'Без луѓе', false]]),
                $this->factQuestion('Why do learners compare cities?', 'Зошто учениците споредуваат градови?', 'Comparing cities helps remember different roles and stories.', 'Споредување градови помага да се паметат различни улоги и приказни.', [['To remember roles and stories', 'За да паметат улоги и приказни', true], ['To forget maps', 'За да заборават мапи', false], ['To avoid culture', 'За да избегнат култура', false], ['To hide language', 'За да скријат јазик', false]]),
            ]),
            $this->factQuiz('history-of-macedonia', 'how-families-preserve-history', 'family-history-and-traditions-quiz', 'Family History and Traditions Quiz', 'Квиз за семејна историја и традиции', 'Think about how families remember and share heritage.', 'Размисли како семејствата паметат и споделуваат наследство.', 23, [
                $this->factQuestion('Which object can carry family memory?', 'Кој предмет може да носи семејно сеќавање?', 'A photograph can carry family memory.', 'Фотографија може да носи семејно сеќавање.', [['A photograph', 'Фотографија', true], ['A blank wall only', 'Само празен ѕид', false], ['A hidden book', 'Скриена книга', false], ['A lost name', 'Изгубено име', false]]),
                $this->factQuestion('What can recipes preserve?', 'Што можат да зачуваат рецептите?', 'Recipes can preserve family food memories.', 'Рецептите можат да зачуваат семејни сеќавања за храна.', [['Food memories', 'Сеќавања за храна', true], ['Only numbers', 'Само броеви', false], ['Only maps', 'Само мапи', false], ['Nothing', 'Ништо', false]]),
                $this->factQuestion('What helps children learn family history?', 'Што им помага на децата да учат семејна историја?', 'Stories from older relatives can help.', 'Приказни од постари роднини можат да помогнат.', [['Stories from relatives', 'Приказни од роднини', true], ['Never asking', 'Никогаш да не прашуваат', false], ['Forgetting songs', 'Заборавање песни', false], ['Avoiding language', 'Избегнување јазик', false]]),
                $this->factQuestion('Which is a tradition?', 'Што е традиција?', 'A repeated family or community custom can be a tradition.', 'Повторен семеен или заеднички обичај може да биде традиција.', [['A shared custom', 'Заеднички обичај', true], ['A broken pencil', 'Скршен молив', false], ['A random number', 'Случаен број', false], ['No memory', 'Без сеќавање', false]]),
                $this->factQuestion('Where can Macedonian community life happen in Australia?', 'Каде може да се случува македонски заеднички живот во Австралија?', 'It can happen at clubs, schools, churches, and events.', 'Може да се случува во клубови, училишта, цркви и настани.', [['Clubs and events', 'Клубови и настани', true], ['Nowhere', 'Никаде', false], ['Only online games', 'Само онлајн игри', false], ['Only airports', 'Само аеродроми', false]]),
            ]),

            $this->factQuiz('culture-and-traditions', 'holidays-and-family-gatherings', 'traditions-and-celebrations-quiz', 'Traditions and Celebrations Quiz', 'Квиз за традиции и прослави', 'Review family gatherings, holidays, and shared customs.', 'Повтори семејни собири, празници и заеднички обичаи.', 21, [
                $this->factQuestion('What is often shared at celebrations?', 'Што често се споделува на прослави?', 'Food is often shared at celebrations.', 'Храна често се споделува на прослави.', [['Food', 'Храна', true], ['Silence only', 'Само тишина', false], ['No guests', 'Без гости', false], ['Empty tables', 'Празни маси', false]]),
                $this->factQuestion('What does oro usually bring together?', 'Што обично зближува орото?', 'Oro brings people together through dance.', 'Оро ги зближува луѓето преку танц.', [['People', 'Луѓе', true], ['Only books', 'Само книги', false], ['Only maps', 'Само мапи', false], ['No one', 'Никого', false]]),
                $this->factQuestion('What is a holiday gathering?', 'Што е празничен собир?', 'It is a time when family or community gathers.', 'Тоа е време кога се собира семејство или заедница.', [['Family or community gathering', 'Собир на семејство или заедница', true], ['A closed schoolbag', 'Затворена торба', false], ['A silent test', 'Тивок тест', false], ['Only one shoe', 'Само еден чевел', false]]),
                $this->factQuestion('Why do traditions matter?', 'Зошто се важни традициите?', 'They help people remember and share culture.', 'Тие им помагаат на луѓето да паметат и споделуваат култура.', [['They share culture', 'Споделуваат култура', true], ['They erase memory', 'Бришат сеќавање', false], ['They stop family time', 'Го запираат семејното време', false], ['They hide language', 'Го кријат јазикот', false]]),
                $this->factQuestion('What can children learn at community events?', 'Што можат да научат децата на настани во заедницата?', 'They can hear language, music, and stories.', 'Можат да слушнат јазик, музика и приказни.', [['Language, music, and stories', 'Јазик, музика и приказни', true], ['Nothing useful', 'Ништо корисно', false], ['Only silence', 'Само тишина', false], ['Only traffic rules', 'Само сообраќајни правила', false]]),
            ]),
            $this->factQuiz('culture-and-traditions', 'macedonian-weddings', 'oro-and-weddings-quiz', 'Oro and Weddings Quiz', 'Квиз за оро и свадби', 'Practise simple words connected with weddings and dancing.', 'Вежбај едноставни зборови поврзани со свадби и оро.', 22, [
                $this->factQuestion('What is oro?', 'Што е оро?', 'Oro is a traditional dance.', 'Оро е традиционален танц.', [['A traditional dance', 'Традиционален танц', true], ['A soup', 'Супа', false], ['A lake', 'Езеро', false], ['A school desk', 'Училишна клупа', false]]),
                $this->factQuestion('What is свадба?', 'Што е свадба?', 'Свадба means wedding.', 'Свадба значи wedding.', [['Wedding', 'Свадба', true], ['Market', 'Пазар', false], ['Mountain', 'Планина', false], ['Pencil', 'Молив', false]]),
                $this->factQuestion('Who are гости?', 'Кои се гости?', 'Гости means guests.', 'Гости значи guests.', [['Guests', 'Гости', true], ['Books', 'Книги', false], ['Lakes', 'Езера', false], ['Letters', 'Букви', false]]),
                $this->factQuestion('What is often heard at weddings?', 'Што често се слуша на свадби?', 'Music is often heard at weddings.', 'Музика често се слуша на свадби.', [['Music', 'Музика', true], ['Thunder only', 'Само гром', false], ['Silence only', 'Само тишина', false], ['No sound', 'Без звук', false]]),
                $this->factQuestion('What can dancing show?', 'Што може да покаже оро?', 'Dancing can show joy and connection.', 'Орото може да покаже радост и поврзаност.', [['Joy and connection', 'Радост и поврзаност', true], ['No friendship', 'Без пријателство', false], ['Only homework', 'Само домашна работа', false], ['No celebration', 'Без прослава', false]]),
            ]),
            $this->factQuiz('culture-and-traditions', 'macedonian-community-life-in-australia', 'macedonian-community-quiz', 'Macedonian Community Quiz', 'Квиз за македонска заедница', 'Review community words for Australian Macedonian family life.', 'Повтори зборови за македонски семеен живот во Австралија.', 23, [
                $this->factQuestion('What does заедница mean?', 'Што значи заедница?', 'Заедница means community.', 'Заедница значи community.', [['Community', 'Заедница', true], ['Window', 'Прозорец', false], ['Lake', 'Езеро', false], ['Pencil', 'Молив', false]]),
                $this->factQuestion('Where can children practise Macedonian?', 'Каде можат децата да вежбаат македонски?', 'They can practise at home, school, clubs, and events.', 'Можат да вежбаат дома, во училиште, клубови и настани.', [['Home, school, clubs, and events', 'Дома, училиште, клубови и настани', true], ['Nowhere', 'Никаде', false], ['Only in silence', 'Само во тишина', false], ['Only while sleeping', 'Само додека спијат', false]]),
                $this->factQuestion('What can community events include?', 'Што може да има настан во заедницата?', 'Events can include language, food, music, and stories.', 'Настаните можат да имаат јазик, храна, музика и приказни.', [['Language, food, music, and stories', 'Јазик, храна, музика и приказни', true], ['Only empty chairs', 'Само празни столици', false], ['Only exams', 'Само испити', false], ['Only traffic', 'Само сообраќај', false]]),
                $this->factQuestion('Why is speaking Macedonian at home helpful?', 'Зошто е корисно да се зборува македонски дома?', 'It helps families keep language alive.', 'Тоа им помага на семејствата да го чуваат јазикот.', [['It keeps language alive', 'Го чува јазикот', true], ['It removes culture', 'Ја брише културата', false], ['It stops learning', 'Го запира учењето', false], ['It hides family stories', 'Крие семејни приказни', false]]),
                $this->factQuestion('What is one respectful community value?', 'Која е една вредност во заедницата?', 'Helping and welcoming others is a respectful value.', 'Помагање и добредојде се вредности со почит.', [['Helping and welcoming', 'Помагање и добредојде', true], ['Ignoring everyone', 'Игнорирање на сите', false], ['Never sharing', 'Никогаш споделување', false], ['Forgetting names', 'Заборавање имиња', false]]),
            ]),

            $this->factQuiz('food-and-music', 'shopska-salad-and-shared-meals', 'food-basics-quiz', 'Food Basics Quiz', 'Квиз за основи на храна', 'Review simple Macedonian food words.', 'Повтори едноставни македонски зборови за храна.', 21, [
                $this->factQuestion('What is леб?', 'Што е леб?', 'Леб means bread.', 'Леб значи bread.', [['Bread', 'Леб', true], ['Water', 'Вода', false], ['Music', 'Музика', false], ['Dance', 'Оро', false]]),
                $this->factQuestion('What is сирење?', 'Што е сирење?', 'Сирење means cheese.', 'Сирење значи cheese.', [['Cheese', 'Сирење', true], ['Tomato', 'Домат', false], ['Pepper', 'Пиперка', false], ['Bean', 'Грав', false]]),
                $this->factQuestion('Which food uses tomatoes, cucumbers, and cheese?', 'Која храна користи домати, краставици и сирење?', 'Shopska salad often uses these ingredients.', 'Шопска салата често ги користи овие состојки.', [['Shopska salad', 'Шопска салата', true], ['Ajvar', 'Ајвар', false], ['Tavče gravče', 'Тавче гравче', false], ['Bread', 'Леб', false]]),
                $this->factQuestion('What is грав?', 'Што е грав?', 'Грав means beans.', 'Грав значи beans.', [['Beans', 'Грав', true], ['Song', 'Песна', false], ['Lake', 'Езеро', false], ['Guest', 'Гостин', false]]),
                $this->factQuestion('Why are shared meals important?', 'Зошто се важни заедничките оброци?', 'They help families talk and connect.', 'Им помагаат на семејствата да разговараат и да се поврзат.', [['They connect families', 'Ги поврзуваат семејствата', true], ['They stop talking', 'Го запираат разговорот', false], ['They hide culture', 'Ја кријат културата', false], ['They erase recipes', 'Ги бришат рецептите', false]]),
            ]),
            $this->factQuiz('food-and-music', 'tavce-gravce', 'ajvar-and-tavce-gravce-quiz', 'Ajvar and Tavče Gravče Quiz', 'Квиз за ајвар и тавче гравче', 'Practise favourite food names and ingredients.', 'Вежбај имиња и состојки за омилена храна.', 22, [
                $this->factQuestion('What is tavče gravče?', 'Што е тавче гравче?', 'Tavče gravče is a baked bean dish.', 'Тавче гравче е јадење со печен грав.', [['A baked bean dish', 'Јадење со печен грав', true], ['A lake', 'Езеро', false], ['A dance', 'Оро', false], ['A city', 'Град', false]]),
                $this->factQuestion('What is ajvar commonly made from?', 'Од што најчесто се прави ајвар?', 'Ajvar is commonly made from roasted peppers.', 'Ајвар најчесто се прави од печени пиперки.', [['Roasted peppers', 'Печени пиперки', true], ['Chocolate', 'Чоколадо', false], ['Rice', 'Ориз', false], ['Apples only', 'Само јаболка', false]]),
                $this->factQuestion('What is пиперка?', 'Што е пиперка?', 'Пиперка means pepper.', 'Пиперка значи pepper.', [['Pepper', 'Пиперка', true], ['Bean', 'Грав', false], ['Bread', 'Леб', false], ['Cheese', 'Сирење', false]]),
                $this->factQuestion('How is tavče gravče usually served?', 'Како обично се служи тавче гравче?', 'It is usually served warm.', 'Обично се служи топло.', [['Warm', 'Топло', true], ['Frozen', 'Замрзнато', false], ['As a drink', 'Како пијалак', false], ['Only raw', 'Само сурово', false]]),
                $this->factQuestion('What can ajvar be eaten with?', 'Со што може да се јаде ајвар?', 'Ajvar can be eaten with bread or meals.', 'Ајвар може да се јаде со леб или оброци.', [['Bread or meals', 'Леб или оброци', true], ['Only ice', 'Само мраз', false], ['Only paper', 'Само хартија', false], ['Only water', 'Само вода', false]]),
            ]),
            $this->factQuiz('food-and-music', 'folk-music-and-celebrations', 'music-and-celebrations-quiz', 'Music and Celebrations Quiz', 'Квиз за музика и прослави', 'Review music, rhythm, dancing, and family celebration words.', 'Повтори зборови за музика, ритам, оро и семејни прослави.', 23, [
                $this->factQuestion('What does песна mean?', 'Што значи песна?', 'Песна means song.', 'Песна значи song.', [['Song', 'Песна', true], ['Bread', 'Леб', false], ['Lake', 'Езеро', false], ['Pencil', 'Молив', false]]),
                $this->factQuestion('What instrument may be heard at celebrations?', 'Кој инструмент може да се слушне на прослави?', 'An accordion may be heard at celebrations.', 'Хармоника може да се слушне на прослави.', [['Accordion', 'Хармоника', true], ['Only whistle', 'Само свирче', false], ['Only bell', 'Само ѕвонче', false], ['Only drumstick', 'Само палка', false]]),
                $this->factQuestion('What does rhythm help with?', 'Со што помага ритамот?', 'Rhythm can help people dance together.', 'Ритамот може да им помогне на луѓето да играат заедно.', [['Dancing together', 'Оро заедно', true], ['Stopping music', 'Запирање музика', false], ['Forgetting songs', 'Заборавање песни', false], ['Hiding celebrations', 'Криење прослави', false]]),
                $this->factQuestion('Where is folk music often heard?', 'Каде често се слуша народна музика?', 'It is often heard at weddings, festivals, and community events.', 'Често се слуша на свадби, фестивали и настани во заедницата.', [['Weddings and events', 'Свадби и настани', true], ['Only libraries', 'Само библиотеки', false], ['Only empty rooms', 'Само празни соби', false], ['Only tests', 'Само тестови', false]]),
                $this->factQuestion('Why can music carry memory?', 'Зошто музиката може да носи сеќавање?', 'Songs can remind families of people, places, and celebrations.', 'Песните можат да потсетат на луѓе, места и прослави.', [['It reminds families', 'Ги потсетува семејствата', true], ['It erases stories', 'Брише приказни', false], ['It stops dancing', 'Го запира орото', false], ['It hides language', 'Го крие јазикот', false]]),
            ]),
        ];
    }

    private function mapQuestions(): array
    {
        return [
            $this->mapQuestion('skopje', 'Skopje', 'Скопје', 'city', 'Skopje is the capital city.', 'Скопје е главен град.', [['Skopje', 'Скопје', true], ['Ohrid', 'Охрид', false], ['Bitola', 'Битола', false], ['Tetovo', 'Тетово', false]]),
            $this->mapQuestion('ohrid', 'Ohrid', 'Охрид', 'city', 'Ohrid is known for its lake and old town.', 'Охрид е познат по езерото и стариот град.', [['Ohrid', 'Охрид', true], ['Prilep', 'Прилеп', false], ['Kumanovo', 'Куманово', false], ['Strumica', 'Струмица', false]]),
            $this->mapQuestion('bitola', 'Bitola', 'Битола', 'city', 'Bitola is an important city in the south-west.', 'Битола е важен град на југозапад.', [['Bitola', 'Битола', true], ['Veles', 'Велес', false], ['Gostivar', 'Гостивар', false], ['Štip', 'Штип', false]]),
            $this->mapQuestion('prilep', 'Prilep', 'Прилеп', 'city', 'Prilep is in the central-southern part of the country.', 'Прилеп е во централно-јужниот дел.', [['Prilep', 'Прилеп', true], ['Skopje', 'Скопје', false], ['Struga', 'Струга', false], ['Kavadarci', 'Кавадарци', false]]),
            $this->mapQuestion('tetovo', 'Tetovo', 'Тетово', 'city', 'Tetovo is in the north-west.', 'Тетово е на северозапад.', [['Tetovo', 'Тетово', true], ['Gevgelija', 'Гевгелија', false], ['Kočani', 'Кочани', false], ['Prilep', 'Прилеп', false]]),
            $this->mapQuestion('kumanovo', 'Kumanovo', 'Куманово', 'city', 'Kumanovo is north-east of Skopje.', 'Куманово е североисточно од Скопје.', [['Kumanovo', 'Куманово', true], ['Bitola', 'Битола', false], ['Gevgelija', 'Гевгелија', false], ['Gostivar', 'Гостивар', false]]),
            $this->mapQuestion('strumica', 'Strumica', 'Струмица', 'city', 'Strumica is in the south-east.', 'Струмица е на југоисток.', [['Strumica', 'Струмица', true], ['Ohrid', 'Охрид', false], ['Kičevo', 'Кичево', false], ['Tetovo', 'Тетово', false]]),
            $this->mapQuestion('veles', 'Veles', 'Велес', 'city', 'Veles sits near the central Vardar route.', 'Велес е близу централниот вардарски правец.', [['Veles', 'Велес', true], ['Struga', 'Струга', false], ['Ohrid', 'Охрид', false], ['Kočani', 'Кочани', false]]),
            $this->mapQuestion('stip', 'Štip', 'Штип', 'city', 'Štip is an eastern city.', 'Штип е град на исток.', [['Štip', 'Штип', true], ['Bitola', 'Битола', false], ['Tetovo', 'Тетово', false], ['Gevgelija', 'Гевгелија', false]]),
            $this->mapQuestion('gostivar', 'Gostivar', 'Гостивар', 'city', 'Gostivar is in the western part of the country.', 'Гостивар е во западниот дел.', [['Gostivar', 'Гостивар', true], ['Kavadarci', 'Кавадарци', false], ['Strumica', 'Струмица', false], ['Veles', 'Велес', false]]),
            $this->mapQuestion('struga', 'Struga', 'Струга', 'city', 'Struga is near Lake Ohrid.', 'Струга е близу Охридското Езеро.', [['Struga', 'Струга', true], ['Skopje', 'Скопје', false], ['Kočani', 'Кочани', false], ['Prilep', 'Прилеп', false]]),
            $this->mapQuestion('kicevo', 'Kičevo', 'Кичево', 'city', 'Kičevo is in the western-central area.', 'Кичево е во западно-централниот дел.', [['Kičevo', 'Кичево', true], ['Kumanovo', 'Куманово', false], ['Gevgelija', 'Гевгелија', false], ['Bitola', 'Битола', false]]),
            $this->mapQuestion('kavadarci', 'Kavadarci', 'Кавадарци', 'city', 'Kavadarci is associated with the Tikveš region.', 'Кавадарци е поврзан со Тиквешкиот регион.', [['Kavadarci', 'Кавадарци', true], ['Gostivar', 'Гостивар', false], ['Tetovo', 'Тетово', false], ['Struga', 'Струга', false]]),
            $this->mapQuestion('gevgelija', 'Gevgelija', 'Гевгелија', 'city', 'Gevgelija is in the far south.', 'Гевгелија е на крајниот југ.', [['Gevgelija', 'Гевгелија', true], ['Ohrid', 'Охрид', false], ['Veles', 'Велес', false], ['Kumanovo', 'Куманово', false]]),
            $this->mapQuestion('kocani', 'Kočani', 'Кочани', 'city', 'Kočani is in the east.', 'Кочани е на исток.', [['Kočani', 'Кочани', true], ['Bitola', 'Битола', false], ['Prilep', 'Прилеп', false], ['Struga', 'Струга', false]]),
            $this->mapQuestion('lake-ohrid', 'Lake Ohrid', 'Охридско Езеро', 'lake', 'Lake Ohrid is one of the best-known lakes in the region.', 'Охридското Езеро е едно од најпознатите езера во регионот.', [['Lake Ohrid', 'Охридско Езеро', true], ['Lake Prespa', 'Преспанско Езеро', false], ['Matka Canyon', 'Кањон Матка', false], ['Mavrovo', 'Маврово', false]]),
            $this->mapQuestion('lake-prespa', 'Lake Prespa', 'Преспанско Езеро', 'lake', 'Lake Prespa is another important lake in the south-west.', 'Преспанското Езеро е уште едно важно езеро на југозапад.', [['Lake Prespa', 'Преспанско Езеро', true], ['Lake Ohrid', 'Охридско Езеро', false], ['Vodno', 'Водно', false], ['Pelister', 'Пелистер', false]]),
            $this->mapQuestion('matka-canyon', 'Matka Canyon', 'Кањон Матка', 'landmark', 'Matka Canyon is near Skopje.', 'Кањонот Матка е близу Скопје.', [['Matka Canyon', 'Кањон Матка', true], ['Lake Ohrid', 'Охридско Езеро', false], ['Pelister', 'Пелистер', false], ['Gevgelija', 'Гевгелија', false]]),
            $this->mapQuestion('vodno', 'Vodno', 'Водно', 'landmark', 'Vodno is a mountain area near Skopje.', 'Водно е планинско место близу Скопје.', [['Vodno', 'Водно', true], ['Mavrovo', 'Маврово', false], ['Lake Prespa', 'Преспанско Езеро', false], ['Štip', 'Штип', false]]),
            $this->mapQuestion('mavrovo', 'Mavrovo', 'Маврово', 'landmark', 'Mavrovo is known for mountain nature.', 'Маврово е познато по планинска природа.', [['Mavrovo', 'Маврово', true], ['Vodno', 'Водно', false], ['Kavadarci', 'Кавадарци', false], ['Kumanovo', 'Куманово', false]]),
            $this->mapQuestion('pelister', 'Pelister', 'Пелистер', 'landmark', 'Pelister is associated with mountain nature near Bitola.', 'Пелистер е поврзан со планинска природа кај Битола.', [['Pelister', 'Пелистер', true], ['Matka Canyon', 'Кањон Матка', false], ['Lake Ohrid', 'Охридско Езеро', false], ['Tetovo', 'Тетово', false]]),
        ];
    }

    private function lesson(string $categorySlug, string $slug, ?string $quizSlug, string $titleEn, string $titleMk, string $summaryEn, string $summaryMk, int $estimatedMinutes, array $vocabEn, array $vocabMk, array $examplesEn, array $examplesMk, string $practiceEn, string $practiceMk): array
    {
        return compact('categorySlug', 'slug', 'quizSlug', 'titleEn', 'titleMk', 'summaryEn', 'summaryMk', 'estimatedMinutes', 'vocabEn', 'vocabMk', 'examplesEn', 'examplesMk', 'practiceEn', 'practiceMk') + [
            'category_slug' => $categorySlug,
            'quiz_slug' => $quizSlug,
            'title_en' => $titleEn,
            'title_mk' => $titleMk,
            'summary_en' => $summaryEn,
            'summary_mk' => $summaryMk,
            'estimated_minutes' => $estimatedMinutes,
            'vocab_en' => $vocabEn,
            'vocab_mk' => $vocabMk,
            'examples_en' => $examplesEn,
            'examples_mk' => $examplesMk,
            'practice_en' => $practiceEn,
            'practice_mk' => $practiceMk,
        ];
    }

    private function lessonContentEn(array $lesson): string
    {
        $examples = array_merge($lesson['examples_en'], $this->extraExamplesEn($lesson));
        $facts = array_merge($lesson['vocab_en'], $this->extraFactsEn($lesson));
        $practice = array_merge([$lesson['practice_en']], $this->practicePromptsEn($lesson));

        return implode("\n\n", [
            "Introduction:\n{$lesson['summary_en']} {$this->introBridgeEn($lesson)}",
            "What you will learn:\n- ".implode("\n- ", $this->learningGoalsEn($lesson)),
            "Main explanation:\n{$this->categoryContextEn($lesson['category_slug'])}\n{$this->topicContextEn($lesson)}\n{$this->studyContextEn($lesson)}",
            "Examples:\n- ".implode("\n- ", $examples),
            "Key vocabulary / key facts:\n- ".implode("\n- ", $facts),
            "Practice:\n- ".implode("\n- ", $practice),
            "Remember:\n{$this->rememberEn($lesson)}",
        ]);
    }

    private function lessonContentMk(array $lesson): string
    {
        $examples = array_merge($lesson['examples_mk'], $this->extraExamplesMk($lesson));
        $facts = array_merge($lesson['vocab_mk'], $this->extraFactsMk($lesson));
        $practice = array_merge([$lesson['practice_mk']], $this->practicePromptsMk($lesson));

        return implode("\n\n", [
            "Вовед:\n{$lesson['summary_mk']} {$this->introBridgeMk($lesson)}",
            "Што ќе научиш:\n- ".implode("\n- ", $this->learningGoalsMk($lesson)),
            "Објаснување:\n{$this->categoryContextMk($lesson['category_slug'])}\n{$this->topicContextMk($lesson)}\n{$this->studyContextMk($lesson)}",
            "Примери:\n- ".implode("\n- ", $examples),
            "Клучни зборови / клучни факти:\n- ".implode("\n- ", $facts),
            "Вежбање:\n- ".implode("\n- ", $practice),
            "Запомни:\n{$this->rememberMk($lesson)}",
        ]);
    }

    private function introBridgeEn(array $lesson): string
    {
        return "This mini lesson is written for beginners, family learners, and diaspora learners who may hear Macedonian at home but want more confidence reading, speaking, and remembering it. Work through it slowly before taking the related quiz.";
    }

    private function introBridgeMk(array $lesson): string
    {
        return "Оваа мала лекција е за почетници, деца, родители и сите што сакаат посигурно да читаат, зборуваат и паметат македонски. Читај полека пред квизот.";
    }

    private function learningGoalsEn(array $lesson): array
    {
        return [
            "Understand the main idea of {$lesson['title_en']} in beginner-friendly language.",
            'Recognise useful words, names, places, or facts connected to the topic.',
            'Read examples in English and Macedonian and notice how the words are used.',
            'Practise with simple family, school, map, or community activities.',
            'Prepare for the related quiz without needing outside textbook material.',
        ];
    }

    private function learningGoalsMk(array $lesson): array
    {
        return [
            "Да ја разбереш темата: {$lesson['title_mk']}.",
            'Да препознаеш важни зборови, места или факти.',
            'Да читаш едноставни примери на македонски и англиски.',
            'Да вежбаш со кратки домашни или семејни задачи.',
        ];
    }

    private function categoryContextEn(string $categorySlug): string
    {
        return [
            'macedonian-language' => 'Language learning begins with useful words that can be spoken today, not only memorised for a test. Say each Macedonian word aloud, then place it inside a small real situation: greeting a grandparent, counting objects on a table, naming a family member, reading a calendar, or asking for a pencil. Macedonian word endings can change in full sentences, so the goal here is not perfect grammar on day one. The goal is recognition, confidence, and a small speaking habit that grows each time you use the words.',
            'macedonian-alphabet' => 'Reading Macedonian starts with noticing shapes and sounds. The Cyrillic letters may feel new at first, but they become familiar when you connect them to names, family words, signs, and places. Do not rush through the alphabet as a list. Look at one word, find the first letter, say the sound, then blend slowly. This lesson uses short words so learners can hear how letters build syllables and how syllables build words.',
            'geography' => 'Geography is more than memorising dots on a map. It helps learners connect cities, lakes, mountains, valleys, roads, food, weather, travel, and family stories. When a child hears that relatives are from a town, a village, a lake area, or a mountain region, map skills give that story a place. Use this lesson with a map if possible, and notice direction words such as north, south, east, west, near, far, lake, mountain, city, and region.',
            'history-of-macedonia' => 'History is best introduced with care, respect, and curiosity. For beginners, history is not only dates or arguments. It is also places, museums, old streets, songs, family photos, recipes, migration memories, and stories told at the kitchen table. This lesson avoids political disputes and focuses on safe learning habits: ask questions, compare memories kindly, notice places, and understand that families may tell stories in different ways.',
            'culture-and-traditions' => 'Culture is learned through repeated family moments: a song at a celebration, an oro dance at a wedding, a table full of food, a visit to relatives, or a community event in Australia. Traditions can vary by region and family, so this lesson uses respectful general language. The goal is to help learners name what they see, ask elders about meaning, and feel comfortable joining in without pretending every family does things exactly the same way.',
            'food-and-music' => 'Food and music are powerful memory tools because they involve taste, sound, rhythm, and family time. A dish can remind someone of a grandparent, a song can bring people to dance, and a shared meal can make Macedonian words easier to remember. This lesson introduces simple vocabulary and cultural ideas without turning recipes or songs into strict rules. Families may prepare dishes differently, and music traditions can vary from place to place.',
        ][$categorySlug] ?? 'This lesson builds beginner knowledge through simple explanation, examples, key words, and practice. Read each section slowly and connect it to something real in your family, school, or community life.';
    }

    private function categoryContextMk(string $categorySlug): string
    {
        return [
            'macedonian-language' => 'Јазикот се учи најлесно со зборови што можеш да ги користиш веднаш. Кажи ги зборовите на глас и поврзи ги со дом, семејство, училиште или разговор.',
            'macedonian-alphabet' => 'Читањето македонски почнува со букви и звуци. Гледај ја буквата, кажи го звукот и полека спојувај слогови во зборови.',
            'geography' => 'Географијата помага да ги поврземе градовите, езерата, планините, патувањата и семејните приказни со вистински места на мапа.',
            'history-of-macedonia' => 'Историјата се учи со почит преку места, приказни, фотографии, песни, рецепти и семејни сеќавања. Поставувај внимателни прашања.',
            'culture-and-traditions' => 'Културата се гледа во семејни собири, оро, музика, храна, празници и настани. Традициите можат да се разликуваат по семејство и регион.',
            'food-and-music' => 'Храната и музиката носат сеќавање. Јадење, песна или ритам можат да ги поврзат семејството, јазикот и прославите.',
        ][$categorySlug] ?? 'Оваа лекција гради почетно знаење со објаснување, примери, зборови и вежбање.';
    }

    private function topicContextEn(array $lesson): string
    {
        return [
            'basic-macedonian-greetings' => 'Greetings are often the first bridge into Macedonian conversation. Use здраво for hello in friendly everyday situations. Use добро утро in the morning, добар ден during the day, добра вечер in the evening, and добра ноќ when someone is going to sleep. Благодарам means thank you, and те молам can mean please or you are welcome depending on the situation. Како си? asks how are you, and добро сум answers I am well. Пријатно and довидување are useful goodbye words. A short dialogue can sound like this: A: Здраво, како си? B: Добро сум, благодарам. А ти?',
            'numbers-1-20' => 'Numbers help with games, age, school, shopping, family stories, and counting objects at home. Learn them in order first: еден, два, три, четири, пет, шест, седум, осум, девет, десет, единаесет, дванаесет, тринаесет, четиринаесет, петнаесет, шеснаесет, седумнаесет, осумнаесет, деветнаесет, дваесет. Then place them in sentences: Имам десет години. Имам две книги. Еден, два, три can begin a counting game. Count spoons, shoes, books, steps, or family members.',
            'family-words' => 'Family words matter because many learners first hear Macedonian through parents, grandparents, aunties, uncles, cousins, and family friends. Start with мајка, татко, брат, сестра, баба, and дедо. Then add тетка, чичко or вујко, братучед, and братучетка. Simple sentences are powerful: Ова е мојата мајка. Имам една сестра. Мојот брат е дома. For diaspora families, these words carry identity because they help children speak about the people who keep language and memory alive.',
            'days-months-and-time' => 'Time words help learners talk about school, sport, birthdays, visits, and family plans. The days are понеделник, вторник, среда, четврток, петок, сабота, недела. Useful month names include јануари, февруари, март, април, мај, јуни, јули, август, септември, октомври, ноември, декември. Денес means today, утре means tomorrow, and вчера means yesterday. You can also use утро, ден, вечер, and ноќ for morning, day, evening, and night. Try: Денес е понеделник. Утре одам на училиште.',
            'common-classroom-words' => 'Classroom words make Macedonian useful during study time. Learn книга, молив, тетратка, училиште, наставник, ученик, маса, стол, чита, and пишува. These words can be practised at a desk, kitchen table, or community language school. Say Имам книга when holding a book, Ова е молив when pointing to a pencil, and Јас пишувам when writing. Labelling real objects is a strong beginner strategy because learners see, touch, say, and remember the word at the same time.',
            'introduction-to-macedonian-cyrillic-alphabet' => 'Modern Macedonian uses a Cyrillic alphabet with 31 letters. Learning the letters helps you read family names, place names, signs, song titles, recipes, and simple messages. Begin by recognising common letters such as А, Б, В, Г, Д, Е, Ж, З, И, Ј, К, Л, М, Н, О, П, Р, С, and Т. Try familiar words: Македонија, мајка, татко, добро, книга. Do not worry about writing every letter perfectly today. First, train your eyes to notice the letter shapes and your mouth to say the sounds.',
            'vowels-and-consonants' => 'Macedonian vowels are А, Е, И, О, and У. A vowel is an open sound that helps a syllable speak clearly. Consonants such as М, Т, Д, Б, К, Н, and Р work with vowels to build syllables. Practise slowly: ма-ма, та-то, до-бро. When learners can hear vowels, reading becomes less mysterious because words are no longer long strings of letters. They become small sound groups. Clap once for each syllable and say the word again.',
            'letters-that-look-familiar' => 'Some Cyrillic letters look familiar to learners who know the Latin alphabet. А, Е, К, М, О, and Т look similar and can feel friendly at the start. But other letters can trick the eye. Р looks like English P but sounds like r. В looks like B but sounds closer to v. С looks like C but sounds like s. The lesson is simple: use familiar shapes as helpers, but always read with Macedonian Cyrillic sounds.',
            'reading-simple-macedonian-words' => 'Reading begins with short words. Start with дом, мама, тато, вода, книга, ден, брат, and сестра. Point to the first letter, say the sound, then blend the next sound. Do not guess from the shape of the whole word. Read мама as ма-ма, вода as во-да, and добро as до-бро. If a word feels hard, cover part of it with your finger and read one syllable at a time. Small wins build reading confidence.',
            'writing-your-first-macedonian-words' => 'Writing helps the hand remember what the eyes and ears are learning. Begin by tracing letters, then copying short words, then writing from memory. Use family words such as мама, тато, баба, дедо, брат, and сестра, or write your own name if you know its Macedonian spelling. Neat handwriting is not about speed. Sit comfortably, leave space between letters, say the sound as you write, and check whether each letter faces the correct direction.',
            'macedonian-geography-basics' => 'Macedonia has cities, lakes, mountains, valleys, roads, and villages that connect to culture and family stories. Skopje is the capital. Ohrid is a lake city. Bitola, Tetovo, and Prilep are important cities with different local identities. Lake Ohrid and Lake Prespa are major natural places. Mountains and valleys shape travel, weather, food, and where people live. This lesson also prepares learners for the Map Challenge, where a highlighted point on the map becomes a clue.',
            'skopje-ohrid-and-bitola' => 'Skopje, Ohrid, and Bitola are useful anchor cities for beginners. Skopje is the capital and a busy centre for transport, schools, museums, markets, and modern city life. Ohrid is known for Lake Ohrid, older architecture, cultural memory, and the feeling of a lake city. Bitola is an important historic city in the south-west, near Pelister, with streets and places that many families remember. Learn one simple fact about each city first, then add detail later.',
            'lake-ohrid-and-lake-prespa' => 'Lakes are important natural places because they shape climate, travel, tourism, nature, family trips, and memory. Lake Ohrid is closely connected with the city of Ohrid and is one of the best-known lakes in the region. Lake Prespa is also in the south-west and is important for nature and quiet landscapes. When learning lakes, notice the words lake, shore, water, town, mountain, and trip. Many families remember lakes through summer visits, photos, food, and stories.',
            'mountains-and-national-parks' => 'Mountains are common in Macedonia, so learners should know a few names and simple terms. Pelister is associated with Bitola and mountain nature. Mavrovo is known for national park landscapes, snow, forests, and outdoor trips. Shar Mountain is a major mountain area in the north-west. Vodno is near Skopje and is often connected with views over the city. Mountain vocabulary includes планина, врв, долина, парк, снег, патека, and поглед.',
            'macedonian-regions-and-travel' => 'Regions and travel can be taught without making the topic complicated. Learners can describe places by city, village, lake, mountain, road, north, south, east, west, near, and far. People often describe where they are from by naming a town, village, or area connected to family. Roads connect cities, valleys, lakes, and mountains. A simple map skill is to find Skopje first, then describe another place by direction: Ohrid is to the south-west, and Kumanovo is north-east of Skopje.',
            'macedonia-history-basics' => 'Learning Macedonian history begins with respectful curiosity. History can be found in stories, places, museums, traditions, language, songs, family memory, and old photographs. It matters because it helps learners understand identity, belonging, and why families value certain places or customs. Beginners do not need to solve every difficult question. They can start by asking: Who told this story? Where did it happen? What place, song, food, or word helps us remember it?',
            'ohrid-as-a-cultural-centre' => 'Ohrid is an important cultural and historical place because it brings together lake life, old architecture, churches, learning traditions, and family memories. A beginner does not need advanced history to appreciate Ohrid. Start with the lake, the old town feeling, the idea of learning through places, and the way families speak about visits. Respectful learning means noticing beauty and memory without making exaggerated claims. Places can teach when we observe carefully.',
            'skopje-through-time' => 'Skopje is the capital today and a major city where old and modern parts sit close together. Learners might notice bridges, markets, neighbourhoods, museums, transport, schools, and public buildings. A city changes over time, so Skopje can be understood as layers: older streets and markets, newer buildings and roads, family memories, and daily life. Ask what changed, what stayed, and what people remember when they talk about the city.',
            'how-families-preserve-history' => 'Families preserve history in ordinary ways: stories told at dinner, names repeated across generations, photos in albums, recipes cooked on holidays, songs heard at weddings, and memories of migration. A child might learn more from one grandparent story than from a long list of dates. The goal is not to turn every family memory into a perfect record. The goal is to listen, ask kind questions, and notice how language, food, music, and objects carry identity.',
            'macedonian-migration-and-community-life-in-australia' => 'Many Macedonian families in Australia keep culture alive through community schools, churches, clubs, sport, music groups, family gatherings, weddings, food, language lessons, and local events. Migration stories can include hard work, change, memory, and pride. Use respectful general wording because every family story is different. For learners, the key idea is connection: Macedonian can be heard and practised in homes, community centres, celebrations, and friendships across generations.',
            'macedonian-culture-basics' => 'Family, oro, music, food, and celebrations are common ways people experience Macedonian culture. Oro is a circle dance that can bring young and old together. At gatherings, people may share food, greet relatives, hear familiar songs, and tell stories. Traditions keep people connected because they give families repeated moments of belonging. They also change as families live in Australia, so learners should ask how their own family celebrates and what each custom means to them.',
            'macedonian-weddings' => 'Macedonian weddings can include music, dancing, food, guests, family roles, photos, speeches, and joyful noise. Traditions may vary by region, religion, and family preference, so this lesson uses general language. Useful words include свадба, невеста, младоженец, гости, музика, оро, семејство, and прослава. A wedding is more than one event; it can be a place where children hear language, see relatives, learn dance steps, and connect celebration with identity.',
            'holidays-and-family-gatherings' => 'Holidays and family gatherings often include visiting relatives, preparing food, setting a table, sharing songs or stories, and remembering people who are far away. Some families gather for religious holidays, name days, birthdays, community events, or simple Sunday meals. What matters for learners is the language of welcome: guests, lunch, visit, table, bread, thank you, and goodbye. Gatherings make vocabulary easier because the words happen in real life.',
            'traditional-clothing-and-symbols' => 'Traditional clothing and symbols should be learned with respect and care. Clothing can vary by region and occasion, and details such as embroidery, colours, patterns, aprons, belts, shirts, and head coverings may carry local meaning. Symbols can connect people to heritage, but learners should avoid overclaiming that one pattern represents every Macedonian family. A good beginner habit is to describe what you see first: colour, shape, pattern, material, and when it is worn.',
            'macedonian-community-life-in-australia' => 'Macedonian community life in Australia can include language schools, community events, sport, music, dance, churches, clubs, festivals, fundraisers, and family networks. These spaces help children hear Macedonian outside the home and help adults reconnect with culture. Community also gives learners a reason to use words: greeting someone, ordering food, joining a dance, asking about a song, or recognising a place name from family stories.',
            'macedonian-food-and-music-basics' => 'Food and music often appear together at Macedonian gatherings. Tavče gravče, ajvar, shopska salad, bread, cheese, grilled foods, and shared plates can sit beside folk songs, dancing, and family conversation. Music helps with rhythm and memory, while food gives language a place at the table. Learners can ask: What is this dish called? Who made it? What song is playing? What word do I hear again and again?',
            'tavce-gravce' => 'Tavče gravče is a traditional baked bean dish often connected with home cooking and shared meals. The name points to beans and a baking dish. Families may prepare it differently, but common ideas include beans, onion, pepper, paprika, oil, warmth, and a slow cooked feeling. For learners, the dish is useful because it teaches food words and cultural meaning at the same time. It is not just a recipe; it is a memory of lunch, guests, and home.',
            'ajvar-and-peppers' => 'Ajvar is a pepper spread often connected with autumn preparation, roasted peppers, family work, jars, bread, cheese, and shared meals. Families may make it mild or hot, smooth or chunky, and every household can have its own preference. The beginner words are ајвар, пиперка, леб, сирење, печено, тегла, and есен. Ajvar is a good culture lesson because it shows how food preparation can become a family event, not only something bought from a shop.',
            'shopska-salad-and-shared-meals' => 'Shopska salad is often made with tomatoes, cucumber, peppers, onion, and white cheese. It is simple, colourful, and easy to connect with beginner vocabulary: домат, краставица, пиперка, кромид, сирење, салата, чинија, and трпеза. Shared meals matter because people talk, pass food, tell stories, and teach children polite phrases. A table can become a small classroom when learners name ingredients and ask for food in Macedonian.',
            'folk-music-and-celebrations' => 'Folk music is often heard at weddings, parties, dances, festivals, and community events. Instruments can vary, and families may know different songs, but the shared idea is rhythm, memory, and movement. Music helps language because repeated words, choruses, and dance calls stay in the mind. Learners can clap a rhythm, notice whether people dance oro, listen for familiar words, and ask older relatives what song they remember from celebrations.',
        ][$lesson['slug']] ?? 'This topic becomes easier when learners connect the words to real life. Read the vocabulary, notice the examples, and ask how the idea appears at home, in school, on a map, at a celebration, or in the community.';
    }

    private function topicContextMk(array $lesson): string
    {
        return [
            'basic-macedonian-greetings' => 'Поздравите се прв чекор во разговор. Користи здраво, добро утро, добар ден, добра вечер, добра ноќ, благодарам, те молам, како си, добро сум, пријатно и довидување. Краток разговор: А: Здраво, како си? Б: Добро сум, благодарам. А ти?',
            'numbers-1-20' => 'Броевите се користат за возраст, игри, училиште и броење дома. Вежбај: еден, два, три, четири, пет, шест, седум, осум, девет, десет, единаесет, дванаесет, тринаесет, четиринаесет, петнаесет, шеснаесет, седумнаесет, осумнаесет, деветнаесет, дваесет.',
            'family-words' => 'Семејните зборови се важни за деца во дијаспората. Научи мајка, татко, брат, сестра, баба, дедо, тетка, чичко, вујко, братучед и братучетка. Кажи: Ова е мојата мајка. Имам една сестра.',
            'days-months-and-time' => 'Зборовите за време помагаат за календар, училиште и планови. Вежбај денес, утре, вчера, утро, ден, вечер, ноќ, понеделник, вторник, среда, четврток, петок, сабота, недела и месеците од јануари до декември.',
            'common-classroom-words' => 'Училишните зборови можат да се вежбаат со вистински предмети: книга, молив, тетратка, училиште, наставник, ученик, маса, стол, чита и пишува. Покажи предмет и кажи го зборот.',
            'introduction-to-macedonian-cyrillic-alphabet' => 'Македонскиот јазик користи кирилична азбука со 31 буква. Почни со препознавање букви како А, Б, В, Г, Д, Е, Ж, З, И, Ј, К, Л, М, Н, О, П, Р, С и Т.',
            'vowels-and-consonants' => 'Самогласките се А, Е, И, О и У. Тие помагаат зборот да се чита јасно. Спој ги со согласки: ма-ма, та-то, до-бро.',
            'letters-that-look-familiar' => 'Некои букви изгледаат познато: А, Е, К, М, О и Т. Но Р не е англиско P, туку звучи како r. В звучи поблиску до v.',
            'reading-simple-macedonian-words' => 'Почни со кратки зборови: дом, мама, тато, вода, книга, ден, брат и сестра. Читај полека по слогови: ма-ма, во-да, до-бро.',
            'writing-your-first-macedonian-words' => 'Пишувањето помага да се запаметат буквите. Препишувај мама, тато, баба, дедо, брат, сестра или своето име ако го знаеш на македонски.',
            'macedonian-geography-basics' => 'Македонија има градови, езера, планини, долини, патишта и села. Скопје е главен град, Охрид е град покрај езеро, а Битола, Тетово и Прилеп се важни градови.',
            'skopje-ohrid-and-bitola' => 'Скопје е главен град. Охрид е познат по езерото и старата градска атмосфера. Битола е важен историски град на југозапад, близу Пелистер.',
            'lake-ohrid-and-lake-prespa' => 'Охридското Езеро и Преспанското Езеро се важни природни места. Езерата се поврзани со природа, патувања, летни спомени и семејни фотографии.',
            'mountains-and-national-parks' => 'Планините се важен дел од географијата. Вежбај имиња како Пелистер, Маврово, Шар Планина и Водно. Зборови: планина, врв, долина, парк, снег и поглед.',
            'macedonian-regions-and-travel' => 'За региони и патување користи зборови како град, село, езеро, планина, пат, север, југ, исток, запад, близу и далеку.',
            'macedonia-history-basics' => 'Историјата се учи преку приказни, места, музеи, традиции, јазик, песни и семејно сеќавање. Почни со внимателни прашања.',
            'ohrid-as-a-cultural-centre' => 'Охрид е важно културно и историско место. Езерото, старата архитектура, црквите, учењето и семејните посети го прават посебен.',
            'skopje-through-time' => 'Скопје е главен град и место каде се среќаваат старо и ново. Може да видиш мостови, пазари, населби, музеи и модерни делови.',
            'how-families-preserve-history' => 'Семејствата ја чуваат историјата со приказни, фотографии, рецепти, песни, имиња и спомени од преселба. Слушај и поставувај добри прашања.',
            'macedonian-migration-and-community-life-in-australia' => 'Во Австралија многу семејства ја чуваат културата преку училишта, цркви, клубови, спорт, музика, собири и јазик дома.',
            'macedonian-culture-basics' => 'Семејство, оро, музика, храна и прослави ги поврзуваат луѓето. Прашај како твоето семејство слави и што значи традицијата за вас.',
            'macedonian-weddings' => 'Македонските свадби можат да имаат музика, оро, храна, гости, фотографии и семејни обичаи. Традициите се разликуваат по семејство и регион.',
            'holidays-and-family-gatherings' => 'Празниците и собирите носат роднини, храна, посети, песни, приказни и заедничка трпеза. Тука зборовите се учат природно.',
            'traditional-clothing-and-symbols' => 'Традиционалната облека може да има вез, бои, шари и симболи. Различни региони имаат различни детали, затоа учи со почит.',
            'macedonian-community-life-in-australia' => 'Македонската заедница во Австралија може да вклучува јазични училишта, настани, спорт, музика, цркви, клубови и семејни мрежи.',
            'macedonian-food-and-music-basics' => 'Храната и музиката често одат заедно на собири. Тавче гравче, ајвар, шопска салата, песни и оро помагаат да се памети јазикот.',
            'tavce-gravce' => 'Тавче гравче е традиционално јадење со грав што често се поврзува со дом, ручек, гости и топла трпеза.',
            'ajvar-and-peppers' => 'Ајвар е намаз од пиперки, често поврзан со есен, печени пиперки, тегли, леб, сирење и семејна подготовка.',
            'shopska-salad-and-shared-meals' => 'Шопска салата често има домати, краставица, пиперки, кромид и сирење. Заедничката трпеза помага за разговор и учење.',
            'folk-music-and-celebrations' => 'Народната музика се слуша на свадби, забави, фестивали и настани. Ритамот, песните и орото помагаат да се памети културата.',
        ][$lesson['slug']] ?? 'Темата е полесна кога ја поврзуваш со вистински живот, дом, училиште, мапа, прослава или заедница.';
    }

    private function studyContextEn(array $lesson): string
    {
        return "A good study pattern is see it, say it, use it. First, look at the word or fact. Second, say it aloud slowly. Third, use it in a sentence, point to it on a map, connect it to a person, or explain it to someone at home. If Macedonian reading feels difficult, switch between English support and Macedonian examples. The related quiz is not meant to trick you; it is there to help you check what your brain can now recognise.";
    }

    private function studyContextMk(array $lesson): string
    {
        return 'Добар начин за учење е: види, кажи, употреби. Прво погледни го зборот или фактот. Потоа кажи го на глас. На крај употреби го во реченица, на мапа или во разговор дома.';
    }

    private function extraExamplesEn(array $lesson): array
    {
        return [
            "Use one key word from this lesson in a sentence about your own family, school, map, celebration, meal, or community.",
            "Explain {$lesson['title_en']} to another learner in one simple English sentence, then repeat one Macedonian word from the lesson.",
        ];
    }

    private function extraExamplesMk(array $lesson): array
    {
        return [
            'Употреби еден збор од лекцијата во кратка реченица.',
            "Објасни ја темата {$lesson['title_mk']} со една едноставна мисла.",
        ];
    }

    private function extraFactsEn(array $lesson): array
    {
        return [
            'Original MakedonIQ lesson content written for beginner family learning.',
            'No textbook wording is needed: learn through simple examples, memory, place, and practice.',
            'Related quiz links, when available, use the normal secure quiz flow.',
        ];
    }

    private function extraFactsMk(array $lesson): array
    {
        return [
            'Оригинална MakedonIQ лекција за почетничко семејно учење.',
            'Учи преку едноставни примери, сеќавање, место и вежбање.',
        ];
    }

    private function practicePromptsEn(array $lesson): array
    {
        return [
            'Write three words from the lesson and read them aloud twice.',
            'Ask a family member one simple question connected to the topic.',
            'Before the quiz, close the page and try to remember two key facts without looking.',
        ];
    }

    private function practicePromptsMk(array $lesson): array
    {
        return [
            'Запиши три зборови од лекцијата и прочитај ги два пати.',
            'Прашај член од семејството едно едноставно прашање за темата.',
            'Пред квизот, пробај да запомниш два клучни факти без гледање.',
        ];
    }

    private function rememberEn(array $lesson): string
    {
        return "{$lesson['title_en']} is easier when it is connected to real life. Learn a few words first, then add examples, places, people, and memories. You do not need to master everything in one sitting. Return to the lesson, speak the words aloud, and use the quiz as a friendly check of what is becoming familiar.";
    }

    private function rememberMk(array $lesson): string
    {
        return "{$lesson['title_mk']} е полесно кога се поврзува со вистински живот. Научи неколку зборови, кажи ги на глас, врати се на лекцијата и користи го квизот како проверка.";
    }

    private function translationQuiz(string $categorySlug, string $lessonSlug, string $slug, string $titleEn, string $titleMk, string $descriptionEn, string $descriptionMk, int $sortOrder, array $terms): array
    {
        return $this->quiz($categorySlug, $lessonSlug, $slug, $titleEn, $titleMk, $descriptionEn, $descriptionMk, $sortOrder, array_map(
            fn (array $term): array => $this->translationQuestion($term[0], $term[1], $term[2]),
            $terms,
        ));
    }

    private function factQuiz(string $categorySlug, string $lessonSlug, string $slug, string $titleEn, string $titleMk, string $descriptionEn, string $descriptionMk, int $sortOrder, array $questions): array
    {
        return $this->quiz($categorySlug, $lessonSlug, $slug, $titleEn, $titleMk, $descriptionEn, $descriptionMk, $sortOrder, $questions);
    }

    private function quiz(string $categorySlug, string $lessonSlug, string $slug, string $titleEn, string $titleMk, string $descriptionEn, string $descriptionMk, int $sortOrder, array $questions): array
    {
        return [
            'category_slug' => $categorySlug,
            'lesson_slug' => $lessonSlug,
            'slug' => $slug,
            'title_en' => $titleEn,
            'title_mk' => $titleMk,
            'description_en' => $descriptionEn,
            'description_mk' => $descriptionMk,
            'difficulty' => 'beginner',
            'estimated_minutes' => 8,
            'sort_order' => $sortOrder,
            'questions' => $questions,
        ];
    }

    private function translationQuestion(string $sourceMk, string $correctEn, array $distractorsEn): array
    {
        return [
            'translation_direction' => 'mk_to_en',
            'question_en' => "What does \"{$sourceMk}\" mean?",
            'question_mk' => "Што значи „{$sourceMk}“ на англиски?",
            'explanation_en' => "\"{$sourceMk}\" means \"{$correctEn}\".",
            'explanation_mk' => "„{$sourceMk}“ значи „{$correctEn}“.",
            'answers' => $this->answerOptions($correctEn, null, $distractorsEn, null, $sourceMk),
        ];
    }

    private function factQuestion(string $questionEn, string $questionMk, string $explanationEn, string $explanationMk, array $answers): array
    {
        return [
            'question_en' => $questionEn,
            'question_mk' => $questionMk,
            'explanation_en' => $explanationEn,
            'explanation_mk' => $explanationMk,
            'answers' => array_map(fn (array $answer): array => [
                'answer_en' => $answer[0],
                'answer_mk' => $answer[1],
                'is_correct' => $answer[2],
            ], $answers),
        ];
    }

    private function answerOptions(string $correctEn, ?string $correctMk, array $distractorsEn, ?array $distractorsMk, string $seed): array
    {
        $options = [[
            'answer_en' => $correctEn,
            'answer_mk' => $correctMk,
            'is_correct' => true,
        ]];

        foreach ($distractorsEn as $index => $distractor) {
            $options[] = [
                'answer_en' => $distractor,
                'answer_mk' => $distractorsMk[$index] ?? null,
                'is_correct' => false,
            ];
        }

        $offset = abs(crc32($seed)) % count($options);

        return array_merge(array_slice($options, $offset), array_slice($options, 0, $offset));
    }

    private function mapQuestion(string $key, string $answerEn, string $answerMk, string $targetType, string $explanationEn, string $explanationMk, array $answers): array
    {
        $coordinates = MapChallengeCoordinates::for($key);

        return [
            'map_key' => $key,
            'answer_en' => $answerEn,
            'answer_mk' => $answerMk,
            'x' => $coordinates['x'],
            'y' => $coordinates['y'],
            'target_type' => $targetType,
            'explanation_en' => $explanationEn,
            'explanation_mk' => $explanationMk,
            'answers' => array_map(fn (array $answer): array => [
                'answer_en' => $answer[0],
                'answer_mk' => $answer[1],
                'is_correct' => $answer[2],
            ], $answers),
        ];
    }
}
