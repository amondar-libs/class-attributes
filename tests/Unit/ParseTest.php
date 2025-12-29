<?php

declare(strict_types = 1);

use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\ClassAttributeRepeatable;
use Tests\_fixtures\attributes\MethodAttribute;
use Tests\_fixtures\attributes\RepeatableMethodAttribute;
use Tests\_fixtures\ClassExtendsRepeatableAttributed;
use Tests\_fixtures\ClassWithAttribute;
use Tests\_fixtures\ClassWithAttributedMethods;

beforeEach(fn() => Event::fake([
    CacheMissed::class,
    KeyWritten::class,
]));

afterEach(fn() => Event::assertNothingDispatched());

it('should parse usages properly `without ascend`', function () {
    $result = Amondar\ClassAttributes\Parse::attribute(ClassAttribute::class)
        ->findUsages(__DIR__ . '/../_fixtures');

    expect($result)->toHaveCount(1)
        ->and($result[ 0 ]->name)->toBe('ClassWithAttribute');
})->group('parse');

it('should parse usages properly `with ascend`', function () {
    $result = Amondar\ClassAttributes\Parse::attribute(ClassAttribute::class)
        ->ascend()
        ->findUsages(__DIR__ . '/../_fixtures');

    expect($result)->toHaveCount(2)
        ->and($result[ 0 ]->name)->toBe('ClassExtendsAttributed')
        ->and($result[ 1 ]->name)->toBe('ClassWithAttribute');
})->group('parse');

it('should parse class attributes properly', function () {
    $result = Amondar\ClassAttributes\Parse::attribute(ClassAttribute::class)
        ->on(ClassWithAttribute::class)
        ->get();

    expect($result)->not->toBeNull()
        ->and($result)->toEqual(
            new Amondar\ClassAttributes\Results\DiscoveredResult(
                ClassWithAttribute::class,
                [
                    new ClassAttribute([ 'some' => 'data' ]),
                ]
            )
        );
})->group('parse');

it('should parse extended class attributes properly', function () {
    $result = Amondar\ClassAttributes\Parse::attribute(ClassAttributeRepeatable::class)
        ->on(ClassExtendsRepeatableAttributed::class)
        ->get();

    expect($result)->toBeNull();

    $result = Amondar\ClassAttributes\Parse::attribute(ClassAttributeRepeatable::class)
        ->on(ClassExtendsRepeatableAttributed::class)
        ->ascend()
        ->get();

    expect($result)->not->toBeNull()
        ->and($result)->toEqual(
            new Amondar\ClassAttributes\Results\DiscoveredResult(
                ClassExtendsRepeatableAttributed::class,
                [
                    new ClassAttributeRepeatable([ 'some' => 'data' ]),
                    new ClassAttributeRepeatable([ 'duplicated' => 'data' ]),
                ]
            )
        );
})->group('parse');

it('should parse common method attribute properly', function () {
    $result = Amondar\ClassAttributes\Parse::attribute(MethodAttribute::class)
        ->on(ClassWithAttributedMethods::class)
        ->inMethods();

    expect($result)->not->toBeNull()
        ->and($result)->toEqual(
            new Amondar\ClassAttributes\Results\DiscoveredResult(
                ClassWithAttributedMethods::class,
                [
                    new Amondar\ClassAttributes\Results\DiscoveredMethod(
                        'myMethod',
                        [
                            new MethodAttribute('My method description'),
                        ]
                    ),
                ]
            )
        );
})->group('parse');

it('should parse repeatable method attribute properly', function () {
    $result = Amondar\ClassAttributes\Parse::attribute(RepeatableMethodAttribute::class)
        ->on(ClassWithAttributedMethods::class)
        ->inMethods();

    expect($result)->not->toBeNull()
        ->and($result)->toEqual(
            new Amondar\ClassAttributes\Results\DiscoveredResult(
                ClassWithAttributedMethods::class,
                [
                    new Amondar\ClassAttributes\Results\DiscoveredMethod(
                        'usersList',
                        [
                            new RepeatableMethodAttribute('/users'),
                            new RepeatableMethodAttribute('/api/v1/users'),
                        ]
                    ),
                    new Amondar\ClassAttributes\Results\DiscoveredMethod(
                        'postsList',
                        [
                            new RepeatableMethodAttribute('/posts'),
                            new RepeatableMethodAttribute('/api/v1/posts'),
                        ]
                    ),
                ]
            )
        );
})->group('parse');
