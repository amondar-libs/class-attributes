<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

use Amondar\ClassAttributes\Exceptions\AttributeException;
use Attribute;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Class DiscoveredAttribute
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredAttribute
{
    /**
     * DiscoveredAttribute constructor.
     */
    public function __construct(
        public string $className,
        public bool $isOnClass,
        public bool $isOnMethod,
        public bool $isOnFunction,
        public bool $isOnProperty,
        public bool $isOnParameter,
        public bool $isOnConstant,
        public bool $isOnClassConstant,
        public bool $isRepeatable,
    ) {}

    public static function from(string $attributeClass): self
    {
        if ( ! class_exists($attributeClass)) {
            throw AttributeException::noAttributeClassFound(
                $attributeClass
            );
        }

        $ref = new ReflectionClass($attributeClass);

        // The "Attribute" meta-attribute is what defines TARGET_* and IS_REPEATABLE
        $attrs = $ref->getAttributes(Attribute::class);

        if ($attrs === []) {
            // Not an attribute class at all (no #[Attribute] declared)
            throw AttributeException::classIsNotAnAttribute($attributeClass);
        }

        /** @var Attribute $meta */
        $meta = $attrs[ 0 ]->newInstance();

        return new self(
            className: $attributeClass,
            isOnClass: (bool) ($meta->flags & Attribute::TARGET_CLASS),
            isOnMethod: (bool) ($meta->flags & Attribute::TARGET_METHOD),
            isOnFunction: (bool) ($meta->flags & Attribute::TARGET_FUNCTION),
            isOnProperty: (bool) ($meta->flags & Attribute::TARGET_PROPERTY),
            isOnParameter: (bool) ($meta->flags & Attribute::TARGET_PARAMETER),
            isOnConstant: PHP_VERSION_ID >= 80500 && ($meta->flags & Attribute::TARGET_CONSTANT),
            isOnClassConstant: (bool) ($meta->flags & Attribute::TARGET_CLASS_CONSTANT),
            isRepeatable: (bool) ($meta->flags & Attribute::IS_REPEATABLE)
        );
    }

    public function getArgs(): array
    {
        return [$this->className, ReflectionAttribute::IS_INSTANCEOF];
    }

    public function shouldSkipMethods(bool $includeParameters = false): bool
    {
        return ! $this->isOnMethod
               && (
                   ! $this->isOnParameter || ! $includeParameters
               );
    }

    public function shouldSkipClassExistence(): bool
    {
        return ! $this->isOnClass
            && ! $this->isOnClassConstant
            && ! $this->isOnProperty
            && ! $this->isOnMethod
            && ! $this->isOnParameter;
    }
}
