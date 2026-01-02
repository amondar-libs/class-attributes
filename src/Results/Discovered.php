<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class Discovered
 *
 * @template T
 *
 * @author Amondar-SO
 */
abstract readonly class Discovered
{
    /**
     * Discovered constructor.
     *
     * @param  string|class-string<T>  $target
     */
    public function __construct(public string $target)
    {
        //
    }

}
