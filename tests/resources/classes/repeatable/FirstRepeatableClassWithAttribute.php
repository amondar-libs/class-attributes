<?php

namespace Tests\resources\classes\repeatable;

use Tests\resources\attributes\ClassAttributeRepeatable;

/**
 * Class FirstRepeatableClassWithAttribute
 *
 * @author Amondar-SO
 */
#[ClassAttributeRepeatable( [ 'someData' => 'someValue' ])]
#[ClassAttributeRepeatable( [ 'someAnotherData' => 'someAnotherValue' ])]
class FirstRepeatableClassWithAttribute
{

    /**
     * FirstRepeatableClassWithAttribute constructor.
     */
    public function __construct()
    {
        //
    }

}