<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\attributes\MethodAttribute;
use Tests\_fixtures\attributes\RepeatableMethodAttribute;

/**
 * Class ClassWithAttributedMethods
 *
 * @author Amondar-SO
 */
class ClassWithAttributedMethods
{
    #[MethodAttribute('My method description')]
    #[DescriptionAttribute('My method')]
    public function myMethod() {}

    #[RepeatableMethodAttribute('/users')]
    #[RepeatableMethodAttribute('/users')]
    #[RepeatableMethodAttribute('/api/v1/users')]
    #[DescriptionAttribute('Users list method')]
    public function usersList() {}

    #[RepeatableMethodAttribute('/posts')]
    #[RepeatableMethodAttribute('/api/v1/posts')]
    #[DescriptionAttribute('Posts list method')]
    public function postsList() {}
}
