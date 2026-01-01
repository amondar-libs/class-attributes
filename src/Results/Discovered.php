<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class Discovered
 *
 * @author Amondar-SO
 */
abstract readonly class Discovered
{
    /**
     * Discovered constructor.
     */
    public function __construct(public string $target)
    {
        //
    }

}
