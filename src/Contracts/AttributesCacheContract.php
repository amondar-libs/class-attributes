<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Contracts;

/**
 * Interface AttributesCacheContract
 *
 * @author Amondar-SO
 */
interface AttributesCacheContract
{
    /**
     * Adds one or more namespaces to the application configuration.
     *
     * @param  array<string, \Amondar\ClassAttributes\Libraries\AttributesLoader>  $namespaces  An associative array
     *                                                                                          where the key is the namespace name
     *                                                                                          and the value is the namespace path.
     */
    public static function addNamespace(array $namespaces): void;

    public function load(): void;
}
