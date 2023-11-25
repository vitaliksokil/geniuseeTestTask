<?php

namespace App\Services\Movie;

use App\Enums\MovieType;
use App\Repositories\Movie\MovieRepositoryInterface;
use App\Repositories\MoviesSource\MoviesSourceInterface;

class MovieService implements MovieServiceInterface
{
    public function __construct(
        private readonly MoviesSourceInterface $moviesSourceRep,
        private readonly MovieRepositoryInterface $movieRepository
    )
    {
    }

    public function syncMovies(string $search, MovieType $type, int $page): bool
    {
        try {
            $data = $this->moviesSourceRep->getMovies(
                $search,
                $type,
                $page
            );
            // the result returns only a brief info about the movie, but we need all,
            // and there is no other option how to make one more request to retrieve the whole data
            $updData = [];
            foreach ($data as $movie){
                // collecting all data we need
                if (isset($movie['imdbID'])){
                    // prepare data for updating in DB , creating DTO that will match for our model
                    $updData[] = $this->moviesSourceRep->getById($movie['imdbID'])->toArray();
                    // todo add reviews
                }
            }
            return $this->movieRepository->createOrUpdate($updData);
        }catch (\Exception $exception) {
            // returning false as a result of endpoint if something went wrong
            return false;
        }
    }
}
