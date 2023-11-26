<?php

namespace App\Enums;

use App\Enums\Traits\EnumFromUpperCaseValue;
use App\Enums\Traits\EnumValues;

enum MovieType: string
{
    use EnumValues, EnumFromUpperCaseValue;

    case Movie = 'movie';
    case Series = 'series';
    case Episode = 'episode';


}
