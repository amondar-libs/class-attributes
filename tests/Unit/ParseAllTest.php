<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Parse;
use Amondar\ClassAttributes\Results\DiscoveredMethod;
use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\ClassExtendsAttributed;
use Tests\_fixtures\ClassWithAttribute;
use Tests\_fixtures\ClassWithAttributedMethods;

$dirs = [
    __DIR__ . '/../_fixtures',
];

it('should parse all properly `without ascend`', function () use ($dirs) {
    $data = Parse::attribute(DescriptionAttribute::class)->all(...$dirs);

    expect($data)->toHaveCount(3)
        ->and($data[0]->target)->toBe(ClassWithAttributedMethods::class)
        ->and($data[0]->onClass)->toBeEmpty()
        ->and($data[0]->onMethods)->toEqual(
            collect(
                [
                    new DiscoveredMethod(
                        'myMethod',
                        [new DescriptionAttribute('My method')]
                    ),
                    new DiscoveredMethod(
                        'usersList',
                        [new DescriptionAttribute('Users list method')]
                    ),
                    new DiscoveredMethod(
                        'postsList',
                        [new DescriptionAttribute('Posts list method')]
                    ),
                ]
            )
        )
        ->and($data[1]->target)->toBe(ClassExtendsAttributed::class)
        ->and($data[1]->onClass)->toBeEmpty()
        ->and($data[1]->onMethods)->toEqual(
            collect([
                new DiscoveredMethod(
                    'myAnotherMethod',
                    [new DescriptionAttribute('My another method')]
                ),
            ])
        )
        ->and($data[2]->target)->toBe(ClassWithAttribute::class)
        ->and($data[2]->onMethods)->toBeEmpty()
        ->and($data[2]->onClass)->toEqual(
            collect([
                new DescriptionAttribute('Class with attribute'),
            ])
        );

})->group('parse', 'parse::all');

it('should parse all properly `with ascend`', function () use ($dirs) {
    $data = Parse::attribute(DescriptionAttribute::class)
        ->ascend()
        ->all(...$dirs);

    expect($data)->toHaveCount(3)
        ->and($data[1]->onClass)->toEqual(
            collect([
                new DescriptionAttribute('Class with attribute'),
            ])
        )
        ->and($data[1]->onMethods)->toEqual(
            collect([
                new DiscoveredMethod(
                    'myAnotherMethod',
                    [new DescriptionAttribute('My another method')]
                ),
            ])
        );
})->group('parse', 'parse::all');
