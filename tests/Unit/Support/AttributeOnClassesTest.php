<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Support\Attribute;
use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\attributes\RepeatableMethodAttribute;
use Tests\_fixtures\attributes\TagAttribute;

it('should works as expected', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->on(Tests\_fixtures\ChildDefaultClass::class);

    expect($discovered)->toBeArray()->toHaveCount(1)
        ->and($discovered[ 0 ])->toEqual(
            new Amondar\ClassAttributes\Results\Discovered(
                name: Tests\_fixtures\ChildDefaultClass::class,
                parent: null,
                attribute: new TagAttribute('Child'),
                target: Amondar\ClassAttributes\Enums\Target::onClass,
            )
        );

    $discovered = Attribute::for(DescriptionAttribute::class)
                           ->ascend()
                           ->on(Tests\_fixtures\ChildDefaultClass::class);

    expect($discovered)->toBeArray()->toHaveCount(1)
        ->and($discovered[ 0 ])->toEqual(
            new Amondar\ClassAttributes\Results\Discovered(
                name: Tests\_fixtures\DefaultClass::class,
                parent: null,
                attribute: new DescriptionAttribute('Class with attributed methods'),
                target: Amondar\ClassAttributes\Enums\Target::onClass,
            )
        );
});

it('should works as expected with existence check', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->on(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(RepeatableMethodAttribute::class)
                           ->on(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(DescriptionAttribute::class)
                           ->on(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(DescriptionAttribute::class)
                           ->ascend()
                           ->on(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeTrue();
});
