<?php

declare(strict_types = 1);

namespace Tests\_fixtures\attributes;

use Attribute;

/**
 * Class DescriptionAttribute
 *
 * @author Amondar-SO
 */
#[Attribute(Attribute::TARGET_ALL)]
class TagAttribute
{
    /**
     * DescriptionAttribute constructor.
     */
    public function __construct(public string $description)
    {
        //
    }

}
