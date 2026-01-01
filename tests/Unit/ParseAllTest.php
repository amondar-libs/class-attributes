<?php

declare(strict_types = 1);

use Amondar\ClassAttributes\Parse;
use Amondar\ClassAttributes\Results\DiscoveredMethod;
use Amondar\ClassAttributes\Results\DiscoveredResult;
use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\ClassExtendsAttributed;

$dirs = [
    __DIR__ . '/../_fixtures',
];

it('should parse all properly `without ascend`', function () use ($dirs) {
    $data = Parse::attribute(DescriptionAttribute::class)->all(...$dirs);

    expect($data)->toHaveCount(3)
        ->and($data[0])->toEqual(
            new DiscoveredResult(
                Tests\_fixtures\ClassWithAttributedMethods::class,
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
        ->and($data[1])->toEqual(
            new DiscoveredResult(
                ClassExtendsAttributed::class,
                [
                    new DiscoveredMethod(
                        'myAnotherMethod',
                        [new DescriptionAttribute('My another method')]
                    ),
                ]
            )
        )
        ->and($data[2])->toEqual(
            new DiscoveredResult(
                Tests\_fixtures\ClassWithAttribute::class,
                [
                    new DescriptionAttribute('Class with attribute'),
                ]
            )
        );

})->group('parse', 'parse::all');

it('should parse all properly `with ascend`', function () use ($dirs) {
    $data = Parse::attribute(DescriptionAttribute::class)
        ->ascend()
        ->all(...$dirs);

    expect($data)->toHaveCount(3)
        ->and($data[1])->toEqual(
            new DiscoveredResult(
                ClassExtendsAttributed::class,
                [
                    new DescriptionAttribute('Class with attribute'),
                    new DiscoveredMethod(
                        'myAnotherMethod',
                        [new DescriptionAttribute('My another method')]
                    ),
                ]
            )
        );
})->group('parse', 'parse::all');
