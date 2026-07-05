<?php

namespace App\Services;

use App\Contracts\MovieRepositoryInterface;

class MovieService
{
    public function __construct(private readonly MovieRepositoryInterface $movieRepository)
    {
    }

    /**
     * Возвращает информацию о фильме по IMDB ID.
     *
     * @param  string  $imdbId  IMDB идентификатор фильма
     * @return array|null данные о фильме или null если не найден
     */
    public function getMovieInfo(string $imdbId): ?array
    {
        return $this->movieRepository->findByImdbId($imdbId);
    }
}
