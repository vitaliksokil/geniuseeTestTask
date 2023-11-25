<?php

namespace App\Providers;

use App\Repositories\Movie\MovieRepository;
use App\Repositories\Movie\MovieRepositoryInterface;
use App\Repositories\MoviesSource\MoviesSourceInterface;
use App\Repositories\MoviesSource\OmdbRepository;
use App\Services\Movie\MovieService;
use App\Services\Movie\MovieServiceInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // added bind in this provider and not created new one because there are no a lot of classes,
        // so currently for that small project it has no sense for splitting
        $this->app->bind(MovieServiceInterface::class, MovieService::class);
        $this->app->bind(MoviesSourceInterface::class, OmdbRepository::class);
        $this->app->bind(MovieRepositoryInterface::class, MovieRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app->environment('prod') || $this->app->environment('dev') || $this->app->environment('release')) {
            URL::forceScheme('https');
        }
    }
}
