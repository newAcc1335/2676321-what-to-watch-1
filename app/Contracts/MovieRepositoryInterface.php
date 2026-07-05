<?php

namespace App\Contracts;

interface MovieRepositoryInterface
{
    /**
     * Возвращает данные о фильме по IMDB ID.
     *
     * @param string $imdbId IMDB идентификатор фильма
     * @return array|null данные о фильме или null если не найден
     */
    public function findByImdbId(string $imdbId): ?array;
}
