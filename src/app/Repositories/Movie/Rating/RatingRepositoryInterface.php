<?php

namespace App\Repositories\Movie\Rating;

interface RatingRepositoryInterface
{
    public function createOrUpdateMultiple(array $ratings): bool;
}
