<?php

namespace App\DTO\Movie;

use App\DTO\AbstractDto;
use App\DTO\Casts\Movie\Rating\ValueRateToInt;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCastable;

class RatingDto extends AbstractDto
{
    #[MapInputName('Source')]
    public string $source;
    #[MapInputName('Value'), WithCastable(ValueRateToInt::class)]
    public int $value;
    #[MapInputName('imdbID')]
    public string $imdb_id;
}
