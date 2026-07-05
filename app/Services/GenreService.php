<?php

namespace App\Services;

use App\Models\Genre;

class GenreService
{
    /**
     * Находит или создаёт жанры по именам и возвращает их id
     *
     * @param array<string> $genres массив с названиями жанров
     * @return array<int> массив id жанров
     */
    public function findOrCreateByNames(array $genres): array
    {
        return collect($genres)->map(function (string $name) {
            return Genre::firstOrCreate(['name' => $name])->id;
        })->toArray();
    }
}
