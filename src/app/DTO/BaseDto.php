<?php

namespace App\DTO;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseDto {

    protected Validator $validator;

    abstract protected function rules() : array;
    abstract protected function messages() : array;

    public function __construct(array $data, $doValidate = true)
    {

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        if ($doValidate){
            $this->validate();
        }
    }

    public function validate()
    {
        $validator = Validator::make(get_object_vars($this), $this->rules(), $this->messages());

        if ($validator->fails()) {
            $errors = (new ValidationException($validator))->errors();
            $this->throwValidationError($errors);
        } else {
            return $this;
        }
    }

    protected function throwValidationError($errors)
    {
        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    public function set(string $key, mixed $value) {
        $this->{$key} = $value;
    }

    public function get(string $key) {
        if(isset($this->{$key})) return $this->{$key};
    }

    public function toArray()
    {
        $data = get_object_vars($this);
        unset($data['_method']);

        return $data;
    }

    public function toObject()
    {
        $data = get_object_vars($this);
        unset($data['_method']);
        return (object)get_object_vars($data);
    }

}
