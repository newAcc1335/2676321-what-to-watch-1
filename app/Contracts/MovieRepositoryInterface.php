<?php

namespace App\Contracts;

interface MovieRepositoryInterface
{
    public function findByImdbId(string $imdbId): ?array;
}
