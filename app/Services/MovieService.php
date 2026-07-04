<?php

namespace App\Services;

use App\Contracts\MovieRepositoryInterface;

class MovieService
{
    public function __construct(private MovieRepositoryInterface $movieRepository) {}

    public function getMovieInfo(string $imdbId): ?array
    {
        return $this->movieRepository->findByImdbId($imdbId);
    }
}
