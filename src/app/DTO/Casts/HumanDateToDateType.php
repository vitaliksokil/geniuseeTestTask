<?php

namespace App\DTO\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;

class HumanDateToDateType implements Castable
{
    public static function dataCastUsing(...$arguments): Cast
    {

        // '05 May 2017' to 2017-05-05
        return new class implements Cast {
            public function cast(DataProperty $property, mixed $value, array $context): string {
                return date('Y-m-d', strtotime($value));
            }
        };
    }
}
