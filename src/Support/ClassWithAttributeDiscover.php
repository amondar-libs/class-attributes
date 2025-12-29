<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Support;

use Amondar\ClassAttributes\Results\DiscoveredResult;
use ReflectionAttribute;
use ReflectionClass;
use Throwable;

/**
 * Class ClassWithAttributeDiscover
 *
 * @author Amondar-SO
 */
final readonly class ClassWithAttributeDiscover extends Discover
{
    /**
     * ClassWithAttributeDiscover constructor.
     */
    public function __construct(
        private string $attributeClass,
        private string $onClass,
        private bool $ascend = false
    ) {
        //
    }

    public function discover(bool $isRepeatable): ?DiscoveredResult
    {
        try {
            $reflectionClass = new ReflectionClass($this->onClass);

            $attributes = [];

            do {
                $result = array_map(
                    fn(ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
                    $reflectionClass->getAttributes($this->attributeClass)
                );

                $attributes = array_merge($attributes, $result);

                // Break if the attribute is not repeatable and there are already attributes discovered.
                // Does not make sense to continue searching for attributes if the attribute is not repeatable.
                if ($attributes !== [] && ! $isRepeatable) {
                    break;
                }
            } while ($this->ascend && false !== $reflectionClass = $reflectionClass->getParentClass());

            $attributes = $this->removeDuplicates($attributes);

            return $attributes !== [] ?
                new DiscoveredResult(
                    $this->onClass,
                    $attributes,
                ) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
