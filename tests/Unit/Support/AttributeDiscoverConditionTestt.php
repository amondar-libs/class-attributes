<?php

declare(strict_types = 1);

it('should works as expected', function (
    $attribute,
    $onClass,
    $expected
) {
    $result = (new Amondar\ClassAttributes\Conditions\AttributeDiscoverCondition(
        $attribute,
        ascend: true
    ))->satisfies(
        Spatie\StructureDiscoverer\Discover::in(__DIR__ . '/../../_fixtures')
            ->full()
            ->named(class_basename($onClass))
            ->get()[ 0 ]
    );

    expect($result)->toBe($expected);
})->with([
    '`root class with attribute`' => [
        Tests\_fixtures\attributes\ClassAttribute::class,
        Tests\_fixtures\ClassWithAttribute::class,
        // Expected
        true,
    ],
    '`parent class with attribute`' => [
        Tests\_fixtures\attributes\ClassAttribute::class,
        Tests\_fixtures\ClassExtendsAttributed::class,
        // Expected
        true,
    ],
    '`false on clean class`' => [
        Tests\_fixtures\attributes\ClassAttribute::class,
        Tests\_fixtures\ClassWithoutAttributes::class,
        // Expected
        false,
    ],
    '`with method attribute`' => [
        Tests\_fixtures\attributes\MethodAttribute::class,
        Tests\_fixtures\ClassWithAttributedMethods::class,
        // Expected
        true,
    ],
])->group('support', 'support::attribute-discover-condition');
