<?php

namespace App\Providers;

use App\Broadcasting\Messages\SnsChannel;
use App\Broadcasting\Messages\TwilioChannel;
use Aws\Credentials\Credentials;
use Aws\Sns\SnsClient;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
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
