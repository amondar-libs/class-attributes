<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\ClassAttributeRepeatable;

/**
 * Class ClassWithRepeatedAttributes
 *
 * @author Amondar-SO
 */
#[ClassAttributeRepeatable([ 'some' => 'data' ])]
#[ClassAttributeRepeatable([ 'duplicated' => 'data' ])]
class ClassWithRepeatedAttributes {}
