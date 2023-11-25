<?php

namespace App\Repositories\Movie;

interface MovieRepositoryInterface
{
    public function createOrUpdate(array $movies): bool;
}
