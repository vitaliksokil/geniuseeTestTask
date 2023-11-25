<?php

namespace App\Repositories\MoviesSource;

use App\DTO\Movie\MovieDto;
use App\Enums\MovieType;

interface MoviesSourceInterface
{
    public function getMovies(string $search, MovieType $type, int $page): array;

    public function getById(string $id): MovieDto;
}
