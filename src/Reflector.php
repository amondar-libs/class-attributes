<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Closure;
use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionUnionType;

/**
 * Class Reflector
 *
 * @author Amondar-SO
 */
final class Reflector
{
    /**
     * Get the specified class attribute, optionally following an inheritance chain.
     *
     * @template TAttribute of object
     *
     * @param  object|class-string  $objectOrClass
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     *
     * @throws ReflectionException
     */
    public static function getClassAttribute(object|string $objectOrClass, string $attribute, $ascend = false)
    {
        return Reflector::getClassAttributes($objectOrClass, $attribute, $ascend)->flatten()->first();
    }

    /**
     * Get the specified class attribute(s), optionally following an inheritance chain.
     *
     * @template TTarget of object
     * @template TAttribute of object
     *
     * @param  TTarget|class-string<TTarget>  $objectOrClass
     * @param  class-string<TAttribute>  $attribute
     * @return ($includeParents is true ? Collection<class-string<contravariant TTarget>, Collection<int, TAttribute>>
     *                          : Collection<int, TAttribute>)
     *
     * @throws ReflectionException
     */
    public static function getClassAttributes(object|string $objectOrClass, string $attribute, $includeParents = false)
    {
        $reflectionClass = new ReflectionClass($objectOrClass);

        $attributes = [];

        do {
            $attributes[ $reflectionClass->name ] = new Collection(array_map(
                fn(ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
                $reflectionClass->getAttributes($attribute)
            ));
        } while ($includeParents && false !== $reflectionClass = $reflectionClass->getParentClass());

        return $includeParents ? new Collection($attributes) : reset($attributes);
    }

    /**
     * Get the specified method attribute from given class.
     *
     * @template TAttribute of object
     *
     * @param  object|class-string  $objectOrClass
     * @param  class-string<TAttribute>  $attribute
     * @return Collection<string, TAttribute>
     *
     * @throws ReflectionException
     */
    public static function getMethodsWithAttribute(object|string $objectOrClass, string $attribute)
    {
        $reflectionClass = new ReflectionClass($objectOrClass);

        $attributes = [];

        foreach ($reflectionClass->getMethods() as $method) {
            $attributes[ $method->name ] = new Collection(array_map(
                fn(ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
                $method->getAttributes($attribute)
            ));
        }

        return new Collection(array_filter($attributes, fn(Collection $collection) => ! $collection->isEmpty()));
    }

    /**
     * Retrieves a collection of all classes within the specified namespace.
     *
     * @param  class-string  $psrInterface
     * @param  array<string>|string|null  $psrNamespace
     * @return Collection<int, string> A collection containing class names from the given namespace.
     *
     * @throws Exception
     */
    public static function getClassesThatImplements(string $psrInterface, array|string|null $psrNamespace = null): Collection
    {
        return self::getClassesInNamespace($psrNamespace)
            ->filter(fn(string $className) => in_array($psrInterface, class_implements($className)))
            ->values();
    }

    /**
     * Retrieves a collection of all classes within the specified namespace.
     *
     * @param  array<string>|string|null  $psrNamespace
     * @return Collection<string, string> A collection containing class names from the given namespace.
     *
     * @throws Exception
     */
    public static function getClassesInNamespace(array|string|null $psrNamespace = null): Collection
    {
        $psrNamespace = Arr::wrap($psrNamespace) ?? [ 'App' ];

        return new Collection(
            array_combine(
                $psrNamespace,
                array_map(
                    fn(string $namespace) => ClassFinder::getClassesInNamespace(
                        $namespace,
                        ClassFinder::RECURSIVE_MODE
                    ),
                    $psrNamespace
                )
            )
        );
    }

    /**
     * Checks if the given object or class is an instance of, or matches, any class in the provided list of classes.
     *
     * @param  object|string  $objectOrClass  The object or class name to check.
     * @param  array<string>  $classes  An array of class names to check against.
     * @return bool True if the object or class matches or is an instance of any class in the list, otherwise false.
     *
     * @throws InvalidArgumentException
     */
    public static function isInstanceOfAny(object|string $objectOrClass, array $classes): bool
    {
        return array_any(
            $classes,
            fn($class) => (is_string($objectOrClass) && $objectOrClass === $class) || $objectOrClass instanceof $class
        );
    }

    /**
     * Extracts and returns an array of non-builtin, non-self/ static types
     * specified in the return type of a given closure.
     *
     * @param  Closure  $closure  The closure whose return types are to be analyzed.
     * @return string[] An array of names of non-builtin, non-self/static return types.
     *
     * @throws ReflectionException
     */
    public static function getClosureTypes(Closure $closure): array
    {
        $reflection = new ReflectionFunction($closure);

        if (
            $reflection->getReturnType() === null ||
            $reflection->getReturnType() instanceof ReflectionIntersectionType
        ) {
            return [];
        }

        $types = $reflection->getReturnType() instanceof ReflectionUnionType
            ? $reflection->getReturnType()->getTypes()
            : [ $reflection->getReturnType() ];

        return (new Collection($types))
            ->reject(fn($type) => $type->isBuiltin())
            ->reject(fn($type) => in_array($type->getName(), [ 'static', 'self' ]))
            ->map(fn($type) => $type->getName())
            ->values()
            ->all();
    }
}
