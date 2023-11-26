<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\MovieType;
use App\Services\Movie\MovieServiceInterface;

final class SyncMovies
{
    public function __construct(private readonly MovieServiceInterface $movieService)
    {
    }

    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        return $this->movieService->syncMovies(
            $args['search'],
            MovieType::fromUpperCaseValue($args['type']),
            $args['page']
        );
    }
}
