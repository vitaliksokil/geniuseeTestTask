<?php

namespace App\Services;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseService
{
    use AuthorizesRequests;

    /**
     * @throws HttpResponseException|ValidationException
     */
    protected function validate(array &$data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules,$messages);
        if ($validator->passes()) {
            $data = $validator->validate();
            return $data;
        } else {
            $errors = (new ValidationException($validator))->errors();
            $this->throwValidationError($errors);
        }
    }

    protected function throwValidationError($errors)
    {
        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
