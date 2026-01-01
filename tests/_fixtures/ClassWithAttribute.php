<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\ClassAttribute;
use Tests\_fixtures\attributes\DescriptionAttribute;

/**
 * Class ClassWithAttribute
 *
 * @author Amondar-SO
 */
#[ClassAttribute([ 'some' => 'data' ])]
#[DescriptionAttribute('Class with attribute')]
class ClassWithAttribute {}
