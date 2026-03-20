<?php

declare(strict_types = 1);

namespace Tests\_fixtures\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestAttribute
{
    /**
     * DescriptionAttribute constructor.
     */
    public function __construct(public string $description)
    {
        //
    }

}
