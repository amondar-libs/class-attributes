<?php

declare(strict_types = 1);

namespace Tests;

use Closure;
use Illuminate\Foundation\Testing\WithFaker;
use JetBrains\PhpStorm\NoReturn;
use Orchestra\Testbench\TestCase as CoreTestCase;

abstract class TestCase extends CoreTestCase
{
    use WithFaker;

    protected function getPackageProviders($app)
    {
        return [

        ];
    }

    #[NoReturn]
    protected function runInBenchmarking(Closure $closure): void
    {
        $start = microtime(true);
        $closure();
        $end = microtime(true);

        dd(($end - $start) * 1000);
    }
}
