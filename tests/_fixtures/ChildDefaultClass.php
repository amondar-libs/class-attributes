<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\DescriptionAttribute;
use Tests\_fixtures\attributes\RequestAttribute;
use Tests\_fixtures\attributes\TagAttribute;

/**
 * Class ClassWithAttributedMethods
 *
 * @author Amondar-SO
 */
#[TagAttribute('Child')]
class ChildDefaultClass extends DefaultClass
{
    #[TagAttribute('Attributes')]
    protected array $attributes = [];

    #[DescriptionAttribute('Support methods')]
    protected function support(#[TagAttribute('Request'), RequestAttribute('HttpRequest')] $request) {}
}
