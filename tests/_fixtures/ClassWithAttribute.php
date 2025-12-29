<?php

declare(strict_types = 1);

namespace Tests\_fixtures;

use Tests\_fixtures\attributes\ClassAttribute;

/**
 * Class ClassWithAttribute
 *
 * @author Amondar-SO
 */
#[ClassAttribute([ 'some' => 'data' ])]
class ClassWithAttribute {}
