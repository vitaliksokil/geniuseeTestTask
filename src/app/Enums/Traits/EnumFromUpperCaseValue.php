<?php

namespace App\Enums\Traits;

trait EnumFromUpperCaseValue
{
    static function fromUpperCaseValue(string $upperCaseValue): self {
        return self::from(strtolower($upperCaseValue));
    }
}
