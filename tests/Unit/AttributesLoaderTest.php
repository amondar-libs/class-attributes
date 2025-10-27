<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Amondar\ClassAttributes\Enums\LoadType;
use Amondar\ClassAttributes\Libraries\AttributesLoader;
use Illuminate\Support\Collection;
use Tests\resources\attributes\ClassAttribute;
use Tests\resources\attributes\ClassAttributeRepeatable;
use Tests\resources\classes\repeatable\FirstRepeatableClassChildWithAttribute;
use Tests\resources\classes\repeatable\FirstRepeatableClassWithAttribute;
use Tests\resources\classes\repeatable\simple\FirstClassChildWithoutAttribute;
use Tests\resources\classes\repeatable\simple\FirstClassWithAttribute;

test('AttributesLoader works as expected', function () {
    $result = AttributesLoader::new()
        ->add(ClassAttribute::class, LoadType::SimpleClass)
        ->load(FirstClassWithAttribute::class);

    expect($result->toArray())->toMatchArray([
        ClassAttribute::class => new ClassAttribute([
            'someData' => 'someValue',
        ]),
    ]);

    $result = AttributesLoader::new()
        ->add(ClassAttribute::class, LoadType::SimpleClass, true)
        ->load(FirstClassChildWithoutAttribute::class);

    expect($result->toArray())->toMatchArray([
        ClassAttribute::class => new ClassAttribute([
            'someData' => 'someValue',
        ]),
    ]);

    $result = AttributesLoader::new()
        ->add(ClassAttributeRepeatable::class, LoadType::SimpleClass)
        ->load(FirstRepeatableClassWithAttribute::class);

    expect($result->toArray())->toMatchArray([
        ClassAttributeRepeatable::class => new ClassAttributeRepeatable([
            'someData' => 'someValue',
        ]),
    ]);

    $result = AttributesLoader::new()
        ->add(ClassAttributeRepeatable::class, LoadType::RepeatableClass)
        ->load(FirstRepeatableClassChildWithAttribute::class);

    expect($result->map->toArray())->toMatchArray([
        ClassAttributeRepeatable::class => [
            new ClassAttributeRepeatable([
                'someAnotherData1' => 'someAnotherValue1',
            ]),
        ],
    ]);

    $result = AttributesLoader::new()
        ->add(ClassAttributeRepeatable::class, LoadType::RepeatableClass, true)
        ->load(FirstRepeatableClassChildWithAttribute::class);

    expect($result->map->toArray())->toMatchArray([
        ClassAttributeRepeatable::class => [
            new ClassAttributeRepeatable([
                'someAnotherData1' => 'someAnotherValue1',
            ]),
            new ClassAttributeRepeatable([
                'someData' => 'someValue',
            ]),
            new ClassAttributeRepeatable([
                'someAnotherData' => 'someAnotherValue',
            ]),
        ],
    ]);

    $result = AttributesLoader::new()
        ->add(
            ClassAttributeRepeatable::class,
            LoadType::RepeatableClass,
            true,
            fn(Collection $attributes) => $attributes->map->someData->collapse()
        )
        ->load(FirstRepeatableClassChildWithAttribute::class);

    expect($result->map->toArray())->toMatchArray([
        ClassAttributeRepeatable::class => [
            'someAnotherData1' => 'someAnotherValue1',
            'someData'         => 'someValue',
            'someAnotherData'  => 'someAnotherValue',
        ],
    ]);
});
