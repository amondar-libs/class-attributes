<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\DescriptionAttribute;

/**
 * Class ClassExtendsAttributed
 *
 * @author Amondar-SO
 */
class ClassExtendsAttributed extends ClassWithAttribute
{
    #[DescriptionAttribute('My another method')]
    public function myAnotherMethod() {}
}
