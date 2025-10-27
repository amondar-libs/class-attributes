<?php

declare( strict_types = 1 );

namespace Tests;

use Amondar\ClassAttributes\ServiceProvider;
use Closure;
use Illuminate\Foundation\Testing\WithFaker;
use JetBrains\PhpStorm\NoReturn;
use Orchestra\Testbench\TestCase as CoreTestCase;

abstract class TestCase extends CoreTestCase
{
    use WithFaker;

    protected string $appURL;

    protected function setUp() : void
    {
        parent::setUp();

        $this->appURL = $this->app[ 'config' ]->get('app.url');
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    #[NoReturn]
    protected function runInBenchmarking(Closure $closure) : void
    {
        $start = microtime(true);
        $closure();
        $end = microtime(true);

        dd(( $end - $start ) * 1000);
    }

}
