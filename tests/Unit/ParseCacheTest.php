<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Parse;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\RetrievingKey;
use Spatie\StructureDiscoverer\Cache\LaravelDiscoverCacheDriver;
use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\attributes\MethodAttribute;
use Tests\_fixtures\ClassWithAttribute;
use Tests\_fixtures\ClassWithAttributedMethods;

$dirs = [
    __DIR__ . '/../_fixtures',
];

it('should cache usage results', function () use ($dirs) {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(ClassAttribute::class)
        ->withCache(new LaravelDiscoverCacheDriver('laravel'));

    $parser->findUsages(...$dirs);

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($parser, $dirs) {
        expect($event->key)->toEndWith($parser->getCacheKey(...$dirs));

        return true;
    });
})->group('parse', 'parse::cache');

it('should cache class attributes results', function () {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(ClassAttribute::class)
        ->on(ClassWithAttribute::class)
        ->withCache(new LaravelDiscoverCacheDriver('laravel'));

    $key = $parser->getCacheKey();

    $parser->get();

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($key) {
        expect($event->key)->toEndWith($key);

        return true;
    });
})->group('parse', 'parse::cache');

it('should cache method attributes results', function () {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(MethodAttribute::class)
        ->on(ClassWithAttributedMethods::class)
        ->withCache(new LaravelDiscoverCacheDriver('laravel'));

    $key = $parser->getCacheKey();

    $parser->inMethods();

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($key) {
        expect($event->key)->toEndWith($key);

        return true;
    });
})->group('parse', 'parse::cache');

it('should cache all results', function () use ($dirs) {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(DescriptionAttribute::class)
        ->withCache(new LaravelDiscoverCacheDriver('laravel'));

    $key = $parser->getCacheKey(...$dirs);

    $parser->all(...$dirs);

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($key) {
        expect($event->key)->toEndWith($key);

        return true;
    });
})->group('parse', 'parse::cache');

it('should read from cache', function (
    $cacheIt,
    $runIt
) {
    $cacheIt();

    Event::fake([
        CacheHit::class,
        RetrievingKey::class,
    ]);

    $runIt();

    Event::assertDispatched(CacheHit::class);
    Event::assertDispatched(RetrievingKey::class);
})
    ->with([
        '`usage`'            => [
            fn() => (new LaravelDiscoverCacheDriver('laravel'))
                ->put(Parse::attribute(ClassAttribute::class)->getCacheKey(...$dirs), [ 'my' => 'data' ]),
            fn() => Parse::attribute(ClassAttribute::class)
                ->withCache(new LaravelDiscoverCacheDriver('laravel'))
                ->findUsages(...$dirs),
        ],
        '`based on class`'   => [
            fn() => (new LaravelDiscoverCacheDriver('laravel'))
                ->put(Parse::attribute(ClassAttribute::class)->on(ClassWithAttribute::class)->getCacheKey(), [
                    new Amondar\ClassAttributes\Results\DiscoveredResult('asd', []),
                ]),
            fn() => Parse::attribute(ClassAttribute::class)
                ->on(ClassWithAttribute::class)
                ->withCache(new LaravelDiscoverCacheDriver('laravel'))
                ->get(),
        ],
        '`based on methods`' => [
            fn() => (new LaravelDiscoverCacheDriver('laravel'))
                ->put(Parse::attribute(ClassAttribute::class)->on(ClassWithAttribute::class)->getCacheKey(), [
                    new Amondar\ClassAttributes\Results\DiscoveredResult('asd', []),
                ]),
            fn() => Parse::attribute(ClassAttribute::class)
                ->on(ClassWithAttribute::class)
                ->withCache(new LaravelDiscoverCacheDriver('laravel'))
                ->inMethods(),
        ],
        '`all`' => [
            fn() => (new LaravelDiscoverCacheDriver('laravel'))
                ->put(
                    Parse::attribute(ClassAttribute::class)
                        ->on(ClassWithAttribute::class)
                        ->getCacheKey(...$dirs),
                    []
                ),
            fn() => Parse::attribute(DescriptionAttribute::class)
                ->withCache(new LaravelDiscoverCacheDriver('laravel'))
                ->all(...$dirs),
        ],
    ])
    ->group('parse', 'parse::cache');

it('should return null on empty cache', function (
    $getParse,
    $parseIt
) {
    $parse = $getParse();

    (new LaravelDiscoverCacheDriver('laravel'))
        ->put($parse->getCacheKey(), []);

    expect($parseIt($parse))->toBeNull();
})->with([
    '`based on class`'   => [
        fn() => Parse::attribute(ClassAttribute::class)
            ->withCache(new LaravelDiscoverCacheDriver('laravel'))
            ->on(ClassWithAttribute::class),
        fn(Parse $parse) => $parse->get(),
    ],
    '`based on methods`' => [
        fn() => Parse::attribute(MethodAttribute::class)
            ->withCache(new LaravelDiscoverCacheDriver('laravel'))
            ->on(ClassWithAttributedMethods::class),
        fn(Parse $parse) => $parse->inMethods(),
    ],
])->group('parse', 'parse::cache');
