<?php

declare(strict_types = 1);

namespace Tests\_fixtures\attributes;

use Attribute;

/**
 * Class RepeatableMethodAttribute
 *
 * @author Amondar-SO
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RepeatableMethodAttribute
{
    /**
     * MethodAttribute constructor.
     */
    public function __construct(public string $route) {}

}
