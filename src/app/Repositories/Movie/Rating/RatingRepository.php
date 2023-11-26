<?php

namespace App\Repositories\Movie\Rating;

use App\Models\Rating;

class RatingRepository implements RatingRepositoryInterface
{

    public function createOrUpdateMultiple(array $ratings): bool
    {
        try {
            // here it's made by updateOrCreate method and with foreach,
            // because 'upsert' requires the 2nd param to be 'primary' or 'unique' in DB, and that's not about this case
            foreach ($ratings as $rating){
                /* @var \App\DTO\Movie\RatingDto $rating */
                Rating::updateOrCreate(
                    ['imdb_id' => $rating->imdb_id, 'source' => $rating->source],
                    ['value' => $rating->value]
                );
            }
            return true;
        }catch(\Exception $exception){
            return false;
        }
    }
}
