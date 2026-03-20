<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Support\Attribute;
use Tests\_fixtures\attributes\TagAttribute;

it('should works as expected', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onParameters(
            Tests\_fixtures\ChildDefaultClass::class,
            filterMethods: ReflectionMethod::IS_PUBLIC
        );

    expect($discovered)->toBeArray()->toBeEmpty();

    $discovered = Attribute::for(TagAttribute::class)
                           ->onParameters(
            Tests\_fixtures\ChildDefaultClass::class
        );

    expect($discovered)->toBeArray()->toHaveCount(1)
        ->and($discovered[ 0 ])->toEqual(
            new Amondar\ClassAttributes\Results\Discovered(
                name: 'support.request',
                parent: Tests\_fixtures\ChildDefaultClass::class,
                attribute: new TagAttribute('Request'),
                target: Amondar\ClassAttributes\Enums\Target::parameter,
            )
        );
});

it('works as expected with existence check', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onParameters(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(TagAttribute::class)
                           ->onParameters(
            class: Tests\_fixtures\ChildDefaultClass::class,
            filterMethods: ReflectionMethod::IS_PUBLIC,
            exist: true,
        );

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(Tests\_fixtures\attributes\ClassAttribute::class)
                           ->onParameters(
            class: Tests\_fixtures\ChildDefaultClass::class,
            filterMethods: ReflectionMethod::IS_PUBLIC,
            exist: true,
        );

    expect($discovered)->toBeFalse();
});
