<?php

namespace App\DTO\Casts\Movie\Rating;

use App\DTO\Movie\RatingDto;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;

class RatingsArrToRatingsDto implements Castable
{
    public static function dataCastUsing(...$arguments): Cast
    {
        // "Ratings": [
        //        {
        //            "Source": "Internet Movie Database",
        //            "Value": "7.6/10"
        //        },
        //        {
        //            "Source": "Rotten Tomatoes",
        //            "Value": "85%"
        //        },
        //        {
        //            "Source": "Metacritic",
        //            "Value": "67/100"
        //        }
        //    ],
        // It creates from the array above DTO objects for Rating
        return new class implements Cast {
            public function cast(DataProperty $property, mixed $value, array $context): array {
                return RatingsArrToRatingsDto::cast($value, $context['imdbID']);
            }
        };
    }

    public static function cast(mixed $value, string $imdbId): array
    {
        $res = [];
        foreach ($value as $rating){
            $res[] = RatingDto::from(array_merge($rating,['imdbID'=>$imdbId]));
        }
        return $res;
    }
}
