<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MakesGraphQLRequests;
    use RefreshesSchemaCache;

    protected $headers = [];

    /**
     * If true, setup has run at least once.
     * @var bool
     */
    protected static $setUpHasRunOnce = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
//            Artisan::call(
//                'db:seed', ['--class' => 'DatabaseSeeder']
//            );
            static::$setUpHasRunOnce = true;
        }

        $this->headers['Accept'] = 'application/json';
    }

    const WITHOUT_MIDDLEWARES = [
    ];
}
