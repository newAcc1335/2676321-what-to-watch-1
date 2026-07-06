<?php

namespace App\Jobs;

use App\Models\Film;
use App\Services\MovieService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\GenreService;
use Illuminate\Queue\Middleware\RateLimited;

class UpdateFilmJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $imdbId  IMDB идентификатор фильма
     */
    public function __construct(private string $imdbId)
    {
    }

    /**
     * Загружает данные о фильме из внешнего источника и обновляет запись в БД
     */
    public function handle(MovieService $movieService, GenreService $genreService): void
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
            'name' => $this->valueOrNull($data, 'Title'),
            'description' => $this->valueOrNull($data, 'Plot'),
            'poster_image' => $this->valueOrNull($data, 'Poster'),
            'released' => (int) $this->valueOrNull($data, 'Year') ?: null,
            'run_time' => (int) $this->valueOrNull($data, 'Runtime') ?: null,
            'director' => $this->explodeOrNull($data, 'Director'),
            'starring' => $this->explodeOrNull($data, 'Actors'),
            'status' => 'moderate',
        ]);

        $genres = $this->explodeOrNull($data, 'Genre');

        if ($genres !== null) {
            $genreIds = $genreService->findOrCreateByNames($genres);
            $film->genres()->sync($genreIds);
        }
    }

    /**
     * Возвращает значение поля или null, если оно отсутствует или равно "N/A".
     *
     * @param  array  $data  данные фильма из внешнего источника
     * @param  string  $key  ключ поля
     */
    private function valueOrNull(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return $value === 'N/A' ? null : $value;
    }

    /**
     * Разбивает значение поля по запятой в массив или возвращает null.
     *
     * @param  array  $data  данные фильма из внешнего источника
     * @param  string  $key  ключ поля
     * @return array|null
     */
    private function explodeOrNull(array $data, string $key): ?array
    {
        $value = $this->valueOrNull($data, $key);

        return $value === null ? null : explode(', ', $value);
    }

    /**
     * Ограничивает интенсивность обращений к внешнему API
     */
    public function middleware(): array
    {
        return [new RateLimited('omdb')];
    }
}
