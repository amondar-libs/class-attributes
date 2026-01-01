<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Exceptions;

use RuntimeException;

/**
 * Class AttributeException
 *
 * @author Amondar-SO
 */
class AttributeException extends RuntimeException
{
    public static function noAttributeClassFound(string $attributeClass): static
    {
        return new static(
            "No attribute class found for [$attributeClass]."
        );
    }

    public static function classIsNotAnAttribute(string $attributeClass): static
    {
        return new static(
            "Class [$attributeClass] is not an attribute. Please make sure you have used \"#[Attribute(Attribute::TARGET_...)]\" on it."
        );
    }
}
