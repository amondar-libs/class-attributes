<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Libraries;

use Amondar\ClassAttributes\Contracts\AttributesCacheContract;
use Amondar\ClassAttributes\Contracts\AttributesLoaderContract;
use Amondar\ClassAttributes\Reflector;
use Illuminate\Support\Collection;
use ReflectionException;

/**
 * Class AttributesMap
 *
 * @author Amondar-SO
 */
final class AttributesCache implements AttributesCacheContract
{
    /**
     * @var array<string, AttributesLoaderContract>
     */
    private static array $namespaces = [];

    /**
     * @var Collection<string, Collection<string, mixed>>
     */
    private Collection $cache;

    public function __construct()
    {
        $this->cache = new Collection;
    }

    /**
     * Adds a namespace or an array of namespaces to the current list of namespaces.
     *
     * @param  array<string, AttributesLoaderContract>  $namespaces
     */
    public static function addNamespace(array $namespaces): void
    {
        self::$namespaces = array_merge(self::$namespaces, $namespaces);
    }

    /**
     * Loads and caches classes from the defined namespaces if the cache is empty.
     *
     * @throws ReflectionException
     */
    public function load(): void
    {
        if ($this->cache->isNotEmpty()) {
            return;
        }

        $namespaces = array_keys(self::$namespaces);

        foreach (Reflector::getClassesInNamespace($namespaces) as $namespace => $classes) {
            $loader = self::$namespaces[ $namespace ];

            foreach ($classes as $abstract) {
                $this->cache->put($abstract, $loader->load($abstract));
            }
        }
    }

    /**
     * Retrieves a specific attribute from a cached entry based on the abstract identifier.
     *
     * @param  string  $abstract  The identifier for the cached entry.
     * @param  string  $attribute  The attribute to retrieve from the cached entry.
     * @return mixed The value of the specified attribute, or null if not found.
     */
    public function get(string $abstract, string $attribute): mixed
    {
        return $this->cache->get($abstract)?->get($attribute);
    }
}
