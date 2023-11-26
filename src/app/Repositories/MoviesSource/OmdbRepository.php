<?php

namespace App\Repositories\MoviesSource;

use App\Enums\MovieType;
use Illuminate\Support\Facades\Http;

class OmdbRepository implements MoviesSourceInterface
{
    private string $endpoint;
    private array $params;

    public function __construct()
    {
        $this->endpoint = config('omdb.endpoint');
        $this->params['apikey'] = config('omdb.api_key');
    }



    public function getMovies(string $search, MovieType $type, int $page): array
    {
        $data = Http::get($this->buildEndpoint([
            's' => $search,
            'type' => $type->value,
            'page' => $page,
        ]))->json();

        return $this->response($data);
    }

    public function getById(string $id): array
    {
        return $this->response(Http::get($this->buildEndpoint([
            'i' => $id,
        ]))->json());
    }

    private function buildEndpoint(array $params): string
    {
        return "$this->endpoint?" . http_build_query(array_merge($this->params, $params));
    }

    // This response probably would be better to do as its own file,
    // but I did it like private function just to save time.
    // Also it would be nice to add some error handling, but there is nothing in requirements about it,
    // so I've chosen the fastest option
    private function response(array $data): array
    {
        if (isset($data['Response']) && ($data['Response'] === 'True')) {
            // for collection return everything inside 'Search' and for single - return just data
            return $data['Search'] ?? $data;
        }else{
            return [];
        }
    }
}
