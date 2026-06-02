<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    private const DEMO_LESSON_SLUGS = [
        'basic-macedonian-greetings',
        'introduction-to-macedonian-cyrillic-alphabet',
        'macedonian-geography-basics',
    ];

    public function run(): void
    {
        foreach ($this->lessons() as $index => $lessonData) {
            $category = Category::where('slug', $lessonData['category_slug'])->first();

            if (! $category) {
                continue;
            }

            $lesson = Lesson::updateOrCreate(
                ['slug' => $lessonData['slug']],
                [
                    'category_id' => $category->id,
                    'title_en' => $lessonData['title_en'],
                    'title_mk' => $lessonData['title_mk'],
                    'summary_en' => $lessonData['summary_en'],
                    'summary_mk' => $lessonData['summary_mk'],
                    'content_en' => $lessonData['content_en'],
                    'content_mk' => $lessonData['content_mk'],
                    'difficulty' => 'beginner',
                    'estimated_minutes' => $lessonData['estimated_minutes'],
                    'sort_order' => $index + 1,
                    'is_published' => true,
                    'is_demo' => in_array($lessonData['slug'], self::DEMO_LESSON_SLUGS, true),
                ],
            );

            Quiz::where('slug', $lessonData['quiz_slug'])->update([
                'lesson_id' => $lesson->id,
            ]);
        }
    }

    private function lessons(): array
    {
        return [
            [
                'category_slug' => 'macedonian-language',
                'quiz_slug' => 'basic-macedonian-greetings',
                'slug' => 'basic-macedonian-greetings',
                'title_en' => 'Basic Macedonian Greetings',
                'title_mk' => 'Основни македонски поздрави',
                'summary_en' => 'Learn friendly words and phrases for greeting family and friends.',
                'summary_mk' => 'Научи пријателски зборови и фрази за поздравување на семејство и пријатели.',
                'estimated_minutes' => 5,
                'content_en' => <<<'TEXT'
Macedonian greetings help you begin a friendly conversation.

Здраво means hello. You can use it with friends, family, and people your age.

Добро утро means good morning. Добра вечер means good evening.

Благодарам means thank you. It is one of the most useful polite words to learn first.

Како си? means how are you? It is a simple way to ask someone how they feel.
TEXT,
                'content_mk' => <<<'TEXT'
Македонските поздрави ти помагаат да започнеш пријателски разговор.

Здраво значи hello. Можеш да го користиш со пријатели, семејство и луѓе на твоја возраст.

Добро утро значи good morning. Добра вечер значи good evening.

Благодарам значи thank you. Тоа е еден од најкорисните љубезни зборови за почеток.

Како си? значи how are you? Тоа е едноставен начин да прашаш како се чувствува некој.
TEXT,
            ],
            [
                'category_slug' => 'macedonian-alphabet',
                'quiz_slug' => 'cyrillic-alphabet-basics',
                'slug' => 'introduction-to-macedonian-cyrillic-alphabet',
                'title_en' => 'Introduction to the Macedonian Cyrillic Alphabet',
                'title_mk' => 'Вовед во македонската кирилична азбука',
                'summary_en' => 'Meet the Cyrillic script and a few important Macedonian letters.',
                'summary_mk' => 'Запознај ја кирилицата и неколку важни македонски букви.',
                'estimated_minutes' => 6,
                'content_en' => <<<'TEXT'
Macedonian is written with the Cyrillic script.

The modern Macedonian alphabet has 31 letters. Each letter has its own sound, which makes reading more regular once you learn the alphabet.

Examples of Macedonian letters include А, Б, В, Г, Д, М, and К.

The word Македонија begins with М and includes several letters you will see often.

Start slowly: learn a few letters, say their sounds aloud, then look for them in short words.
TEXT,
                'content_mk' => <<<'TEXT'
Македонскиот јазик се пишува со кирилично писмо.

Современата македонска азбука има 31 буква. Секоја буква има свој звук, па читањето станува полесно кога ќе ја научиш азбуката.

Примери за македонски букви се А, Б, В, Г, Д, М и К.

Зборот Македонија започнува со М и содржи неколку букви што често ќе ги гледаш.

Почни полека: научи неколку букви, кажи ги нивните звуци на глас, па побарај ги во кратки зборови.
TEXT,
            ],
            [
                'category_slug' => 'history-of-macedonia',
                'quiz_slug' => 'macedonia-history-basics',
                'slug' => 'macedonia-history-basics',
                'title_en' => 'Macedonia History Basics',
                'title_mk' => 'Основи на македонската историја',
                'summary_en' => 'A gentle introduction to memory, places, stories, museums, and traditions.',
                'summary_mk' => 'Нежен вовед во паметење, места, приказни, музеи и традиции.',
                'estimated_minutes' => 7,
                'content_en' => <<<'TEXT'
History is how people remember the past and share it with the next generation.

Families preserve history through stories, photographs, songs, recipes, holidays, museums, monuments, and visits to important places.

Ohrid and Skopje are important cultural and historical places. Ohrid is known for its lake, old churches, and learning traditions. Skopje is the capital of North Macedonia and a place where old and new stories meet.

Ancient history can be introduced carefully as part of a wider story about the region. For beginners, it is best to learn names, places, and why people remember them.

History is not only dates. It is also family memory, language, music, and the places people care about.
TEXT,
                'content_mk' => <<<'TEXT'
Историјата е начинот на кој луѓето го паметат минатото и го споделуваат со следната генерација.

Семејствата ја зачувуваат историјата преку приказни, фотографии, песни, рецепти, празници, музеи, споменици и посети на важни места.

Охрид и Скопје се важни културни и историски места. Охрид е познат по езерото, старите цркви и училишните традиции. Скопје е главен град на Северна Македонија и место каде што се среќаваат стари и нови приказни.

Античката историја може да се изучува внимателно како дел од пошироката приказна за регионот. За почетници, најважно е да се научат имиња, места и зошто луѓето ги паметат.

Историјата не се само датуми. Таа е и семејно паметење, јазик, музика и места што луѓето ги сакаат.
TEXT,
            ],
            [
                'category_slug' => 'geography',
                'quiz_slug' => 'macedonian-geography-basics',
                'slug' => 'macedonian-geography-basics',
                'title_en' => 'Macedonian Geography Basics',
                'title_mk' => 'Основи на македонската географија',
                'summary_en' => 'Learn about cities, lakes, mountains, valleys, and regions.',
                'summary_mk' => 'Научи за градови, езера, планини, долини и региони.',
                'estimated_minutes' => 6,
                'content_en' => <<<'TEXT'
Geography helps us understand where people live and how places shape daily life.

Skopje is the capital city of North Macedonia. It is an important centre for education, culture, transport, and government.

Ohrid is a famous city and lake. Lake Ohrid is known for its beauty and long history.

Macedonian landscapes include mountains, valleys, rivers, lakes, villages, cities, and wine regions such as Tikvesh.

When you learn geography, you also learn why food, music, travel, and traditions can feel different from place to place.
TEXT,
                'content_mk' => <<<'TEXT'
Географијата ни помага да разбереме каде живеат луѓето и како местата го обликуваат секојдневниот живот.

Скопје е главен град на Северна Македонија. Тој е важен центар за образование, култура, транспорт и државни институции.

Охрид е познат град и езеро. Охридското Езеро е познато по убавината и долгата историја.

Македонските пејзажи вклучуваат планини, долини, реки, езера, села, градови и вински региони како Тиквеш.

Кога учиш географија, учиш и зошто храната, музиката, патувањата и традициите можат да се разликуваат од место до место.
TEXT,
            ],
            [
                'category_slug' => 'culture-and-traditions',
                'quiz_slug' => 'macedonian-culture-basics',
                'slug' => 'macedonian-culture-basics',
                'title_en' => 'Macedonian Culture Basics',
                'title_mk' => 'Основи на македонската култура',
                'summary_en' => 'Explore family celebrations, oro, food, music, weddings, and holidays.',
                'summary_mk' => 'Истражи семејни прослави, оро, храна, музика, свадби и празници.',
                'estimated_minutes' => 6,
                'content_en' => <<<'TEXT'
Culture is the way people share language, food, music, celebrations, and family memory.

Macedonian families often celebrate with food, music, dancing, stories, and time together.

Oro is a traditional dance often performed in a circle or line. It brings people together at weddings, festivals, and family celebrations.

Food, music, weddings, holidays, and community gatherings help families stay connected even when they live far from Macedonia.

Traditions can change over time, but they still help people remember who they are and where their families come from.
TEXT,
                'content_mk' => <<<'TEXT'
Културата е начинот на кој луѓето споделуваат јазик, храна, музика, прослави и семејно паметење.

Македонските семејства често прославуваат со храна, музика, оро, приказни и заедничко време.

Оро е традиционален танц што често се игра во круг или линија. Тоа ги зближува луѓето на свадби, фестивали и семејни прослави.

Храната, музиката, свадбите, празниците и заедничките собири им помагаат на семејствата да останат поврзани дури и кога живеат далеку од Македонија.

Традициите можат да се менуваат со времето, но сепак им помагаат на луѓето да се сетат кои се и од каде потекнуваат нивните семејства.
TEXT,
            ],
            [
                'category_slug' => 'food-and-music',
                'quiz_slug' => 'macedonian-food-and-music-basics',
                'slug' => 'macedonian-food-and-music-basics',
                'title_en' => 'Macedonian Food and Music Basics',
                'title_mk' => 'Основи на македонската храна и музика',
                'summary_en' => 'Learn how favourite dishes and music connect families to culture.',
                'summary_mk' => 'Научи како омилените јадења и музиката ги поврзуваат семејствата со културата.',
                'estimated_minutes' => 6,
                'content_en' => <<<'TEXT'
Food and music are two joyful ways families share culture.

Tavče gravče is a traditional baked bean dish. Ajvar is a roasted red pepper spread often served with bread or meals.

Shopska salad is commonly made with tomatoes, cucumbers, peppers, onion, and cheese.

Folk music is often heard at weddings, festivals, and community events. Instruments such as the accordion can be part of celebration music.

When families cook, sing, dance, and celebrate together, food and music become part of cultural identity.
TEXT,
                'content_mk' => <<<'TEXT'
Храната и музиката се два радосни начини на кои семејствата ја споделуваат културата.

Тавче гравче е традиционално јадење со печен грав. Ајвар е намаз од печени црвени пиперки што често се јаде со леб или оброк.

Шопска салата најчесто се прави со домати, краставици, пиперки, кромид и сирење.

Народната музика често се слуша на свадби, фестивали и собири во заедницата. Инструменти како хармоника можат да бидат дел од музиката за прослава.

Кога семејствата готват, пеат, играат и прославуваат заедно, храната и музиката стануваат дел од културниот идентитет.
TEXT,
            ],
        ];
    }
}
