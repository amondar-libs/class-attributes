<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Amondar\ClassAttributes\Libraries\Attributes;
use Tests\resources\attributes\ClassAttribute;
use Tests\resources\attributes\ClassAttributeRepeatable;
use Tests\resources\attributes\MethodAttribute;
use Tests\resources\classes\repeatable\FirstRepeatableClassChildWithAttribute;
use Tests\resources\classes\repeatable\FirstRepeatableClassWithAttribute;
use Tests\resources\classes\repeatable\simple\FirstClassChildWithoutAttribute;
use Tests\resources\classes\repeatable\simple\FirstClassWithAttribute;

test('Attributes can load class attribute', function () {
    $attribute = (new Attributes(FirstClassWithAttribute::class))->loadFromClass(ClassAttribute::class);

    expect($attribute)
        ->toBeInstanceOf(ClassAttribute::class)
        ->and($attribute->someData)
        ->toMatchArray([ 'someData' => 'someValue' ]);
});

test('Attributes can load class attribute from child class', function () {
    $attribute = (new Attributes(FirstClassChildWithoutAttribute::class))->loadFromClass(ClassAttribute::class, true);

    expect($attribute)
        ->toBeInstanceOf(ClassAttribute::class)
        ->and($attribute->someData)
        ->toMatchArray([ 'someData' => 'someValue' ]);

    $attribute = (new Attributes(FirstClassChildWithoutAttribute::class))->loadFromClass(ClassAttribute::class);

    expect($attribute)->toBeNull();
});

test('Attributes can load repeatable class attribute', function () {
    $attributes = (new Attributes(FirstRepeatableClassWithAttribute::class))->loadAsRepeatable(ClassAttributeRepeatable::class);

    expect($attributes->toArray())->toMatchArray([
        new ClassAttributeRepeatable([ 'someData' => 'someValue' ]),
        new ClassAttributeRepeatable([ 'someAnotherData' => 'someAnotherValue' ]),
    ]);

    $attributes = (new Attributes(FirstRepeatableClassChildWithAttribute::class))->loadAsRepeatable(ClassAttributeRepeatable::class);

    expect($attributes->toArray())->toMatchArray([
        new ClassAttributeRepeatable([ 'someAnotherData1' => 'someAnotherValue1' ]),
    ]);

    $attributes = (new Attributes(FirstRepeatableClassChildWithAttribute::class))->loadAsRepeatable(
        ClassAttributeRepeatable::class,
        true
    );

    expect($attributes->toArray())->toMatchArray([
        new ClassAttributeRepeatable([ 'someAnotherData1' => 'someAnotherValue1' ]),
        new ClassAttributeRepeatable([ 'someData' => 'someValue' ]),
        new ClassAttributeRepeatable([ 'someAnotherData' => 'someAnotherValue' ]),
    ]);
});

test('Attributes can load methods attributes', function () {
    $attributes = (new Attributes(FirstClassChildWithoutAttribute::class))->loadFromMethods(MethodAttribute::class);

    expect($attributes->map->toArray())->toMatchArray([
        'thirdMethod'  => [
            new MethodAttribute('Third method description'),
        ],
        'secondMethod' => [
            new MethodAttribute('Second method description'),
        ],
        'firstMethod'  => [
            new MethodAttribute('First method description'),
        ],
    ]);
});
