<?php

namespace App\Repositories\Movie;

use App\Models\Movie;

class MovieRepository implements MovieRepositoryInterface
{

    public function createOrUpdate(array $movies): bool
    {
        return Movie::upsert($movies, ['imdb_id']);
    }
}