<?php

declare(strict_types = 1);

namespace Tests\_fixtures\attributes;

use Attribute;

/**
 * Class ClassAttribute
 *
 * @author Amondar-SO
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ClassAttribute
{
    public function __construct(public array $someData) {}

}
