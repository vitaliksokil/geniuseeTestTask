<?php

namespace App\DTO\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;

class StringMinsToInt implements Castable
{
    public static function dataCastUsing(...$arguments): Cast
    {
        return new class implements Cast {
            public function cast(DataProperty $property, mixed $value, array $context): int {
                return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            }
        };
    }
}
