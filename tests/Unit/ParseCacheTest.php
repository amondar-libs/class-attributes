<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Enums\Target;
use Amondar\ClassAttributes\Laravel\AttributeCacheDriver;
use Amondar\ClassAttributes\Parse;
use Amondar\ClassAttributes\Results\Discovered;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\RetrievingKey;
use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\attributes\MethodAttribute;
use Tests\_fixtures\DefaultClass;

$dirs = [
    __DIR__ . '/../_fixtures',
];

afterEach(fn() => (new AttributeCacheDriver('laravel', 'redis'))->flush());

it('should cache usage results', function () use ($dirs) {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(ClassAttribute::class)
        ->withCache(new AttributeCacheDriver('laravel', 'redis'));

    $parser->findUsages(...$dirs);

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($parser, $dirs) {
        expect($event->key)->toEndWith($parser->getCacheKey(...$dirs));

        return true;
    });
});

it('should cache class attributes results', function () {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(ClassAttribute::class)
        ->on(DefaultClass::class)
        ->withCache(new AttributeCacheDriver('laravel', 'redis'));

    $key = $parser->getCacheKey();

    $parser->get();

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($key) {
        expect($event->key)->toEndWith($key);

        return true;
    });
});

it('should cache method attributes results', function () {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(MethodAttribute::class)
        ->on(DefaultClass::class)
        ->withCache(new AttributeCacheDriver('laravel'));

    $key = $parser->getCacheKey();

    $parser->onMethods();

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($key) {
        expect($event->key)->toEndWith($key);

        return true;
    });
});

it('should cache all results', function () use ($dirs) {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(DescriptionAttribute::class)
        ->withCache(new AttributeCacheDriver('laravel'));

    $parser->in(...$dirs);

    Event::assertDispatchedTimes(CacheMissed::class, 4);
    Event::assertDispatchedTimes(KeyWritten::class, 4);
});

it('should read from cache', function (
    $cacheIt,
    $runIt,
) {
    $cacheIt();

    Event::fake([
        CacheHit::class,
        RetrievingKey::class,
    ]);

    Event::assertNothingDispatched();

    $runIt();

    // 2 bacause of tags.
    Event::assertDispatchedTimes(CacheHit::class, 2);
    Event::assertDispatchedTimes(RetrievingKey::class, 2);
})
    ->with([
        '`usage`'            => [
            fn() => (new AttributeCacheDriver('laravel'))
                ->put(
                    Parse::attribute(ClassAttribute::class)->getCacheKey(...$dirs),
                    [
                        new Discovered(
                            name: DefaultClass::class,
                            parent: null,
                            attribute: new ClassAttribute([ 'some' => 'data' ]),
                            target: Target::onClass
                        ),
                    ]
                ),
            fn() => Parse::attribute(ClassAttribute::class)
                ->withCache(new AttributeCacheDriver('laravel'))
                ->findUsages(...$dirs),
        ],
        '`based on class`'   => [
            fn() => (new AttributeCacheDriver('laravel'))
                ->put(
                    Parse::attribute(ClassAttribute::class)->on(DefaultClass::class)->getCacheKey(),
                    [
                        new Discovered(
                            name: DefaultClass::class,
                            parent: null,
                            attribute: new ClassAttribute([ 'some' => 'data' ]),
                            target: Target::onClass
                        ),
                    ]
                ),
            fn() => Parse::attribute(ClassAttribute::class)
                ->on(DefaultClass::class)
                ->withCache(new AttributeCacheDriver('laravel'))
                ->get(),
        ],
        '`all`' => [
            fn() => (new AttributeCacheDriver('laravel'))
                ->put(
                    Parse::attribute(DescriptionAttribute::class)->getCacheKey(...$dirs),
                    [
                        DefaultClass::class => [
                            'class' => [
                                new Discovered(
                                    name: DefaultClass::class,
                                    parent: null,
                                    attribute: new DescriptionAttribute('Class with attributed methods'),
                                    target: Target::onClass
                                ),
                            ],
                            'method' => [
                                new Discovered(
                                    name: 'myMethod',
                                    parent: DefaultClass::class,
                                    attribute: new DescriptionAttribute('My method'),
                                    target: Target::method,
                                ),
                                new Discovered(
                                    name: 'postsList',
                                    parent: DefaultClass::class,
                                    attribute: new DescriptionAttribute('Posts list method'),
                                    target: Target::method,
                                ),
                            ],
                        ],
                    ]
                ),
            fn() => Parse::attribute(DescriptionAttribute::class)
                ->withCache(new AttributeCacheDriver('laravel'))
                ->in(...$dirs),
        ],
    ]);
