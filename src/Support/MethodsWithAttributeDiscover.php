<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Support;

use Amondar\ClassAttributes\Results\DiscoveredMethod;
use Amondar\ClassAttributes\Results\DiscoveredResult;
use ReflectionAttribute;
use ReflectionClass;
use Throwable;

/**
 * Class MethodsWithAttrubuteDiscover
 *
 * @internal
 *
 * @author Amondar-SO
 */
final readonly class MethodsWithAttributeDiscover extends Discover
{
    /**
     * MethodsWithAttrubuteDiscover constructor.
     */
    public function __construct(
        private string $attributeClass,
        private string $onClass,
    ) {
        //
    }

    public function discover(bool $isRepeatable): ?DiscoveredResult
    {
        try {
            $reflectionClass = new ReflectionClass($this->onClass);

            $attributes = [];

            foreach ($reflectionClass->getMethods() as $method) {
                $result = array_map(
                    fn(ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
                    $method->getAttributes($this->attributeClass)
                );

                if ($result !== []) {
                    $result = $this->removeDuplicates($result);

                    $attributes[] = new DiscoveredMethod(
                        $method->name,
                        $result
                    );
                }
            }

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
