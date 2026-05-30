<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class MapChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'geography')->first();

        if (! $category) {
            return;
        }

        $lesson = Lesson::where('slug', 'macedonian-geography-basics')->first();

        $quiz = $category->quizzes()->updateOrCreate(
            ['slug' => 'macedonia-map-challenge'],
            [
                'lesson_id' => $lesson?->id,
                'title_en' => 'Macedonia Map Challenge',
                'title_mk' => 'Македонски мапа предизвик',
                'description_en' => 'Test your knowledge of Macedonian cities, lakes, and landmarks by guessing the highlighted place on the map.',
                'description_mk' => 'Провери го твоето знаење за македонски градови, езера и места преку погодок на означеното место на мапата.',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'points_per_question' => 10,
                'is_published' => true,
                'sort_order' => 2,
            ],
        );

        if ($quiz->attempts()->whereNotNull('completed_at')->exists() && $quiz->questions()->exists()) {
            return;
        }

        $quiz->questions()->delete();

        foreach ($this->questions() as $index => $questionData) {
            $question = $quiz->questions()->create([
                'question_type' => 'map_guess',
                'metadata' => $questionData['metadata'],
                'question_en' => $questionData['question_en'],
                'question_mk' => $questionData['question_mk'],
                'explanation_en' => $questionData['explanation_en'],
                'explanation_mk' => $questionData['explanation_mk'],
                'sort_order' => $index + 1,
                'points' => null,
                'is_published' => true,
            ]);

            foreach ($questionData['answers'] as $answerIndex => $answerData) {
                $question->answers()->create([
                    'answer_en' => $answerData[0],
                    'answer_mk' => $answerData[1],
                    'is_correct' => $answerData[2],
                    'sort_order' => $answerIndex + 1,
                ]);
            }
        }
    }

    private function questions(): array
    {
        return [
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Skopje is the capital city of North Macedonia.',
                'Скопје е главниот град на Северна Македонија.',
                'skopje',
                'Skopje',
                'Скопје',
                52,
                28,
                'city',
                [
                    ['Skopje', 'Скопје', true],
                    ['Ohrid', 'Охрид', false],
                    ['Bitola', 'Битола', false],
                    ['Tetovo', 'Тетово', false],
                ],
            ),
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Ohrid is known for its lake and old town.',
                'Охрид е познат по езерото и стариот град.',
                'ohrid',
                'Ohrid',
                'Охрид',
                22,
                72,
                'city',
                [
                    ['Ohrid', 'Охрид', true],
                    ['Prilep', 'Прилеп', false],
                    ['Kumanovo', 'Куманово', false],
                    ['Strumica', 'Струмица', false],
                ],
            ),
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Bitola is an important city in the south-west.',
                'Битола е важен град во југозападниот дел.',
                'bitola',
                'Bitola',
                'Битола',
                38,
                80,
                'city',
                [
                    ['Bitola', 'Битола', true],
                    ['Veles', 'Велес', false],
                    ['Gostivar', 'Гостивар', false],
                    ['Štip', 'Штип', false],
                ],
            ),
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Tetovo is in the north-west near the Šar Mountains.',
                'Тетово е на северозапад, близу Шар Планина.',
                'tetovo',
                'Tetovo',
                'Тетово',
                30,
                26,
                'city',
                [
                    ['Tetovo', 'Тетово', true],
                    ['Gevgelija', 'Гевгелија', false],
                    ['Kočani', 'Кочани', false],
                    ['Prilep', 'Прилеп', false],
                ],
            ),
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Prilep sits in the central-southern part of the country.',
                'Прилеп се наоѓа во централно-јужниот дел.',
                'prilep',
                'Prilep',
                'Прилеп',
                45,
                70,
                'city',
                [
                    ['Prilep', 'Прилеп', true],
                    ['Skopje', 'Скопје', false],
                    ['Struga', 'Струга', false],
                    ['Kavadarci', 'Кавадарци', false],
                ],
            ),
            $this->mapQuestion(
                'Which lake is highlighted on the map?',
                'Кое езеро е означено на мапата?',
                'Lake Ohrid is one of the most famous lakes in the region.',
                'Охридското Езеро е едно од најпознатите езера во регионот.',
                'lake-ohrid',
                'Lake Ohrid',
                'Охридско Езеро',
                18,
                75,
                'lake',
                [
                    ['Lake Ohrid', 'Охридско Езеро', true],
                    ['Lake Prespa', 'Преспанско Езеро', false],
                    ['Matka Canyon', 'Кањон Матка', false],
                    ['Mavrovo', 'Маврово', false],
                ],
            ),
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Strumica is in the south-east.',
                'Струмица се наоѓа на југоисток.',
                'strumica',
                'Strumica',
                'Струмица',
                75,
                78,
                'city',
                [
                    ['Strumica', 'Струмица', true],
                    ['Ohrid', 'Охрид', false],
                    ['Kičevo', 'Кичево', false],
                    ['Tetovo', 'Тетово', false],
                ],
            ),
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Kumanovo is north-east of Skopje.',
                'Куманово е североисточно од Скопје.',
                'kumanovo',
                'Kumanovo',
                'Куманово',
                62,
                20,
                'city',
                [
                    ['Kumanovo', 'Куманово', true],
                    ['Bitola', 'Битола', false],
                    ['Gevgelija', 'Гевгелија', false],
                    ['Gostivar', 'Гостивар', false],
                ],
            ),
        ];
    }

    private function mapQuestion(
        string $questionEn,
        string $questionMk,
        string $explanationEn,
        string $explanationMk,
        string $targetKey,
        string $targetLabelEn,
        string $targetLabelMk,
        int $x,
        int $y,
        string $targetType,
        array $answers,
    ): array {
        return [
            'question_en' => $questionEn,
            'question_mk' => $questionMk,
            'explanation_en' => $explanationEn,
            'explanation_mk' => $explanationMk,
            'metadata' => [
                'map_target_key' => $targetKey,
                'map_target_label_en' => $targetLabelEn,
                'map_target_label_mk' => $targetLabelMk,
                'map_x' => $x,
                'map_y' => $y,
                'target_type' => $targetType,
            ],
            'answers' => $answers,
        ];
    }
}
