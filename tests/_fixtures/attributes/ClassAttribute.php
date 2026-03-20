<?php

declare(strict_types = 1);

namespace Tests\_fixtures\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ClassAttribute
{
    public function __construct(public array $someData) {}

}
