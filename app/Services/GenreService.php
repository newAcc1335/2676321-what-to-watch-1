<?php

namespace App\Services;

use App\Models\Genre;

class GenreService
{
    public function findOrCreateByNames(array $genres): array
    {
        return collect($genres)->map(function (string $name) {
            return Genre::firstOrCreate(['name' => $name])->id;
        })->toArray();
    }
}
