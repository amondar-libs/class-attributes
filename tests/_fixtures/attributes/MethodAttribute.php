<?php

declare(strict_types = 1);

namespace Tests\_fixtures\attributes;

use Attribute;

/**
 * Class MethodAttribute
 *
 * @author Amondar-SO
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MethodAttribute
{
    /**
     * MethodAttribute constructor.
     */
    public function __construct(public string $description) {}

}
