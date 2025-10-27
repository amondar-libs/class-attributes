<?php

namespace Tests\resources\classes\repeatable\simple;

use Tests\resources\attributes\MethodAttribute;

/**
 * Class FirstClassChildWithoutAttribute
 *
 * @author Amondar-SO
 */
class FirstClassChildWithoutAttribute extends FirstClassWithAttribute
{

    #[MethodAttribute( 'Third method description' )]
    public function thirdMethod()
    {
        //
    }

}