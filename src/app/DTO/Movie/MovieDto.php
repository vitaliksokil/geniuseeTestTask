<?php

namespace App\DTO\Movie;

use App\DTO\AbstractDto;
use App\DTO\Casts\HumanDateToDateType;
use App\DTO\Casts\Movie\Rating\RatingsArrToRatingsDto;
use App\DTO\Casts\StringIntWithCommaToInt;
use App\DTO\Casts\StringMinsToInt;
use App\DTO\Casts\StringToFloat;
use App\Enums\MovieType;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCastable;

class MovieDto extends AbstractDto
{
    #[MapInputName('imdbID')]
    public string $imdb_id;
    #[MapInputName('Title')]
    public string $title;
    #[MapInputName('Type')]
    public MovieType $type;
    #[MapInputName('Released'), WithCastable(HumanDateToDateType::class)]
    public string $release_date;
    #[MapInputName('Year')]
    public int $year;
    #[MapInputName('Poster')]
    public string $poster_url;
    #[MapInputName('Genre')]
    public string $genre;
    #[MapInputName('Runtime'), WithCastable(StringMinsToInt::class)]
    public int $runtime;
    #[MapInputName('Country')]
    public string $country;
    #[MapInputName('imdbRating'), WithCastable(StringToFloat::class)]
    public float $imdb_rating;
    #[MapInputName('imdbVotes'), WithCastable(StringIntWithCommaToInt::class)]
    public int $imdb_votes;
}
