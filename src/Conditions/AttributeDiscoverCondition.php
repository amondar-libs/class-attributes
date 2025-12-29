<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Conditions;

use Amondar\ClassAttributes\Support\ClassWithAttributeDiscover;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use Spatie\StructureDiscoverer\Data\DiscoveredClass;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\DiscoverConditions\DiscoverCondition;
use Spatie\StructureDiscoverer\Enums\DiscoveredStructureType;
use Throwable;

/**
 * Class AttributeDiscoverCondition
 *
 * @internal
 *
 * @author Amondar-SO
 */
final class AttributeDiscoverCondition extends DiscoverCondition
{
    public function __construct(
        private readonly string $attributeClass,
        private readonly bool $ascend = false,
    ) {
        //
    }

    public function satisfies(DiscoveredStructure $discoveredData): bool
    {
        if ($discoveredData->getType() !== DiscoveredStructureType::ClassDefinition) {
            return false;
        }

        return $this->searchOnClassHead($discoveredData) || $this->searchOnClassMethods($discoveredData);
    }

    /**
     * Searches for the presence of specific attributes on the given class or its parent classes.
     */
    #[Pure]
    private function searchOnClassHead(DiscoveredClass $data): bool
    {
        foreach ($data->attributes as $attribute) {
            if ($attribute->class === $this->attributeClass) {
                return true;
            }
        }

        if ($this->ascend) {
            return (new ClassWithAttributeDiscover(
                $this->attributeClass,
                $data->getFcqn(),
                ascend: true
            ))->discover(false) !== null;
        }

        return false;
    }

    /**
     * Searches for methods within a given class that have specified attributes.
     */
    private function searchOnClassMethods(DiscoveredClass $data): bool
    {
        try {
            $class = new ReflectionClass($data->getFcqn());

            foreach ($class->getMethods() as $method) {
                if ($method->getAttributes($this->attributeClass) !== []) {
                    return true;
                }
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }
}
