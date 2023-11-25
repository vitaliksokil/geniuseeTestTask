<?php


namespace App\Services\AdditionalServices\CodeGeneratorService;


class CodeGeneratorService implements CodeGeneratorServiceInterface
{
    public function generateCode(int $length = 4)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
