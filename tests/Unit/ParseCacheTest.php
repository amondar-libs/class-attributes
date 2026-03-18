<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Enums\Target;
use Amondar\ClassAttributes\Laravel\AttributesCacheDriver;
use Amondar\ClassAttributes\Parse;
use Amondar\ClassAttributes\Results\Discovered;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\RetrievingKey;
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
        ->withCache(new AttributesCacheDriver('laravel', 'redis'));

    $parser->findUsages(...$dirs);

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($parser, $dirs) {
        expect($event->key)->toEndWith($parser->getCacheKey(...$dirs));

        return true;
    });
})
    ->group('parse', 'parse::cache')
    ->after(fn() => cache()->store('redis')->tags(AttributesCacheDriver::TAGS)->flush());

it('should cache class attributes results', function () {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(ClassAttribute::class)
        ->on(ClassWithAttribute::class)
        ->withCache(new AttributesCacheDriver('laravel', 'redis'));

    $key = $parser->getCacheKey();

    $parser->get();

    Event::assertDispatched(CacheMissed::class);
    Event::assertDispatched(KeyWritten::class, function (KeyWritten $event) use ($key) {
        expect($event->key)->toEndWith($key);

        return true;
    });
})
    ->group('parse', 'parse::cache')
    ->after(fn() => cache()->store('redis')->tags(AttributesCacheDriver::TAGS)->flush());

it('should cache method attributes results', function () {
    Event::fake([
        CacheMissed::class,
        KeyWritten::class,
    ]);

    $parser = Parse::attribute(MethodAttribute::class)
        ->on(ClassWithAttributedMethods::class)
        ->withCache(new AttributesCacheDriver('laravel'));

    $key = $parser->getCacheKey();

    $parser->onMethods();

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
        ->withCache(new AttributesCacheDriver('laravel'));

    $parser->in(...$dirs);

    Event::assertDispatchedTimes(CacheMissed::class, 3);
    Event::assertDispatchedTimes(KeyWritten::class, 3);
})->group('parse', 'parse::cache');

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
            fn() => (new AttributesCacheDriver('laravel'))
                ->put(
                    Parse::attribute(ClassAttribute::class)->getCacheKey(...$dirs),
                    [
                        new Discovered(
                            name: ClassWithAttribute::class,
                            parent: null,
                            attribute: new ClassAttribute([ 'some' => 'data' ]),
                            target: Target::onClass
                        ),
                    ]
                ),
            fn() => Parse::attribute(ClassAttribute::class)
                ->withCache(new AttributesCacheDriver('laravel'))
                ->findUsages(...$dirs),
        ],
        '`based on class`'   => [
            fn() => (new AttributesCacheDriver('laravel'))
                ->put(
                    Parse::attribute(ClassAttribute::class)->on(ClassWithAttribute::class)->getCacheKey(),
                    [
                        new Discovered(
                            name: ClassWithAttribute::class,
                            parent: null,
                            attribute: new ClassAttribute([ 'some' => 'data' ]),
                            target: Target::onClass
                        ),
                    ]
                ),
            fn() => Parse::attribute(ClassAttribute::class)
                ->on(ClassWithAttribute::class)
                ->withCache(new AttributesCacheDriver('laravel'))
                ->get(),
        ],
        //        '`based on methods`' => [
        //            fn() => (new AttributesCacheDriver('laravel'))
        //                ->put(Parse::attribute(ClassAttribute::class)->on(ClassWithAttribute::class)->getCacheKey(), [
        //                    new Amondar\ClassAttributes\Results\DiscoveredResult('asd', []),
        //                ]),
        //            fn() => Parse::attribute(ClassAttribute::class)
        //                ->on(ClassWithAttribute::class)
        //                ->withCache(new AttributesCacheDriver('laravel'))
        //                ->onMethods(),
        //        ],
        //        '`all`' => [
        //            fn() => (new AttributesCacheDriver('laravel'))
        //                ->put(
        //                    Parse::attribute(ClassAttribute::class)
        //                        ->on(ClassWithAttribute::class)
        //                        ->getCacheKey(...$dirs),
        //                    []
        //                ),
        //            fn() => Parse::attribute(DescriptionAttribute::class)
        //                ->withCache(new AttributesCacheDriver('laravel'))
        //                ->all(...$dirs),
        //        ],
    ])
    ->group('parse', 'parse::cache');
//
// it('should return null on empty cache', function (
//    $getParse,
//    $parseIt
// ) {
//    $parse = $getParse();
//
//    (new AttributesCacheDriver('laravel'))
//        ->put($parse->getCacheKey(), []);
//
//    expect($parseIt($parse))->toBeNull();
// })->with([
//    '`based on class`'   => [
//        fn() => Parse::attribute(ClassAttribute::class)
//            ->withCache(new AttributesCacheDriver('laravel'))
//            ->on(ClassWithAttribute::class),
//        fn(Parse $parse) => $parse->get(),
//    ],
//    '`based on methods`' => [
//        fn() => Parse::attribute(MethodAttribute::class)
//            ->withCache(new AttributesCacheDriver('laravel'))
//            ->on(ClassWithAttributedMethods::class),
//        fn(Parse $parse) => $parse->onMethods(),
//    ],
// ])->group('parse', 'parse::cache');
