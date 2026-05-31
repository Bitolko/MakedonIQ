<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class MakedonIQSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->content() as $categoryIndex => $categoryData) {
            $category = Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name_en' => $categoryData['name_en'],
                    'name_mk' => $categoryData['name_mk'],
                    'description_en' => $categoryData['description_en'],
                    'description_mk' => $categoryData['description_mk'],
                    'icon' => $categoryData['icon'],
                    'sort_order' => $categoryIndex + 1,
                    'is_published' => true,
                ],
            );

            foreach ($categoryData['quizzes'] as $quizIndex => $quizData) {
                $quiz = $category->quizzes()->updateOrCreate(
                    ['slug' => $quizData['slug']],
                    [
                        'title_en' => $quizData['title_en'],
                        'title_mk' => $quizData['title_mk'],
                        'description_en' => $quizData['description_en'],
                        'description_mk' => $quizData['description_mk'],
                        'difficulty' => $quizData['difficulty'],
                        'estimated_minutes' => $quizData['estimated_minutes'],
                        'points_per_question' => 10,
                        'is_published' => true,
                        'sort_order' => $quizIndex + 1,
                    ],
                );

                $this->syncQuizQuestions($quiz, $quizData['questions']);
            }
        }
    }

    private function syncQuizQuestions(Quiz $quiz, array $questions): void
    {
        $hasCompletedAttempts = $quiz->attempts()->whereNotNull('completed_at')->exists();

        if (! $hasCompletedAttempts) {
            $quiz->questions()->delete();
        }

        foreach ($questions as $questionIndex => $questionData) {
            $question = $hasCompletedAttempts
                ? $quiz->questions()->firstOrCreate(
                    ['question_en' => $questionData['question_en']],
                    $this->questionAttributes($questionData, $questionIndex),
                )
                : $quiz->questions()->create($this->questionAttributes($questionData, $questionIndex));

            if ($question->attemptAnswers()->exists()) {
                continue;
            }

            $question->update($this->questionAttributes($questionData, $questionIndex));
            $question->answers()->delete();

            foreach ($questionData['answers'] as $answerIndex => $answerData) {
                $question->answers()->create([
                    'answer_en' => $answerData['answer_en'],
                    'answer_mk' => $answerData['answer_mk'],
                    'is_correct' => $answerData['is_correct'],
                    'sort_order' => $answerIndex + 1,
                ]);
            }
        }
    }

    private function questionAttributes(array $questionData, int $questionIndex): array
    {
        return [
            'translation_direction' => $questionData['translation_direction'] ?? null,
            'question_en' => $questionData['question_en'],
            'question_mk' => $questionData['question_mk'],
            'explanation_en' => $questionData['explanation_en'],
            'explanation_mk' => $questionData['explanation_mk'],
            'sort_order' => $questionIndex + 1,
            'points' => null,
            'is_published' => true,
        ];
    }

    private function content(): array
    {
        return [
            [
                'name_en' => 'Macedonian Language',
                'name_mk' => 'Македонски јазик',
                'slug' => 'macedonian-language',
                'description_en' => 'Learn useful words and phrases for family conversations.',
                'description_mk' => 'Научи корисни зборови и фрази за семејни разговори.',
                'icon' => 'Aa',
                'quizzes' => [
                    [
                        'title_en' => 'Basic Macedonian Greetings',
                        'title_mk' => 'Основни македонски поздрави',
                        'slug' => 'basic-macedonian-greetings',
                        'description_en' => 'Start with friendly everyday greetings and polite phrases.',
                        'description_mk' => 'Започни со секојдневни поздрави и љубезни фрази.',
                        'difficulty' => 'beginner',
                        'estimated_minutes' => 8,
                        'questions' => [
                            $this->question('What does “добро утро” mean?', 'Што значи „добро утро“ на англиски?', '“Добро утро” means “Good morning”.', '„Добро утро“ значи „Good morning“.', [
                                ['Good night', 'Добра ноќ', false],
                                ['Good morning', 'Добро утро', true],
                                ['Thank you', 'Благодарам', false],
                                ['Goodbye', 'Довидување', false],
                            ], 'mk_to_en'),
                            $this->question('What does “благодарам” mean?', 'Што значи „благодарам“ на англиски?', '“Благодарам” is used to say “Thank you”.', '„Благодарам“ се користи за „Thank you“.', [
                                ['Please', 'Ве молам', false],
                                ['Thank you', 'Благодарам', true],
                                ['Hello', 'Здраво', false],
                                ['Good evening', 'Добра вечер', false],
                            ], 'mk_to_en'),
                            $this->question('What does “пријатно” mean?', 'Што значи „пријатно“ на англиски?', '“Пријатно” can be used for “Enjoy” or “Bon appetit”.', '„Пријатно“ може да значи „Enjoy“ или „Bon appetit“.', [
                                ['Enjoy / Bon appetit', 'Пријатно', true],
                                ['Good night', 'Добра ноќ', false],
                                ['My name is', 'Јас се викам', false],
                                ['Where are you?', 'Каде си?', false],
                            ], 'mk_to_en'),
                            $this->question('What does “како си?” mean?', 'Што значи „како си?“ на англиски?', '“Како си?” means “How are you?”.', '„Како си?“ значи „How are you?“.', [
                                ['How old are you?', 'Колку години имаш?', false],
                                ['How are you?', 'Како си?', true],
                                ['Where are you from?', 'Од каде си?', false],
                                ['What time is it?', 'Колку е часот?', false],
                            ], 'mk_to_en'),
                            $this->question('What does “добра ноќ” mean?', 'Што значи „добра ноќ“ на англиски?', '“Добра ноќ” means “Good night”.', '„Добра ноќ“ значи „Good night“.', [
                                ['Good morning', 'Добро утро', false],
                                ['Good afternoon', 'Добар ден', false],
                                ['Good night', 'Добра ноќ', true],
                                ['Good luck', 'Со среќа', false],
                            ], 'mk_to_en'),
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Macedonian Alphabet',
                'name_mk' => 'Македонска азбука',
                'slug' => 'macedonian-alphabet',
                'description_en' => 'Recognise Cyrillic letters and sounds.',
                'description_mk' => 'Препознај кирилични букви и звуци.',
                'icon' => 'AB',
                'quizzes' => [
                    [
                        'title_en' => 'Cyrillic Alphabet Basics',
                        'title_mk' => 'Основи на кириличната азбука',
                        'slug' => 'cyrillic-alphabet-basics',
                        'description_en' => 'Learn a few important sounds in the Macedonian alphabet.',
                        'description_mk' => 'Научи неколку важни звуци во македонската азбука.',
                        'difficulty' => 'beginner',
                        'estimated_minutes' => 7,
                        'questions' => [
                            $this->question('Which letter makes the “m” sound?', 'Која буква го дава звукот „m“?', 'The Macedonian letter М makes the “m” sound.', 'Македонската буква М го дава звукот „m“.', [
                                ['М', 'М', true],
                                ['А', 'А', false],
                                ['К', 'К', false],
                                ['Т', 'Т', false],
                            ]),
                            $this->question('Which letter makes the “a” sound?', 'Која буква го дава звукот „a“?', 'The letter А makes the “a” sound.', 'Буквата А го дава звукот „a“.', [
                                ['О', 'О', false],
                                ['А', 'А', true],
                                ['Е', 'Е', false],
                                ['И', 'И', false],
                            ]),
                            $this->question('Which letter makes the “k” sound?', 'Која буква го дава звукот „k“?', 'The letter К makes the “k” sound.', 'Буквата К го дава звукот „k“.', [
                                ['К', 'К', true],
                                ['М', 'М', false],
                                ['Н', 'Н', false],
                                ['Р', 'Р', false],
                            ]),
                            $this->question('Which letter is used in “Македонија”?', 'Која буква се користи во „Македонија“?', 'The word “Македонија” includes the letter М.', 'Зборот „Македонија“ ја содржи буквата М.', [
                                ['М', 'М', true],
                                ['Џ', 'Џ', false],
                                ['Ф', 'Ф', false],
                                ['Ќ', 'Ќ', false],
                            ]),
                            $this->question('How many letters are in the modern Macedonian alphabet?', 'Колку букви има современата македонска азбука?', 'The modern Macedonian alphabet has 31 letters.', 'Современата македонска азбука има 31 буква.', [
                                ['26', '26', false],
                                ['30', '30', false],
                                ['31', '31', true],
                                ['33', '33', false],
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'History of Macedonia',
                'name_mk' => 'Историја на Македонија',
                'slug' => 'history-of-macedonia',
                'description_en' => 'Explore beginner-friendly history and heritage.',
                'description_mk' => 'Истражи едноставна историја и наследство.',
                'icon' => 'HI',
                'quizzes' => [
                    [
                        'title_en' => 'Macedonia History Basics',
                        'title_mk' => 'Основи на македонската историја',
                        'slug' => 'macedonia-history-basics',
                        'description_en' => 'A gentle introduction to history, places, and memory.',
                        'description_mk' => 'Нежен вовед во историја, места и паметење.',
                        'difficulty' => 'beginner',
                        'estimated_minutes' => 8,
                        'questions' => [
                            $this->question('Which ancient kingdom is often associated with Alexander the Great?', 'Кое античко кралство често се поврзува со Александар Велики?', 'Alexander the Great is often associated with ancient Macedonia.', 'Александар Велики често се поврзува со античка Македонија.', [
                                ['Ancient Macedonia', 'Античка Македонија', true],
                                ['Ancient Egypt', 'Антички Египет', false],
                                ['Ancient China', 'Античка Кина', false],
                                ['Ancient India', 'Античка Индија', false],
                            ]),
                            $this->question('What is Skopje known as today?', 'Што е Скопје денес?', 'Skopje is the capital city of North Macedonia.', 'Скопје е главен град на Северна Македонија.', [
                                ['A mountain', 'Планина', false],
                                ['A capital city', 'Главен град', true],
                                ['A lake', 'Езеро', false],
                                ['A river', 'Река', false],
                            ]),
                            $this->question('Which lake is one of the oldest and deepest in Europe?', 'Кое езеро е едно од најстарите и најдлабоките во Европа?', 'Lake Ohrid is known as one of Europe’s oldest and deepest lakes.', 'Охридското Езеро е познато како едно од најстарите и најдлабоките езера во Европа.', [
                                ['Lake Ohrid', 'Охридско Езеро', true],
                                ['Lake Prespa', 'Преспанско Езеро', false],
                                ['Lake Dojran', 'Дојранско Езеро', false],
                                ['Lake Matka', 'Матка', false],
                            ]),
                            $this->question('Which city is famous for its old architecture and lake?', 'Кој град е познат по стара архитектура и езеро?', 'Ohrid is famous for its old architecture and lake.', 'Охрид е познат по стара архитектура и езеро.', [
                                ['Ohrid', 'Охрид', true],
                                ['Prilep', 'Прилеп', false],
                                ['Kumanovo', 'Куманово', false],
                                ['Tetovo', 'Тетово', false],
                            ]),
                            $this->question('What is a common way history is preserved?', 'Кој е чест начин да се зачува историјата?', 'History is preserved through stories, books, museums, and family memory.', 'Историјата се зачувува преку приказни, книги, музеи и семејно паметење.', [
                                ['Forgetting old stories', 'Заборавање стари приказни', false],
                                ['Stories and museums', 'Приказни и музеи', true],
                                ['Avoiding books', 'Избегнување книги', false],
                                ['Hiding traditions', 'Криење традиции', false],
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Geography',
                'name_mk' => 'Географија',
                'slug' => 'geography',
                'description_en' => 'Learn cities, lakes, mountains, and regions.',
                'description_mk' => 'Научи градови, езера, планини и региони.',
                'icon' => 'GE',
                'quizzes' => [
                    [
                        'title_en' => 'Macedonian Geography Basics',
                        'title_mk' => 'Основи на македонската географија',
                        'slug' => 'macedonian-geography-basics',
                        'description_en' => 'A quick quiz about places and landscapes.',
                        'description_mk' => 'Краток квиз за места и пејзажи.',
                        'difficulty' => 'beginner',
                        'estimated_minutes' => 7,
                        'questions' => [
                            $this->question('What is the capital city of North Macedonia?', 'Кој е главен град на Северна Македонија?', 'Skopje is the capital city.', 'Скопје е главен град.', [
                                ['Skopje', 'Скопје', true],
                                ['Ohrid', 'Охрид', false],
                                ['Bitola', 'Битола', false],
                                ['Struga', 'Струга', false],
                            ]),
                            $this->question('Which famous lake is shared with neighbouring countries?', 'Кое познато езеро се дели со соседни земји?', 'Lake Ohrid is shared with a neighbouring country.', 'Охридското Езеро се дели со соседна земја.', [
                                ['Lake Ohrid', 'Охридско Езеро', true],
                                ['Lake Tikvesh', 'Тиквешко Езеро', false],
                                ['Lake Mladost', 'Езеро Младост', false],
                                ['Treska Lake', 'Езеро Треска', false],
                            ]),
                            $this->question('Which city is known for Lake Ohrid?', 'Кој град е познат по Охридското Езеро?', 'Ohrid is the city most associated with Lake Ohrid.', 'Охрид е градот најмногу поврзан со Охридското Езеро.', [
                                ['Ohrid', 'Охрид', true],
                                ['Veles', 'Велес', false],
                                ['Kratovo', 'Кратово', false],
                                ['Berovo', 'Берово', false],
                            ]),
                            $this->question('Which region is known for wine production?', 'Кој регион е познат по производство на вино?', 'Tikvesh is well known for wine production.', 'Тиквеш е познат по производство на вино.', [
                                ['Tikvesh', 'Тиквеш', true],
                                ['Mavrovo', 'Маврово', false],
                                ['Matka', 'Матка', false],
                                ['Pelister', 'Пелистер', false],
                            ]),
                            $this->question('What type of landscape is common in Macedonia?', 'Каков пејзаж е чест во Македонија?', 'Mountains and valleys are common across the country.', 'Планини и долини се чести низ земјата.', [
                                ['Desert only', 'Само пустина', false],
                                ['Mountains and valleys', 'Планини и долини', true],
                                ['Ice sheets', 'Ледени површини', false],
                                ['Tropical rainforest', 'Тропска шума', false],
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Culture and Traditions',
                'name_mk' => 'Култура и традиции',
                'slug' => 'culture-and-traditions',
                'description_en' => 'Celebrate customs, dance, family, and community.',
                'description_mk' => 'Прослави обичаи, оро, семејство и заедница.',
                'icon' => 'CU',
                'quizzes' => [
                    [
                        'title_en' => 'Macedonian Culture Basics',
                        'title_mk' => 'Основи на македонската култура',
                        'slug' => 'macedonian-culture-basics',
                        'description_en' => 'Learn simple facts about traditions and celebrations.',
                        'description_mk' => 'Научи едноставни факти за традиции и прослави.',
                        'difficulty' => 'beginner',
                        'estimated_minutes' => 8,
                        'questions' => [
                            $this->question('What is a traditional Macedonian dance called?', 'Како се вика традиционално македонско играње?', 'A traditional circle dance is called oro.', 'Традиционалното играње во круг се вика оро.', [
                                ['Oro', 'Оро', true],
                                ['Tango', 'Танго', false],
                                ['Waltz', 'Валцер', false],
                                ['Salsa', 'Салса', false],
                            ]),
                            $this->question('What is commonly shared during family celebrations?', 'Што често се споделува на семејни прослави?', 'Food is commonly shared during family celebrations.', 'Храна често се споделува на семејни прослави.', [
                                ['Food', 'Храна', true],
                                ['Silence', 'Тишина', false],
                                ['Homework only', 'Само домашна работа', false],
                                ['Empty plates', 'Празни чинии', false],
                            ]),
                            $this->question('What is ajvar?', 'Што е ајвар?', 'Ajvar is a popular pepper spread.', 'Ајвар е популарен намаз од пиперки.', [
                                ['A pepper spread', 'Намаз од пиперки', true],
                                ['A lake', 'Езеро', false],
                                ['A dance', 'Оро', false],
                                ['A musical instrument', 'Инструмент', false],
                            ]),
                            $this->question('What is a common feature of Macedonian weddings?', 'Што е честа карактеристика на македонските свадби?', 'Music and dancing are common at weddings.', 'Музика и оро се чести на свадби.', [
                                ['Music and dancing', 'Музика и оро', true],
                                ['No guests', 'Без гости', false],
                                ['No food', 'Без храна', false],
                                ['No celebration', 'Без прослава', false],
                            ]),
                            $this->question('Why are traditions important?', 'Зошто се важни традициите?', 'Traditions help families remember and share culture.', 'Традициите им помагаат на семејствата да ја паметат и споделуваат културата.', [
                                ['They connect families and culture', 'Ги поврзуваат семејствата и културата', true],
                                ['They erase language', 'Го бришат јазикот', false],
                                ['They stop learning', 'Го запираат учењето', false],
                                ['They are never shared', 'Никогаш не се споделуваат', false],
                            ]),
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Food and Music',
                'name_mk' => 'Храна и музика',
                'slug' => 'food-and-music',
                'description_en' => 'Explore dishes, songs, instruments, and celebrations.',
                'description_mk' => 'Истражи јадења, песни, инструменти и прослави.',
                'icon' => 'FM',
                'quizzes' => [
                    [
                        'title_en' => 'Macedonian Food and Music Basics',
                        'title_mk' => 'Основи на македонска храна и музика',
                        'slug' => 'macedonian-food-and-music-basics',
                        'description_en' => 'A beginner quiz about favourite foods and celebration music.',
                        'description_mk' => 'Почетен квиз за омилена храна и музика на прослави.',
                        'difficulty' => 'beginner',
                        'estimated_minutes' => 8,
                        'questions' => [
                            $this->question('What is tavče gravče?', 'Што е тавче гравче?', 'Tavče gravče is a traditional baked bean dish.', 'Тавче гравче е традиционално јадење со печен грав.', [
                                ['A baked bean dish', 'Јадење со печен грав', true],
                                ['A fruit dessert', 'Овошен десерт', false],
                                ['A soft drink', 'Безалкохолен пијалак', false],
                                ['A bread only', 'Само леб', false],
                            ]),
                            $this->question('What is ajvar made from?', 'Од што се прави ајвар?', 'Ajvar is commonly made from roasted red peppers.', 'Ајвар најчесто се прави од печени црвени пиперки.', [
                                ['Roasted red peppers', 'Печени црвени пиперки', true],
                                ['Chocolate', 'Чоколадо', false],
                                ['Rice only', 'Само ориз', false],
                                ['Apples', 'Јаболка', false],
                            ]),
                            $this->question('What instrument is often associated with Balkan folk music?', 'Кој инструмент често се поврзува со балканска народна музика?', 'The accordion is often heard in Balkan folk music.', 'Хармониката често се слуша во балканска народна музика.', [
                                ['Accordion', 'Хармоника', true],
                                ['Electric guitar only', 'Само електрична гитара', false],
                                ['Triangle', 'Триангл', false],
                                ['Harp', 'Харфа', false],
                            ]),
                            $this->question('What is shopska salad commonly made with?', 'Од што најчесто се прави шопска салата?', 'Shopska salad commonly includes tomatoes, cucumbers, and cheese.', 'Шопска салата најчесто има домати, краставици и сирење.', [
                                ['Tomatoes, cucumbers, and cheese', 'Домати, краставици и сирење', true],
                                ['Beans and chocolate', 'Грав и чоколадо', false],
                                ['Rice and honey', 'Ориз и мед', false],
                                ['Only bread', 'Само леб', false],
                            ]),
                            $this->question('What is the role of music at celebrations?', 'Која е улогата на музиката на прослави?', 'Music brings people together and supports dancing and joy.', 'Музиката ги зближува луѓето и носи оро и радост.', [
                                ['To bring people together', 'Да ги зближи луѓето', true],
                                ['To stop dancing', 'Да го запре орото', false],
                                ['To make everyone quiet only', 'Само да ги замолчи сите', false],
                                ['To hide traditions', 'Да ги скрие традициите', false],
                            ]),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function question(string $questionEn, string $questionMk, string $explanationEn, string $explanationMk, array $answers, ?string $translationDirection = null): array
    {
        return [
            'translation_direction' => $translationDirection,
            'question_en' => $questionEn,
            'question_mk' => $questionMk,
            'explanation_en' => $explanationEn,
            'explanation_mk' => $explanationMk,
            'answers' => array_map(
                fn (array $answer): array => [
                    'answer_en' => $answer[0],
                    'answer_mk' => $answer[1],
                    'is_correct' => $answer[2],
                ],
                $answers,
            ),
        ];
    }
}
