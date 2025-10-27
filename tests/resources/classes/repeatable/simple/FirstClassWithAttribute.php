<?php

namespace Tests\resources\classes\repeatable\simple;

use Tests\resources\attributes\ClassAttribute;
use Tests\resources\attributes\MethodAttribute;

/**
 * Class FirstClassWithAttribute
 *
 * @author Amondar-SO
 */
#[ClassAttribute( [ 'someData' => 'someValue' ] )]
class FirstClassWithAttribute
{
    #[MethodAttribute( 'First method description' )]
    public function firstMethod()
    {
        //
    }

    #[MethodAttribute( 'Second method description' )]
    public function secondMethod()
    {
        //
    }

}