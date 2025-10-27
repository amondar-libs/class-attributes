<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Contracts;

use Amondar\ClassAttributes\Enums\LoadType;
use Closure;
use Illuminate\Support\Collection;
use ReflectionException;

/**
 * Interface AttributesLoaderContract
 *
 * @author Amondar-SO
 */
interface AttributesLoaderContract
{
    /**
     * Adds a configuration to the 'toLoad' property for handling data loading.
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute  The
     *                                               name
     *                                               of
     *                                               the
     *                                               attribute
     *                                               to
     *                                               load.
     * @param  LoadType  $type  The
     *                          type
     *                          of
     *                          loading
     *                          mechanism
     *                          to
     *                          use.
     * @param  bool  $ascend  Whether
     *                        to
     *                        ascend
     *                        the
     *                        class
     *                        hierarchy
     *                        during
     *                        loading.
     * @param Closure(Collection<class-string<TAttribute>, TAttribute|Collection<int, TAttribute>>): mixed|null
     *                                                                                $customLoader A custom loader
     *                                                                                function to handle the loading
     *                                                                                process.
     * @return static Returns the current instance for method chaining.
     */
    public function add(string $attribute, LoadType $type = LoadType::SimpleClass, bool $ascend = false, ?Closure $customLoader = null): static;

    /**
     * Loads data based on the configurations provided in the 'toLoad' property.
     *
     * @param  string  $abstract  The abstract entity or class name to load attributes or methods from.
     * @return Collection<class-string, object> An associative array where keys are attributes and values are the
     *                                          corresponding loaded data.
     *
     * @throws ReflectionException
     */
    public function load(string $abstract): Collection;
}
