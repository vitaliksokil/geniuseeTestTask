<?php

namespace App\Services\Movie;

use App\Enums\MovieType;

interface MovieServiceInterface
{
    public function syncMovies(string $search, MovieType $type, int $page): bool;
}
