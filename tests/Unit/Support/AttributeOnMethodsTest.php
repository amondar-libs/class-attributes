<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Results\Discovered;
use Amondar\ClassAttributes\Support\Attribute;
use Tests\_fixtures\attributes\RequestAttribute;
use Tests\_fixtures\attributes\TagAttribute;

$tagsArray = [
    new Discovered(
        name: 'usersList',
        parent: Tests\_fixtures\DefaultClass::class,
        attribute: new TagAttribute('users.index'),
        target: Amondar\ClassAttributes\Enums\Target::method,
    ),
    new Discovered(
        name: 'postsList',
        parent: Tests\_fixtures\DefaultClass::class,
        attribute: new TagAttribute('posts.index'),
        target: Amondar\ClassAttributes\Enums\Target::method,
    ),
    new Discovered(
        name: 'myMethod',
        parent: Tests\_fixtures\DefaultClass::class,
        attribute: new TagAttribute('MyMethod'),
        target: Amondar\ClassAttributes\Enums\Target::method,
    ),
];

it('works as expected', function () use ($tagsArray): void {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onMethods(Tests\_fixtures\ChildDefaultClass::class);

    expect($discovered)->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray($tagsArray);
});

it('works as expected including parameters', function () use ($tagsArray): void {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onMethods(
            class: Tests\_fixtures\ChildDefaultClass::class,
            includeParameters: true,
        );

    expect($discovered)->toBeArray()
        ->toHaveCount(4)
        ->and($discovered[ 0 ])->toEqual(
            new Discovered(
                name: 'support.request',
                parent: Tests\_fixtures\ChildDefaultClass::class,
                attribute: new TagAttribute('Request'),
                target: Amondar\ClassAttributes\Enums\Target::parameter,
            )
        )
        ->and(array_slice($discovered, 1))->toMatchArray($tagsArray);
});

it('works as expected with existence check', function () {
    $discovered = Attribute::for(TagAttribute::class)
                           ->onMethods(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeTrue();

    $discovered = Attribute::for(RequestAttribute::class)
                           ->onMethods(
            class: Tests\_fixtures\ChildDefaultClass::class,
            exist: true,
        );

    expect($discovered)->toBeFalse();

    $discovered = Attribute::for(RequestAttribute::class)
                           ->onMethods(
            class: Tests\_fixtures\ChildDefaultClass::class,
            includeParameters: true,
            exist: true,
        );

    expect($discovered)->toBeTrue();
});
