<?php

namespace App\Repositories\Movie;

interface MovieRepositoryInterface
{

    public function createOrUpdateMultiple(array $movies): bool;
}
