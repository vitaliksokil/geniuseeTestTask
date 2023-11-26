<?php

namespace App\DTO\Casts\Movie\Rating;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;

class ValueRateToInt implements Castable
{
    public static function dataCastUsing(...$arguments): Cast
    {
        // "59/100" to 59
        // "69%" to 69
        // "8.4/10" to 84
        return new class implements Cast {
            public function cast(DataProperty $property, mixed $value, array $context): int {
                // "69%" to 69
                if (preg_match('~(\d+)\%~',$value,$matches)){
                    return $matches[1];
                }// "59/100" to 59 OR "8.4/10" to 84
                elseif (preg_match('~(\d+)\/(\d+)~', $value, $matches)
                 || preg_match('~(\d+(\.\d+)?)\/(\d+(\.\d+)?)~', $value, $matches)){
                    return $matches[1] / $matches[2] * 100;
                }
                // here we can add some Exception and add error handling,
                // but for time saving I did it like so
                return 0;
            }
        };
    }
}
