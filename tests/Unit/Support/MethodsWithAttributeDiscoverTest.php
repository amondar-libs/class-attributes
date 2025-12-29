<?php

declare(strict_types = 1);

it('should work as expected', function (
    $attribute,
    $onClass,
    $isRepeatable,
    $expected
) {
    $result = (new Amondar\ClassAttributes\Support\MethodsWithAttributeDiscover(
        $attribute,
        $onClass
    ))->discover($isRepeatable);

    if ($expected instanceof Amondar\ClassAttributes\Results\DiscoveredResult) {
        expect($result)->toEqual($expected);
    } else {
        expect($result)->toBeNull();
    }
})->with([
    '`on common attribute class`' => [
        Tests\_fixtures\attributes\MethodAttribute::class,
        Tests\_fixtures\ClassWithAttributedMethods::class,
        // Is Repeatable,
        true,
        new Amondar\ClassAttributes\Results\DiscoveredResult(
            Tests\_fixtures\ClassWithAttributedMethods::class,
            [
                new Amondar\ClassAttributes\Results\DiscoveredMethod(
                    'myMethod',
                    [
                        new Tests\_fixtures\attributes\MethodAttribute('My method description'),
                    ]
                ),
            ]
        ),
    ],
    '`on repeated attribute class`' => [
        Tests\_fixtures\attributes\RepeatableMethodAttribute::class,
        Tests\_fixtures\ClassWithAttributedMethods::class,
        // Is Repeatable,
        false,
        new Amondar\ClassAttributes\Results\DiscoveredResult(
            Tests\_fixtures\ClassWithAttributedMethods::class,
            [
                new Amondar\ClassAttributes\Results\DiscoveredMethod(
                    'usersList',
                    [
                        new Tests\_fixtures\attributes\RepeatableMethodAttribute('/users'),
                        new Tests\_fixtures\attributes\RepeatableMethodAttribute('/api/v1/users'),
                    ]
                ),
                new Amondar\ClassAttributes\Results\DiscoveredMethod(
                    'postsList',
                    [
                        new Tests\_fixtures\attributes\RepeatableMethodAttribute('/posts'),
                        new Tests\_fixtures\attributes\RepeatableMethodAttribute('/api/v1/posts'),
                    ]
                ),
            ]
        ),
    ],
])->group('support', 'support::methods-discover');
