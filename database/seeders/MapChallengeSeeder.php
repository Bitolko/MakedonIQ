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

        $this->renameLegacyDemoQuiz();

        $quiz = $category->quizzes()->updateOrCreate(
            ['slug' => 'macedonia-map-challenge-demo'],
            [
                'lesson_id' => $lesson?->id,
                'title_en' => 'Macedonia Map Challenge Demo',
                'title_mk' => 'Демо: Карта на Македонија',
                'description_en' => 'Try a short demo map challenge with beginner-friendly places.',
                'description_mk' => 'Пробај краток демо предизвик со почетни места на картата.',
                'difficulty' => 'beginner',
                'estimated_minutes' => 5,
                'points_per_question' => 10,
                'is_published' => true,
                'is_demo' => true,
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

    private function renameLegacyDemoQuiz(): void
    {
        $legacyQuiz = Quiz::where('slug', 'macedonia-map-challenge')->first();

        if (! $legacyQuiz) {
            return;
        }

        if (Quiz::where('slug', 'macedonia-map-challenge-demo')->whereKeyNot($legacyQuiz->id)->exists()) {
            return;
        }

        $legacyQuiz->update(['slug' => 'macedonia-map-challenge-demo']);
    }

    private function questions(): array
    {
        return array_slice([
            $this->mapQuestion(
                'Which city is highlighted on the map?',
                'Кој град е означен на мапата?',
                'Skopje is the capital city of North Macedonia.',
                'Скопје е главниот град на Северна Македонија.',
                'skopje',
                'Skopje',
                'Скопје',
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
                'city',
                [
                    ['Kumanovo', 'Куманово', true],
                    ['Bitola', 'Битола', false],
                    ['Gevgelija', 'Гевгелија', false],
                    ['Gostivar', 'Гостивар', false],
                ],
            ),
        ], 0, 5);
    }

    private function mapQuestion(
        string $questionEn,
        string $questionMk,
        string $explanationEn,
        string $explanationMk,
        string $targetKey,
        string $targetLabelEn,
        string $targetLabelMk,
        string $targetType,
        array $answers,
    ): array {
        $coordinates = MapChallengeCoordinates::for($targetKey);

        return [
            'question_en' => $questionEn,
            'question_mk' => $questionMk,
            'explanation_en' => $explanationEn,
            'explanation_mk' => $explanationMk,
            'metadata' => [
                'map_target_key' => $targetKey,
                'map_target_label_en' => $targetLabelEn,
                'map_target_label_mk' => $targetLabelMk,
                'map_x' => $coordinates['x'],
                'map_y' => $coordinates['y'],
                'target_type' => $targetType,
            ],
            'answers' => $answers,
        ];
    }
}
