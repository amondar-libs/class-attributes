<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Enums\Target;
use Amondar\ClassAttributes\Parse;
use Amondar\ClassAttributes\Results\Discovered;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\MethodAttribute;
use Tests\_fixtures\attributes\RepeatableMethodAttribute;
use Tests\_fixtures\attributes\TagAttribute;
use Tests\_fixtures\ChildDefaultClass;
use Tests\_fixtures\DefaultClass;

beforeEach(fn() => Event::fake([
    CacheMissed::class,
    KeyWritten::class,
]));

afterEach(fn() => Event::assertNothingDispatched());

$dirs = [
    __DIR__ . '/../_fixtures',
];

it('should parse usages properly `without ascend`', function () use ($dirs): void {
    $result = Parse::attribute(ClassAttribute::class)
        ->findUsages(...$dirs);

    expect($result)->toHaveCount(1)
        ->and($result->first()->name)->toBe('DefaultClass');
});

it('should parse usages properly `with ascend`', function () use ($dirs): void {
    $result = Parse::attribute(ClassAttribute::class)
        ->ascend()
        ->findUsages(...$dirs);

    expect($result)->toHaveCount(2)
        ->and($result->first()->name)->toBe('ChildDefaultClass')
        ->and($result->last()->name)->toBe('DefaultClass');
});

it('should parse class attributes properly', function (): void {
    $result = Parse::attribute(ClassAttribute::class)
        ->on(DefaultClass::class)
        ->get();

    expect($result)->not->toBeEmpty()->toHaveCount(1)
        ->and($result->first())->toEqual(
            new Discovered(
                name: DefaultClass::class,
                parent: null,
                attribute: new ClassAttribute([ 'some' => 'data' ]),
                target: Target::onClass,
            )
        );
});

it('should parse extended class attributes properly', function (): void {
    $result = Parse::attribute(ClassAttribute::class)
        ->on(ChildDefaultClass::class)
        ->get();

    expect($result)->toBeEmpty();

    $result = Parse::attribute(ClassAttribute::class)
        ->on(ChildDefaultClass::class)
        ->ascend()
        ->get();

    expect($result)->not->toBeEmpty()->toHaveCount(1)
        ->and($result->first())->toEqual(
            new Discovered(
                name: DefaultClass::class,
                parent: null,
                attribute: new ClassAttribute([ 'some' => 'data' ]),
                target: Target::onClass,
            )
        );
});

it('should parse common method attribute properly', function (): void {
    $result = Parse::attribute(MethodAttribute::class)
        ->on(DefaultClass::class)
        ->onMethods();

    expect($result)->not->toBeEmpty()->toHaveCount(1);
});

it('should parse repeatable method attribute properly', function (): void {
    $result = Parse::attribute(RepeatableMethodAttribute::class)
        ->on(DefaultClass::class)
        ->onMethods();

    expect($result)->not->toBeEmpty()->toHaveCount(5);
});

it('should parse in dirs', function () use ($dirs): void {
    $result = Parse::attribute(TagAttribute::class)
        ->in(...$dirs);

    expect($result)
        ->not->toBeEmpty()
        ->toHaveCount(2)
        ->toHaveKeys([DefaultClass::class, ChildDefaultClass::class])
        ->and($result->get(ChildDefaultClass::class))->toHaveCount(5)
        ->toHaveKeys([ Target::onClass->value, Target::method->value, Target::property->value, Target::constant->value, Target::parameter->value])
        ->and($result->get(DefaultClass::class))->toHaveCount(3)
        ->toHaveKeys([Target::method->value, Target::property->value, Target::constant->value]);
});
