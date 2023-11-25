<?php

namespace App\Exceptions\API;

use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseException extends HttpResponseException
{
    protected $message;
    protected $status;

    public function __construct()
    {
        $message = __($this->message);
        $status = $this->status;
        $response = response()->json(['message' => $message],$status);
        parent::__construct($response);
    }
}

