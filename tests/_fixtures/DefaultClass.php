<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\attributes\MethodAttribute;
use Tests\_fixtures\attributes\RepeatableMethodAttribute;
use Tests\_fixtures\attributes\TagAttribute;

/**
 * Class ClassWithAttributedMethods
 *
 * @author Amondar-SO
 */
#[DescriptionAttribute('Class with attributed methods')]
#[ClassAttribute([ 'some' => 'data' ])]
class DefaultClass
{
    #[TagAttribute('MyConst')]
    public const MY_CONST = 'my-const';

    #[TagAttribute('isOk')]
    public bool $isOk = true;

    #[RepeatableMethodAttribute('api/v1/users')]
    #[RepeatableMethodAttribute('api/v2/users/')]
    #[RepeatableMethodAttribute('/api/v3/users')]
    #[DescriptionAttribute('Users list method')]
    #[TagAttribute('users.index')]
    public function usersList() {}

    #[RepeatableMethodAttribute('/posts')]
    #[RepeatableMethodAttribute('/api/v1/posts')]
    #[DescriptionAttribute('Posts list method')]
    #[TagAttribute('posts.index')]
    public function postsList() {}

    #[MethodAttribute('My method description')]
    #[DescriptionAttribute('My method')]
    #[TagAttribute('MyMethod')]
    protected function myMethod() {}
}
