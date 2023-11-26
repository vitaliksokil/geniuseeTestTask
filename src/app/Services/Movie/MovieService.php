<?php

namespace App\Services\Movie;

use App\DTO\Casts\Movie\Rating\RatingsArrToRatingsDto;
use App\DTO\Movie\MovieDto;
use App\Enums\MovieType;
use App\Repositories\Movie\MovieRepositoryInterface;
use App\Repositories\Movie\Rating\RatingRepositoryInterface;
use App\Repositories\MoviesSource\MoviesSourceInterface;
use Illuminate\Support\Facades\DB;

class MovieService implements MovieServiceInterface
{
    public function __construct(
        private readonly MoviesSourceInterface $moviesSourceRep,
        private readonly MovieRepositoryInterface $movieRepository,
        private readonly RatingRepositoryInterface $ratingRepository
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
            $ratings = [];
            foreach ($data as $movie){
                // collecting all data we need
                if (isset($movie['imdbID'])){
                    // prepare data for updating in DB , creating DTO that will match for our model
                    $movieData = $this->moviesSourceRep->getById($movie['imdbID']);
                    $movieDto = MovieDto::from($movieData);
                    $ratingsDto = RatingsArrToRatingsDto::cast($movieData['Ratings'], $movieDto->imdb_id);

                    $updData[] = $movieDto->toArray();
                    $ratings = [...$ratings, ...$ratingsDto]; // merging it so it will be 1D array with DTOs, and not 2d
                }
            }
            return DB::transaction(function() use($ratings, $updData){
                return $this->movieRepository->createOrUpdateMultiple($updData)
                    && $this->ratingRepository->createOrUpdateMultiple($ratings);
            });
        }catch (\Exception $exception) {
            dd($exception->getMessage());
            // returning false as a result of endpoint if something went wrong
            // it's possible to add throwing of exceptions for each error
            return false;
        }
    }
}
