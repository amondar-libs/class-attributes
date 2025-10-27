<?php

declare( strict_types = 1 );

namespace Tests\Unit;

use Amondar\ClassAttributes\AttributesCache;
use Amondar\ClassAttributes\Enums\LoadType;
use Amondar\ClassAttributes\Libraries\AttributesLoader;
use Illuminate\Support\Collection;
use Tests\resources\attributes\ClassAttribute;
use Tests\resources\attributes\ClassAttributeRepeatable;
use Tests\resources\attributes\MethodAttribute;
use Tests\resources\classes\repeatable\FirstRepeatableClassChildWithAttribute;
use Tests\resources\classes\repeatable\FirstRepeatableClassWithAttribute;
use Tests\resources\classes\repeatable\simple\FirstClassChildWithoutAttribute;
use Tests\resources\classes\repeatable\simple\FirstClassWithAttribute;

test('AttributesCache works as expected when adding new namespaces', function () {
    AttributesCache::addNamespace([
        'Tests\resources\classes' => AttributesLoader::new()
                                                     ->add(
                                                         ClassAttribute::class, ascend: true,
                                                         customLoader: fn($attribute) => $attribute->someData,
                                                     )
                                                     ->add(
                                                         ClassAttributeRepeatable::class,
                                                         LoadType::RepeatableClass,
                                                         true,
                                                         fn(Collection $attributes) => $attributes->map->someData->collapse()
                                                     )
                                                     ->add(
                                                         MethodAttribute::class, LoadType::Method,
                                                         customLoader: fn(Collection $methods) => $methods->map(fn($method) => $method->first()->description),
                                                     ),
    ]);

    AttributesCache::load();

    //dd(AttributesCache::get(FirstClassWithAttribute::class, ClassAttributeRepeatable::class));

    expect(AttributesCache::get(FirstClassWithAttribute::class, ClassAttribute::class))
        ->toMatchArray([ 'someData' => 'someValue', ])
        ->and(AttributesCache::get(FirstClassWithAttribute::class, MethodAttribute::class))
        ->toMatchArray([
            'firstMethod'  => 'First method description',
            'secondMethod' => 'Second method description',
        ])
        ->and(AttributesCache::get(FirstClassWithAttribute::class, ClassAttributeRepeatable::class))
        ->toBeNull()
        ->and(AttributesCache::get(FirstClassChildWithoutAttribute::class, ClassAttribute::class))
        ->toMatchArray([ 'someData' => 'someValue' ])
        ->and(AttributesCache::get(FirstClassChildWithoutAttribute::class, MethodAttribute::class))
        ->toMatchArray([
            'firstMethod'  => 'First method description',
            'secondMethod' => 'Second method description',
            'thirdMethod'  => 'Third method description',
        ])
        ->and(AttributesCache::get(FirstClassChildWithoutAttribute::class, ClassAttributeRepeatable::class))
        ->toBeNull()
        ->and(AttributesCache::get(FirstRepeatableClassWithAttribute::class, ClassAttributeRepeatable::class))
        ->toMatchArray([
            'someData'        => 'someValue',
            'someAnotherData' => 'someAnotherValue',
        ])
        ->and(AttributesCache::get(FirstRepeatableClassWithAttribute::class, ClassAttribute::class))
        ->toBeNull()
        ->and(AttributesCache::get(FirstRepeatableClassWithAttribute::class, MethodAttribute::class))
        ->toBeNull()
        ->and(AttributesCache::get(FirstRepeatableClassChildWithAttribute::class, ClassAttributeRepeatable::class))
        ->toMatchArray([
            'someAnotherData1' => 'someAnotherValue1',
            'someData'         => 'someValue',
            'someAnotherData'  => 'someAnotherValue',
        ])
        ->and(AttributesCache::get(FirstRepeatableClassChildWithAttribute::class, ClassAttribute::class))
        ->toBeNull()
        ->and(AttributesCache::get(FirstRepeatableClassChildWithAttribute::class, MethodAttribute::class))
        ->toBeNull();
});
