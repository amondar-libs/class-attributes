<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Support\Attribute;
use Tests\_fixtures\attributes\TagAttribute;

it('should works as expected', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onConstants(
            Tests\_fixtures\ChildDefaultClass::class,
            filter: ReflectionClassConstant::IS_PUBLIC
        );

    expect($discovered)->toBeArray()->toHaveCount(1)
        ->and($discovered[ 0 ])->toEqual(
            new Amondar\ClassAttributes\Results\Discovered(
                name: 'MY_CONST',
                parent: Tests\_fixtures\DefaultClass::class,
                attribute: new TagAttribute('MyConst'),
                target: Amondar\ClassAttributes\Enums\Target::constant,
            )
        );

    $discovered = Attribute::for(TagAttribute::class)
                           ->onConstants(
            Tests\_fixtures\ChildDefaultClass::class,
            filter: ReflectionClassConstant::IS_PROTECTED
        );

    expect($discovered)->toBeArray()->toHaveCount(0);
});

it('should works as expected with existence check', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onConstants(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(TagAttribute::class)
                           ->onConstants(
            class: Tests\_fixtures\ChildDefaultClass::class,
            filter: ReflectionClassConstant::IS_PRIVATE,
            exist: true,
        );

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(Tests\_fixtures\attributes\DescriptionAttribute::class)
                           ->onConstants(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeFalse();
});
