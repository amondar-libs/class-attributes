<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Exceptions;

use RuntimeException;

/**
 * Class ParseException
 *
 * @author Amondar-SO
 */
class ParseException extends RuntimeException
{
    public static function noClassTargetFound(string $methodName): static
    {
        return new static(
            'No class target found. Please call "->onClass()" method before calling "->' . $methodName . '".'
        );
    }
}
