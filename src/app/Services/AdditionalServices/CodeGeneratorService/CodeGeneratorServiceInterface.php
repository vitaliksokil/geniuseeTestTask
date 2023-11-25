<?php


namespace App\Services\AdditionalServices\CodeGeneratorService;


interface CodeGeneratorServiceInterface
{
    public function generateCode(int $length = 4);
}
