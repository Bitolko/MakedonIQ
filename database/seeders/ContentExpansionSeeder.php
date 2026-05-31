<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class ContentExpansionSeeder extends Seeder
{
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
        $quiz = Quiz::where('slug', 'macedonia-map-challenge')->first();

        if (! $quiz) {
            return;
        }

        $sortOrder = max(1, (int) $quiz->questions()->max('sort_order'));

        foreach ($this->mapQuestions() as $questionData) {
            $question = $quiz->questions()
                ->where('metadata->map_target_key', $questionData['map_key'])
                ->first();

            if (! $question) {
                $sortOrder++;
                $question = $quiz->questions()->create($this->mapQuestionAttributes($questionData, $sortOrder));
            }

            if ($question->attemptAnswers()->exists()) {
                continue;
            }

            $question->update($this->mapQuestionAttributes($questionData, (int) $question->sort_order ?: $sortOrder));
            $this->syncAnswers($question, $questionData['answers']);
        }
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
                    ? 'Which landmark is highlighted on the map?'
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
            $this->mapQuestion('skopje', 'Skopje', 'Скопје', 52, 28, 'city', 'Skopje is the capital city.', 'Скопје е главен град.', [['Skopje', 'Скопје', true], ['Ohrid', 'Охрид', false], ['Bitola', 'Битола', false], ['Tetovo', 'Тетово', false]]),
            $this->mapQuestion('ohrid', 'Ohrid', 'Охрид', 22, 72, 'city', 'Ohrid is known for its lake and old town.', 'Охрид е познат по езерото и стариот град.', [['Ohrid', 'Охрид', true], ['Prilep', 'Прилеп', false], ['Kumanovo', 'Куманово', false], ['Strumica', 'Струмица', false]]),
            $this->mapQuestion('bitola', 'Bitola', 'Битола', 38, 80, 'city', 'Bitola is an important city in the south-west.', 'Битола е важен град на југозапад.', [['Bitola', 'Битола', true], ['Veles', 'Велес', false], ['Gostivar', 'Гостивар', false], ['Štip', 'Штип', false]]),
            $this->mapQuestion('prilep', 'Prilep', 'Прилеп', 45, 70, 'city', 'Prilep is in the central-southern part of the country.', 'Прилеп е во централно-јужниот дел.', [['Prilep', 'Прилеп', true], ['Skopje', 'Скопје', false], ['Struga', 'Струга', false], ['Kavadarci', 'Кавадарци', false]]),
            $this->mapQuestion('tetovo', 'Tetovo', 'Тетово', 30, 26, 'city', 'Tetovo is in the north-west.', 'Тетово е на северозапад.', [['Tetovo', 'Тетово', true], ['Gevgelija', 'Гевгелија', false], ['Kočani', 'Кочани', false], ['Prilep', 'Прилеп', false]]),
            $this->mapQuestion('kumanovo', 'Kumanovo', 'Куманово', 62, 20, 'city', 'Kumanovo is north-east of Skopje.', 'Куманово е североисточно од Скопје.', [['Kumanovo', 'Куманово', true], ['Bitola', 'Битола', false], ['Gevgelija', 'Гевгелија', false], ['Gostivar', 'Гостивар', false]]),
            $this->mapQuestion('strumica', 'Strumica', 'Струмица', 75, 78, 'city', 'Strumica is in the south-east.', 'Струмица е на југоисток.', [['Strumica', 'Струмица', true], ['Ohrid', 'Охрид', false], ['Kičevo', 'Кичево', false], ['Tetovo', 'Тетово', false]]),
            $this->mapQuestion('veles', 'Veles', 'Велес', 58, 55, 'city', 'Veles sits near the central Vardar route.', 'Велес е близу централниот вардарски правец.', [['Veles', 'Велес', true], ['Struga', 'Струга', false], ['Ohrid', 'Охрид', false], ['Kočani', 'Кочани', false]]),
            $this->mapQuestion('stip', 'Štip', 'Штип', 68, 56, 'city', 'Štip is an eastern city.', 'Штип е град на исток.', [['Štip', 'Штип', true], ['Bitola', 'Битола', false], ['Tetovo', 'Тетово', false], ['Gevgelija', 'Гевгелија', false]]),
            $this->mapQuestion('gostivar', 'Gostivar', 'Гостивар', 25, 42, 'city', 'Gostivar is in the western part of the country.', 'Гостивар е во западниот дел.', [['Gostivar', 'Гостивар', true], ['Kavadarci', 'Кавадарци', false], ['Strumica', 'Струмица', false], ['Veles', 'Велес', false]]),
            $this->mapQuestion('struga', 'Struga', 'Струга', 16, 70, 'city', 'Struga is near Lake Ohrid.', 'Струга е близу Охридското Езеро.', [['Struga', 'Струга', true], ['Skopje', 'Скопје', false], ['Kočani', 'Кочани', false], ['Prilep', 'Прилеп', false]]),
            $this->mapQuestion('kicevo', 'Kičevo', 'Кичево', 30, 56, 'city', 'Kičevo is in the western-central area.', 'Кичево е во западно-централниот дел.', [['Kičevo', 'Кичево', true], ['Kumanovo', 'Куманово', false], ['Gevgelija', 'Гевгелија', false], ['Bitola', 'Битола', false]]),
            $this->mapQuestion('kavadarci', 'Kavadarci', 'Кавадарци', 53, 73, 'city', 'Kavadarci is associated with the Tikveš region.', 'Кавадарци е поврзан со Тиквешкиот регион.', [['Kavadarci', 'Кавадарци', true], ['Gostivar', 'Гостивар', false], ['Tetovo', 'Тетово', false], ['Struga', 'Струга', false]]),
            $this->mapQuestion('gevgelija', 'Gevgelija', 'Гевгелија', 58, 90, 'city', 'Gevgelija is in the far south.', 'Гевгелија е на крајниот југ.', [['Gevgelija', 'Гевгелија', true], ['Ohrid', 'Охрид', false], ['Veles', 'Велес', false], ['Kumanovo', 'Куманово', false]]),
            $this->mapQuestion('kocani', 'Kočani', 'Кочани', 78, 50, 'city', 'Kočani is in the east.', 'Кочани е на исток.', [['Kočani', 'Кочани', true], ['Bitola', 'Битола', false], ['Prilep', 'Прилеп', false], ['Struga', 'Струга', false]]),
            $this->mapQuestion('lake-ohrid', 'Lake Ohrid', 'Охридско Езеро', 18, 75, 'lake', 'Lake Ohrid is one of the best-known lakes in the region.', 'Охридското Езеро е едно од најпознатите езера во регионот.', [['Lake Ohrid', 'Охридско Езеро', true], ['Lake Prespa', 'Преспанско Езеро', false], ['Matka Canyon', 'Кањон Матка', false], ['Mavrovo', 'Маврово', false]]),
            $this->mapQuestion('lake-prespa', 'Lake Prespa', 'Преспанско Езеро', 28, 86, 'lake', 'Lake Prespa is another important lake in the south-west.', 'Преспанското Езеро е уште едно важно езеро на југозапад.', [['Lake Prespa', 'Преспанско Езеро', true], ['Lake Ohrid', 'Охридско Езеро', false], ['Vodno', 'Водно', false], ['Pelister', 'Пелистер', false]]),
            $this->mapQuestion('matka-canyon', 'Matka Canyon', 'Кањон Матка', 44, 34, 'landmark', 'Matka Canyon is near Skopje.', 'Кањонот Матка е близу Скопје.', [['Matka Canyon', 'Кањон Матка', true], ['Lake Ohrid', 'Охридско Езеро', false], ['Pelister', 'Пелистер', false], ['Gevgelija', 'Гевгелија', false]]),
            $this->mapQuestion('vodno', 'Vodno', 'Водно', 50, 32, 'landmark', 'Vodno is a mountain area near Skopje.', 'Водно е планинско место близу Скопје.', [['Vodno', 'Водно', true], ['Mavrovo', 'Маврово', false], ['Lake Prespa', 'Преспанско Езеро', false], ['Štip', 'Штип', false]]),
            $this->mapQuestion('mavrovo', 'Mavrovo', 'Маврово', 20, 42, 'landmark', 'Mavrovo is known for mountain nature.', 'Маврово е познато по планинска природа.', [['Mavrovo', 'Маврово', true], ['Vodno', 'Водно', false], ['Kavadarci', 'Кавадарци', false], ['Kumanovo', 'Куманово', false]]),
            $this->mapQuestion('pelister', 'Pelister', 'Пелистер', 36, 86, 'landmark', 'Pelister is associated with mountain nature near Bitola.', 'Пелистер е поврзан со планинска природа кај Битола.', [['Pelister', 'Пелистер', true], ['Matka Canyon', 'Кањон Матка', false], ['Lake Ohrid', 'Охридско Езеро', false], ['Tetovo', 'Тетово', false]]),
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
        return implode("\n\n", [
            "Introduction\n{$lesson['summary_en']}",
            "Learn\nRead the words slowly, say them aloud, and connect each one to a real family, school, or community moment.",
            "Key vocabulary\n- ".implode("\n- ", $lesson['vocab_en']),
            "Examples\n- ".implode("\n- ", $lesson['examples_en']),
            "Key points\n- Start with a few useful words.\n- Say the words aloud.\n- Use the words in a real sentence or family conversation.",
            "Practice prompt\n{$lesson['practice_en']}",
        ]);
    }

    private function lessonContentMk(array $lesson): string
    {
        return implode("\n\n", [
            "Вовед\n{$lesson['summary_mk']}",
            "Учи\nЧитај ги зборовите полека, кажи ги на глас и поврзи ги со семеен, училишен или заеднички момент.",
            "Клучни зборови\n- ".implode("\n- ", $lesson['vocab_mk']),
            "Примери\n- ".implode("\n- ", $lesson['examples_mk']),
            "Клучни точки\n- Почни со неколку корисни зборови.\n- Кажи ги зборовите на глас.\n- Употреби ги во вистинска реченица или семеен разговор.",
            "Практична задача\n{$lesson['practice_mk']}",
        ]);
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

    private function mapQuestion(string $key, string $answerEn, string $answerMk, int $x, int $y, string $targetType, string $explanationEn, string $explanationMk, array $answers): array
    {
        return [
            'map_key' => $key,
            'answer_en' => $answerEn,
            'answer_mk' => $answerMk,
            'x' => $x,
            'y' => $y,
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
