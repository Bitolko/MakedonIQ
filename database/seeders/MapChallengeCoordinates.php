<?php

namespace Database\Seeders;

final class MapChallengeCoordinates
{
    public static function for(string $key): array
    {
        return self::all()[$key] ?? ['x' => 50.0, 'y' => 50.0];
    }

    public static function all(): array
    {
        return [
            'skopje' => ['x' => 40.0, 'y' => 31.0],
            'ohrid' => ['x' => 20.5, 'y' => 66.0],
            'bitola' => ['x' => 37.0, 'y' => 69.8],
            'prilep' => ['x' => 45.0, 'y' => 57.0],
            'tetovo' => ['x' => 24.0, 'y' => 34.0],
            'kumanovo' => ['x' => 51.0, 'y' => 27.0],
            'strumica' => ['x' => 82.0, 'y' => 53.5],
            'veles' => ['x' => 52.0, 'y' => 45.0],
            'stip' => ['x' => 65.0, 'y' => 43.0],
            'gostivar' => ['x' => 22.5, 'y' => 42.5],
            'struga' => ['x' => 20.5, 'y' => 64.5],
            'kicevo' => ['x' => 35.5, 'y' => 55.5],
            'kavadarci' => ['x' => 54.5, 'y' => 69.5],
            'gevgelija' => ['x' => 57.5, 'y' => 79.0],
            'kocani' => ['x' => 75.5, 'y' => 46.5],
            'lake-ohrid' => ['x' => 17.0, 'y' => 69.0],
            'lake-prespa' => ['x' => 26.5, 'y' => 73.5],
            'lake-dojran' => ['x' => 83.5, 'y' => 61.5],
            'matka-canyon' => ['x' => 35.0, 'y' => 35.0],
            'krusevo' => ['x' => 36.0, 'y' => 55.5],
            'heraclea-lyncestis' => ['x' => 38.0, 'y' => 70.5],
            'kokino' => ['x' => 58.0, 'y' => 22.0],
            'old-bazaar-skopje' => ['x' => 39.5, 'y' => 31.0],
            'vodno' => ['x' => 50.5, 'y' => 40.0],
            'mavrovo' => ['x' => 17.5, 'y' => 44.0],
            'pelister' => ['x' => 34.0, 'y' => 72.0],
        ];
    }
}
