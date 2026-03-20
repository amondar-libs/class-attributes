<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Support;

use Amondar\ClassAttributes\Enums\Target;
use Amondar\ClassAttributes\Results\Discovered;
use Amondar\ClassAttributes\Results\DiscoveredAttribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * @template TAttribute
 *
 * Class Attribute
 *
 * @author Amondar-SO
 */
final readonly class Attribute
{
    /**
     * @var DiscoveredAttribute<TAttribute>
     */
    public DiscoveredAttribute $discovered;

    /**
     * Attribute constructor.
     *
     * @param  class-string<TAttribute>|DiscoveredAttribute<TAttribute>  $attribute
     */
    public function __construct(string|DiscoveredAttribute $attribute, public bool $ascend = false)
    {
        $this->discovered = is_string($attribute) ? DiscoveredAttribute::from($attribute) : $attribute;
    }

    /**
     * @param class-string<TAttribute>|DiscoveredAttribute<TAttribute> $attribute
     *
     * @return self<TAttribute>
     */
    public static function for(string|DiscoveredAttribute $attribute, bool $ascend = false): self
    {
        return new self($attribute, $ascend);
    }

    /**
     * @return self<TAttribute>
     */
    public function ascend(): self
    {
        return new self($this->discovered, true);
    }

    public function existsOn(string|object $class): bool
    {
        if ($this->discovered->shouldSkipClassExistence()) {
            return false;
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);

        return $this->on($reflection, exist: true)
                || $this->onMethods($reflection, includeParameters: true, exist: true)
                || $this->onProperties($reflection, exist: true)
                || $this->onConstants($reflection, exist: true);
    }

    /**
     * @return array<Discovered<TAttribute>>
     */
    public function findOn(string|object $class): array
    {
        if ($this->discovered->shouldSkipClassExistence()) {
            return [];
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);

        return array_merge(
            $this->on($reflection),
            $this->onMethods($reflection, includeParameters: true),
            $this->onProperties($reflection),
            $this->onConstants($reflection)
        );
    }

    /**
     * @template TExist of bool
     *
     * @param  TExist  $exist
     * @return (TExist is true ? bool : array<Discovered<TAttribute>>)
     */
    public function on(string|object $class, bool $exist = false)
    {
        if ( ! $this->discovered->isOnClass) {
            return $exist ? false : [];
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);
        $result = [];

        do {
            $attributes = $reflection->getAttributes(...$this->discovered->getArgs());

            if ($this->mapAttributes($reflection, $attributes, $exist, $result) === true) {
                return true;
            }
        } while ($this->ascend && false !== $reflection = $reflection->getParentClass());

        return $exist ? false : $result;
    }

    /**
     * @template TExist of bool
     *
     * @param  TExist  $exist
     * @return (TExist is true ? bool : array<Discovered<TAttribute>>)
     */
    public function onConstants(string|object $class, ?int $filter = null, bool $exist = false)
    {
        if ( ! $this->discovered->isOnClassConstant) {
            return $exist ? false : [];
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);
        $args = $this->discovered->getArgs();
        $result = [];

        foreach ($reflection->getReflectionConstants($filter) as $constant) {
            $attributes = $constant->getAttributes(...$args);

            if ($this->mapAttributes($constant, $attributes, $exist, $result) === true) {
                return true;
            }
        }

        return $exist ? false : $result;
    }

    /**
     * @template TExist of bool
     *
     * @param  TExist  $exist
     * @return (TExist is true ? bool : array<Discovered<TAttribute>>)
     */
    public function onProperties(string|object $class, ?int $filter = null, bool $exist = false)
    {
        if ( ! $this->discovered->isOnProperty) {
            return $exist ? false : [];
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);
        $args = $this->discovered->getArgs();
        $result = [];

        foreach ($reflection->getProperties($filter) as $property) {
            $attributes = $property->getAttributes(...$args);

            if ($this->mapAttributes($property, $attributes, $exist, $result) === true) {
                return true;
            }
        }

        return $exist ? false : $result;
    }

    /**
     * @template TExist of bool
     *
     * @param  TExist  $exist
     * @return (TExist is true ? bool : array<Discovered<TAttribute>>)
     */
    public function onMethods(string|object $class, ?int $filter = null, bool $includeParameters = false, bool $exist = false)
    {
        if ($this->discovered->shouldSkipMethods($includeParameters)) {
            return $exist ? false : [];
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);
        $args = $this->discovered->getArgs();
        $result = [];

        foreach ($reflection->getMethods($filter) as $method) {
            $attributes = $method->getAttributes(...$args);

            if ($this->mapAttributes($method, $attributes, $exist, $result) === true) {
                return true;
            }

            if (
                $includeParameters
                && $this->scanMethod($method, $args, $exist, $result) === true
            ) {
                return true;
            }
        }

        return $exist ? false : $result;
    }

    /**
     * @template TExist of bool
     *
     * @param  TExist  $exist
     * @return (TExist is true ? bool : array<Discovered<TAttribute>>)
     */
    public function onParameters(string|object $class, ?int $filterMethods = null, bool $exist = false)
    {
        if ( ! $this->discovered->isOnParameter) {
            return $exist ? false : [];
        }

        $result = [];
        $args = $this->discovered->getArgs();

        if ($class instanceof ReflectionParameter) {
            return $this->scanParameter($class, $args, $exist, $result) ? true : $result;
        }

        if ($class instanceof ReflectionMethod) {
            return $this->scanMethod($class, $args, $exist, $result) ? true : $result;
        }

        $reflection = $class instanceof ReflectionClass ? $class : $this->reflect($class);

        foreach ($reflection->getMethods($filterMethods) as $method) {
            if ($this->scanMethod($method, $args, $exist, $result)) {
                return true;
            }
        }

        return $exist ? false : $result;
    }

    private function reflect(string $className)
    {
        return new ReflectionClass($className);
    }

    private function scanMethod(ReflectionMethod $method, array $args, bool $exist, array &$result): bool
    {
        foreach ($method->getParameters() as $parameter) {
            if ($this->scanParameter($parameter, $args, $exist, $result)) {
                return true;
            }
        }

        return false;
    }

    private function scanParameter(ReflectionParameter $parameter, array $args, bool $exist, array &$result): bool
    {
        $attributes = $parameter->getAttributes(...$args);

        return $this->mapAttributes($parameter, $attributes, $exist, $result) === true;
    }

    /**
     * @template TExist of bool
     *
     * @param  TExist  $exist
     * @return (TExist is true ? true|null : null)
     */
    private function mapAttributes(
        ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionParameter|ReflectionFunction $target,
        array $attributes,
        bool $exist,
        array &$result
    ): ?true {
        if ($attributes !== []) {
            if ($exist) {
                return true;
            }

            $result = array_merge(
                $result,
                $this->mapIntoDiscovered($target, $attributes)
            );
        }

        return null;
    }

    /**
     * @param  array<ReflectionAttribute>|ReflectionAttribute  $attributes
     * @return array<Discovered>
     */
    private function mapIntoDiscovered(
        ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionParameter|ReflectionFunction $target,
        array|ReflectionAttribute $attributes
    ): array {
        $normalizer = static fn(ReflectionAttribute $attribute) => match (true) {
            $target instanceof ReflectionClass => new Discovered(
                name: $target->getName(),
                parent: null,
                attribute: $attribute->newInstance(),
                target: Target::onClass
            ),
            $target instanceof ReflectionParameter => new Discovered(
                name: $target->getDeclaringFunction()->getName() . '.' . $target->getName(),
                parent: $target->getDeclaringClass()?->getName(),
                attribute: $attribute->newInstance(),
                target: Target::parameter,
                relatedFunction: $target->getDeclaringClass() === null ? $target->getDeclaringFunction()->getName() :
                    null,
            ),
            default => new Discovered(
                name: $target->getName(),
                parent: $target->getDeclaringClass()?->getName(),
                attribute: $attribute->newInstance(),
                target: Target::detectFromReflection($target),
            )
        };

        return $attributes instanceof ReflectionAttribute ? [ $normalizer($attributes) ] :
            array_map($normalizer, $attributes);
    }
}
