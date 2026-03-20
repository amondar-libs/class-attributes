<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Support\Attribute;
use Tests\_fixtures\attributes\TagAttribute;

it('should works as expected', function () {
    $discovered = Attribute::for(TagAttribute::class)
        ->onProperties(
            Tests\_fixtures\ChildDefaultClass::class,
            filter: ReflectionProperty::IS_PUBLIC
        );

    expect($discovered)->toBeArray()->toHaveCount(1)
        ->and($discovered[ 0 ])->toEqual(
            new Amondar\ClassAttributes\Results\Discovered(
                name: 'isOk',
                parent: Tests\_fixtures\DefaultClass::class,
                attribute: new TagAttribute('isOk'),
                target: Amondar\ClassAttributes\Enums\Target::property,
            )
        );

    $discovered = Attribute::for(TagAttribute::class)
        ->onProperties(
            Tests\_fixtures\ChildDefaultClass::class,
            filter: ReflectionProperty::IS_PROTECTED
        );

    expect($discovered)->toBeArray()->toHaveCount(1)
        ->and($discovered[ 0 ])->toEqual(
            new Amondar\ClassAttributes\Results\Discovered(
                name: 'attributes',
                parent: Tests\_fixtures\ChildDefaultClass::class,
                attribute: new TagAttribute('Attributes'),
                target: Amondar\ClassAttributes\Enums\Target::property,
            )
        );
});

it('should works as expected with existence check', function () {
    $discovered = Attribute::for(TagAttribute::class)
        ->onProperties(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(TagAttribute::class)
        ->onProperties(
            class: Tests\_fixtures\ChildDefaultClass::class,
            filter: ReflectionProperty::IS_PRIVATE,
            exist: true,
        );

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(Tests\_fixtures\attributes\DescriptionAttribute::class)
        ->onProperties(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeFalse();
});
