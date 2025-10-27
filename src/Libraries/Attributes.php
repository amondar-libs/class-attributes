<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Libraries;

use Amondar\ClassAttributes\Reflector;
use Illuminate\Support\Collection;
use ReflectionException;

/**
 * Class Attributes
 *
 * @template TClass of object
 *
 * @author Amondar-SO
 */
readonly class Attributes
{
    /**
     * @param  class-string<TClass>  $className
     */
    public function __construct(public string $className) {}

    /**
     * Loads data or performs an operation based on the provided attribute name.
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute  The name of the attribute to load or process.
     * @return Collection<string, TAttribute>
     *
     * @throws ReflectionException
     */
    public function loadFromMethods(string $attribute)
    {
        return Reflector::getMethodsWithAttribute($this->className, $attribute);
    }

    /**
     * Retrieves the specified attribute from the class.
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute  The name of the attribute to retrieve.
     * @param  bool  $ascend  Whether to search parent classes for the attribute if it is not found
     *                        in the current class.
     * @return TAttribute|null The value of the requested attribute, or null if not found.
     *
     * @throws ReflectionException
     */
    public function loadFromClass(string $attribute, bool $ascend = false)
    {
        return Reflector::getClassAttribute($this->className, $attribute, $ascend);
    }

    /**
     * Retrieves the specified attribute from the class.
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute  The name of the attribute to retrieve.
     * @param  bool  $ascend  Whether to search parent classes for the attribute if it is not found
     *                        in the current class.
     * @return ($includeParents is true ? Collection<class-string<contravariant TClass>, Collection<int, TAttribute>>
     *                           : Collection<int, TAttribute>)]
     *
     * @throws ReflectionException
     */
    public function loadAsRepeatable(string $attribute, bool $ascend = false)
    {
        $data = Reflector::getClassAttributes($this->className, $attribute, $ascend);

        return ! $ascend ? $data : $data->flatten(1);
    }
}
