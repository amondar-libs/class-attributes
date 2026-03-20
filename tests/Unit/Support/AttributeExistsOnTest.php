<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Support\Attribute;
use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\RepeatableMethodAttribute;

it('should work as expected', function () {
    $discovered = Attribute::for(ClassAttribute::class)
        ->existsOn(Tests\_fixtures\DefaultClass::class);

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(ClassAttribute::class)
        ->existsOn(Tests\_fixtures\ChildDefaultClass::class);

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(ClassAttribute::class)
        ->ascend()
        ->existsOn(Tests\_fixtures\ChildDefaultClass::class);

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(RepeatableMethodAttribute::class)
        ->ascend()
        ->existsOn(Tests\_fixtures\ChildDefaultClass::class);

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(RepeatableMethodAttribute::class)
        ->ascend()
        ->existsOn(Tests\_fixtures\DefaultClass::class);

    expect($discovered)->toBeTrue();
});
