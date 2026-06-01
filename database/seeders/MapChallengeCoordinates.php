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
            'skopje' => ['x' => 52.0, 'y' => 35.5],
            'ohrid' => ['x' => 23.5, 'y' => 67.0],
            'bitola' => ['x' => 40.0, 'y' => 74.0],
            'prilep' => ['x' => 48.0, 'y' => 64.0],
            'tetovo' => ['x' => 35.0, 'y' => 35.5],
            'kumanovo' => ['x' => 61.0, 'y' => 29.5],
            'strumica' => ['x' => 78.5, 'y' => 67.5],
            'veles' => ['x' => 56.5, 'y' => 54.0],
            'stip' => ['x' => 68.5, 'y' => 52.5],
            'gostivar' => ['x' => 31.5, 'y' => 43.5],
            'struga' => ['x' => 20.5, 'y' => 64.5],
            'kicevo' => ['x' => 35.5, 'y' => 55.5],
            'kavadarci' => ['x' => 54.5, 'y' => 69.5],
            'gevgelija' => ['x' => 57.5, 'y' => 79.0],
            'kocani' => ['x' => 75.5, 'y' => 46.5],
            'lake-ohrid' => ['x' => 15.0, 'y' => 68.0],
            'lake-prespa' => ['x' => 27.5, 'y' => 76.0],
            'matka-canyon' => ['x' => 45.5, 'y' => 38.5],
            'vodno' => ['x' => 50.5, 'y' => 40.0],
            'mavrovo' => ['x' => 26.0, 'y' => 41.5],
            'pelister' => ['x' => 38.5, 'y' => 78.0],
        ];
    }
}
