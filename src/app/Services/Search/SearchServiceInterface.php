<?php

namespace App\Services\Search;

interface SearchServiceInterface
{

    public function searchList(array $data);

    public function searchOnMap(array $data);

}
