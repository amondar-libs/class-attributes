<?php

namespace Tests\resources\attributes;

use Attribute;

/**
 * Class ClassAttributeRepeatable
 *
 * @author Amondar-SO
 */
#[Attribute( Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ClassAttributeRepeatable
{
    public function __construct(public array $someData) { }

}