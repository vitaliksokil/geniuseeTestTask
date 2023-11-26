<?php

namespace App\DTO\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;

class StringToFloat implements Castable
{
    public static function dataCastUsing(...$arguments): Cast
    {
        // '136 min' to 136
        return new class implements Cast {
            public function cast(DataProperty $property, mixed $value, array $context): float {
                try {
                    return (float)$value;
                }catch (\Exception $exception){
                    return 0.0;
                }
            }
        };
    }
}
