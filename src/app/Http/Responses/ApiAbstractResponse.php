<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

abstract class ApiAbstractResponse implements Responsable
{

    public function responseFromFields(): array {
        $vars = get_object_vars($this);
        $keys = array_keys($vars);

        return array_combine($keys, $vars);
    }
}
