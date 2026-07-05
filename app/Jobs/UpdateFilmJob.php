<?php

namespace App\Jobs;

use App\Models\Film;
use App\Services\MovieService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateFilmJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $imdbId IMDB идентификатор фильма
     */
    public function __construct(private string $imdbId)
    {
    }

    /**
     * Загружает данные о фильме из внешнего источника и обновляет запись в БД
     */
    public function handle(MovieService $movieService): void
    {
        $data = $movieService->getMovieInfo($this->imdbId);

        if (! $data || $data['Response'] === 'False') {
            return;
        }

        $film = Film::where('imdb_id', $this->imdbId)->first();

        if (! $film) {
            return;
        }

        $film->update([
            'name' => $data['Title'] ?? null,
            'description' => $data['Plot'] ?? null,
            'released' => isset($data['Year']) ? (int) $data['Year'] : null,
            'run_time' => isset($data['Runtime']) ? (int) $data['Runtime'] : null,
            'director' => isset($data['Director']) ? explode(', ', $data['Director']) : null,
            'starring' => isset($data['Actors']) ? explode(', ', $data['Actors']) : null,
            'status' => 'moderate',
        ]);
    }
}
