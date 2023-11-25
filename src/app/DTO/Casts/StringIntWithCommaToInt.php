<?php

namespace App\DTO\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;

class StringIntWithCommaToInt implements Castable
{
    public static function dataCastUsing(...$arguments): Cast
    {
        return new class implements Cast {
            public function cast(DataProperty $property, mixed $value, array $context): int {
                return (int)str_replace(',', '', $value);
            }
        };
    }
}
