<?php

declare(strict_types = 1);

it('should work as expected', function (
    $attribute,
    $onClass,
    $ascend,
    $isRepeatable,
    $expected
) {
    $result = (new Amondar\ClassAttributes\Support\ClassWithAttributeDiscover(
        $attribute,
        $onClass,
        $ascend
    ))->discover($isRepeatable);

    if ($expected instanceof Amondar\ClassAttributes\Results\DiscoveredResult) {
        expect($result)->toEqual($expected);
    } else {
        expect($result)->toBeNull();
    }
})->with([
    '`on common class` ' => [
        Tests\_fixtures\attributes\ClassAttribute::class,
        Tests\_fixtures\ClassWithAttribute::class,
        // Ascend
        false,
        // Is Repeatable,
        false,
        // Expected
        new Amondar\ClassAttributes\Results\DiscoveredResult(
            Tests\_fixtures\ClassWithAttribute::class,
            [
                new Tests\_fixtures\attributes\ClassAttribute([ 'some' => 'data' ]),
            ],
        ),
    ],
    '`on extended class with ascend`' => [
        Tests\_fixtures\attributes\ClassAttribute::class,
        Tests\_fixtures\ClassExtendsAttributed::class,
        // Ascend
        true,
        // Is Repeatable,
        false,
        // Expected
        new Amondar\ClassAttributes\Results\DiscoveredResult(
            Tests\_fixtures\ClassExtendsAttributed::class,
            [
                new Tests\_fixtures\attributes\ClassAttribute([ 'some' => 'data' ]),
            ],
        ),
    ],
    '`on extended class without ascend`' => [
        Tests\_fixtures\attributes\ClassAttribute::class,
        Tests\_fixtures\ClassExtendsAttributed::class,
        // Ascend
        false,
        // Is Repeatable,
        false,
        // Expected
        null,
    ],
    '`on repeatable class`' => [
        Tests\_fixtures\attributes\ClassAttributeRepeatable::class,
        Tests\_fixtures\ClassWithRepeatedAttributes::class,
        // Ascend
        false,
        // Is Repeatable,
        true,
        // Expected
        new Amondar\ClassAttributes\Results\DiscoveredResult(
            Tests\_fixtures\ClassWithRepeatedAttributes::class,
            [
                new Tests\_fixtures\attributes\ClassAttributeRepeatable([ 'some' => 'data' ]),
                new Tests\_fixtures\attributes\ClassAttributeRepeatable([ 'duplicated' => 'data' ]),
            ],
        ),
    ],
    '`on repeatable extended class with ascend`' => [
        Tests\_fixtures\attributes\ClassAttributeRepeatable::class,
        Tests\_fixtures\ClassExtendsRepeatableAttributed::class,
        // Ascend
        true,
        // Is Repeatable,
        true,
        // Expected
        new Amondar\ClassAttributes\Results\DiscoveredResult(
            Tests\_fixtures\ClassExtendsRepeatableAttributed::class,
            [
                new Tests\_fixtures\attributes\ClassAttributeRepeatable([ 'some' => 'data' ]),
                new Tests\_fixtures\attributes\ClassAttributeRepeatable([ 'duplicated' => 'data' ]),
            ],
        ),
    ],
    '`on repeatable extended class without ascend`' => [
        Tests\_fixtures\attributes\ClassAttributeRepeatable::class,
        Tests\_fixtures\ClassExtendsRepeatableAttributed::class,
        // Ascend
        false,
        // Is Repeatable,
        true,
        // Expected
        null,
    ],
])->group('support', 'support::class-discover');
