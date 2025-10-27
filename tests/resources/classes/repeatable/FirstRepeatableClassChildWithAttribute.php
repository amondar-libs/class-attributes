<?php

declare(strict_types = 1);

namespace Tests\resources\classes\repeatable;

use Tests\resources\attributes\ClassAttributeRepeatable;

#[ClassAttributeRepeatable([ 'someAnotherData1' => 'someAnotherValue1' ])]
class FirstRepeatableClassChildWithAttribute extends FirstRepeatableClassWithAttribute {}
