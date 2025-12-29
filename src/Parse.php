<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Conditions\AttributeDiscoverCondition;
use Amondar\ClassAttributes\Exceptions\ParseException;
use Amondar\ClassAttributes\Results\DiscoveredResult;
use Amondar\ClassAttributes\Support\ClassWithAttributeDiscover;
use Amondar\ClassAttributes\Support\MethodsWithAttributeDiscover;
use Attribute;
use Illuminate\Support\Arr;
use ReflectionClass;
use Spatie\StructureDiscoverer\Cache\DiscoverCacheDriver;
use Spatie\StructureDiscoverer\Discover;

/**
 * Class Attribute
 *
 * @immutable
 *
 * @author Amondar-SO
 */
readonly class Parse
{
    /**
     * Attribute constructor.
     */
    public function __construct(
        protected string $attributeClassName,
        protected ?string $onClass = null,
        protected bool $ascend = false,
        protected ?DiscoverCacheDriver $cacheStore = null
    ) {
        //
    }

    /**
     * Creates and returns an instance of the class with the given attribute.
     *
     * @param  string  $attribute  The name of the attribute class.
     */
    public static function attribute(string $attribute): static
    {
        return static::make($attribute);
    }

    /**
     * Creates a new instance of the class.
     *
     * @param  string  $attributeClassName  The name of the attribute
     *                                      class to instantiate.
     */
    public static function make(
        string $attributeClassName,
        ?string $onClass = null,
        bool $ascend = false,
        ?DiscoverCacheDriver $cacheStore = null
    ): static {
        return new static(
            $attributeClassName,
            onClass: $onClass,
            ascend: $ascend,
            cacheStore: $cacheStore
        );
    }

    /**
     * Sets the ascend property to true and returns a new instance.
     */
    public function ascend(): static
    {
        return static::make(
            $this->attributeClassName,
            onClass: $this->onClass,
            ascend: true,
            cacheStore: $this->cacheStore
        );
    }

    /**
     * Sets the class context for the attribute.
     *
     * @param  string  $onClass  The class name to set the context on.
     */
    public function on(string $onClass): static
    {
        return static::make(
            $this->attributeClassName,
            onClass: $onClass,
            ascend: $this->ascend,
            cacheStore: $this->cacheStore
        );
    }

    /**
     * Sets a cache store to be used for caching operations.
     *
     * @param  DiscoverCacheDriver|null  $cacheStore  The cache store instance or null if caching is not required.
     */
    public function withCache(?DiscoverCacheDriver $cacheStore): static
    {
        return static::make(
            $this->attributeClassName,
            onClass: $this->onClass,
            ascend: $this->ascend,
            cacheStore: $cacheStore
        );
    }

    /**
     * Searches for and returns usages based on the specified directories.
     *
     * @param  string  ...$directories  A variable number of directories to search for usages.
     * @return array<int, \Spatie\StructureDiscoverer\Data\DiscoveredClass>
     *
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    public function findUsages(...$directories): array
    {
        $discover = Discover::in(...$directories)->classes();

        if ($this->cacheStore) {
            $discover = $discover->withCache(
                id: $this->getCacheKey(...$directories),
                cache: $this->cacheStore
            );
        }

        return $discover->custom(new AttributeDiscoverCondition(
            $this->attributeClassName,
            $this->ascend
        ))
            ->full()
            ->get();
    }

    /**
     * Discovers and returns an array of methods associated with the specified attribute class name.
     *
     * @throws ParseException If no class target is found for the discovery process.
     */
    public function inMethods(): ?DiscoveredResult
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('inMethods');
        }

        $cacheKey = $this->getCacheKey();

        if ( ! $this->cacheStore?->has($cacheKey)) {

            $result = (new MethodsWithAttributeDiscover($this->attributeClassName, $this->onClass))
                ->discover(
                    $this->isAttributeRepeatable()
                );

            return $this->cache($cacheKey, $result);
        }

        return $this->cacheStore?->get($cacheKey)[ 0 ];
    }

    /**
     * Retrieves and returns an array of discovered attributes.
     *
     * @throws ParseException If no target class is found for discovery.
     */
    public function get(): ?DiscoveredResult
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('get');
        }

        $cacheKey = $this->getCacheKey();

        if ( ! $this->cacheStore?->has($cacheKey)) {

            $result = (new ClassWithAttributeDiscover($this->attributeClassName, $this->onClass, $this->ascend))
                ->discover(
                    $this->isAttributeRepeatable()
                );

            return $this->cache($cacheKey, $result);
        }

        return $this->cacheStore?->get($cacheKey)[ 0 ];
    }

    /**
     * Generates and returns a cache key based on the provided directories.
     *
     * @param  mixed  ...$directories  A variable number of directories used to generate the cache key.
     */
    public function getCacheKey(...$directories): string
    {
        $key = $this->attributeClassName . '::';

        if ($this->ascend) {
            $key .= 'ascend:';
        }

        if ($this->onClass) {
            $key .= hash('xxh3', $this->onClass) . ':';
        }

        if ($directories !== []) {
            $key .= hash('xxh3', implode('|', $directories));
        } else {
            // Remove the last ":"
            $key = mb_substr($key, 0, -1);
        }

        return $key;
    }

    /**
     * Determines if the given attribute class is repeatable.
     */
    protected function isAttributeRepeatable(): bool
    {
        if ( ! class_exists($this->attributeClassName)) {
            return false;
        }

        $ref = new ReflectionClass($this->attributeClassName);

        // The "Attribute" meta-attribute is what defines TARGET_* and IS_REPEATABLE
        $attrs = $ref->getAttributes(Attribute::class);

        if ($attrs === []) {
            // Not an attribute class at all (no #[Attribute] declared)
            return false;
        }

        /** @var Attribute $meta */
        $meta = $attrs[ 0 ]->newInstance();

        return (bool) ($meta->flags & Attribute::IS_REPEATABLE);
    }

    /**
     * Stores the provided result in the cache with the given key, then returns the result.
     *
     * @param  string  $key  The key under which the result will be stored in the cache.
     * @param  DiscoveredResult|null  $result  The data to be cached and returned.
     */
    protected function cache(string $key, ?DiscoveredResult $result): ?DiscoveredResult
    {
        $this->cacheStore?->put($key, Arr::wrap($result ?? []));

        return $result;
    }
}
