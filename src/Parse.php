<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Conditions\AttributeDiscoverCondition;
use Amondar\ClassAttributes\Exceptions\AttributeException;
use Amondar\ClassAttributes\Exceptions\ParseException;
use Amondar\ClassAttributes\Results\DiscoveredAttribute;
use Amondar\ClassAttributes\Results\DiscoveredMethod;
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
class Parse
{
    /**
     * Attribute constructor.
     */
    public function __construct(
        protected readonly string $attributeClassName,
        protected readonly ?string $onClass = null,
        protected readonly bool $ascend = false,
        protected readonly ?DiscoverCacheDriver $cacheStore = null,
        protected ?DiscoveredAttribute $discoveredAttribute = null,
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
        ?DiscoverCacheDriver $cacheStore = null,
        ?DiscoveredAttribute $discoveredAttribute = null
    ): static {
        return new static(
            $attributeClassName,
            onClass: $onClass,
            ascend: $ascend,
            cacheStore: $cacheStore,
            discoveredAttribute: $discoveredAttribute
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
            cacheStore: $this->cacheStore,
            discoveredAttribute: $this->discoveredAttribute
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
            cacheStore: $this->cacheStore,
            discoveredAttribute: $this->discoveredAttribute
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
            cacheStore: $cacheStore,
            discoveredAttribute: $this->discoveredAttribute
        );
    }

    /**
     * Creates a new instance of the current class with caching disabled.
     */
    public function withoutCache(): static
    {
        return static::make(
            $this->attributeClassName,
            onClass: $this->onClass,
            ascend: $this->ascend,
            cacheStore: null,
            discoveredAttribute: $this->discoveredAttribute
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
     * @return DiscoveredResult<DiscoveredMethod>|null
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
                    $this->discoverAttribute()->isRepeatable
                );

            return $this->cache($cacheKey, $result);
        }

        return $this->cacheStore?->get($cacheKey)[ 0 ] ?? null;
    }

    /**
     * Retrieves and returns an array of discovered attributes.
     *
     * @return DiscoveredResult<object>|null
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
                    $this->discoverAttribute()->isRepeatable
                );

            return $this->cache($cacheKey, $result);
        }

        return $this->cacheStore?->get($cacheKey)[ 0 ] ?? null;
    }

    /**
     * Retrieves and returns all discovered results based on the provided directories.
     *
     * @param  mixed  ...$dirs  A variable number of directories to search for usages.
     * @return array<int, DiscoveredResult<DiscoveredResult<object>|DiscoveredResult<DiscoveredMethod>>>
     *
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    public function all(...$dirs): array
    {
        $all = [];

        foreach ($this->findUsages(...$dirs) as $usage) {
            $class = $usage->getFcqn();
            $attribute = $this->discoverAttribute();
            $parse = $this->withoutCache()->on($class);

            $result = [];

            if ($attribute->isOnClass) {
                $result = array_merge($result, $parse->get()?->attributes ?? []);
            }

            if ($attribute->isOnMethod) {
                $result = array_merge($result, $parse->inMethods()?->attributes ?? []);
            }

            if ($result !== []) {
                $all[] = new DiscoveredResult(
                    $class,
                    array_values($result)
                );
            }
        }

        $cacheKey = $this->getCacheKey(...$dirs);

        return ! $this->cacheStore?->has($cacheKey) ?
            $this->cache($cacheKey, $all)
            : $this->cacheStore?->get($cacheKey);
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
     * Retrieves and returns detailed settings of the specified attribute class.
     *
     * @return DiscoveredAttribute An instance containing the attribute's settings, including target types and
     *                             repeatability.
     *
     * @throws ParseException If the attribute class does not exist or is not a valid attribute.
     */
    public function discoverAttribute(): DiscoveredAttribute
    {
        // Check to discover a result in the cache.
        if ($this->discoveredAttribute !== null) {
            return $this->discoveredAttribute;
        }

        if ( ! class_exists($this->attributeClassName)) {
            throw AttributeException::noAttributeClassFound(
                $this->attributeClassName
            );
        }

        $ref = new ReflectionClass($this->attributeClassName);

        // The "Attribute" meta-attribute is what defines TARGET_* and IS_REPEATABLE
        $attrs = $ref->getAttributes(Attribute::class);

        if ($attrs === []) {
            // Not an attribute class at all (no #[Attribute] declared)
            throw AttributeException::classIsNotAnAttribute($this->attributeClassName);
        }

        /** @var Attribute $meta */
        $meta = $attrs[ 0 ]->newInstance();

        return $this->discoveredAttribute = new DiscoveredAttribute(
            isOnClass: (bool) ($meta->flags & Attribute::TARGET_CLASS),
            isOnMethod: (bool) ($meta->flags & Attribute::TARGET_METHOD),
            isOnFunction: (bool) ($meta->flags & Attribute::TARGET_FUNCTION),
            isOnProperty: (bool) ($meta->flags & Attribute::TARGET_PROPERTY),
            isOnParameter: (bool) ($meta->flags & Attribute::TARGET_PARAMETER),
            isOnConstant: PHP_VERSION_ID >= 80500 && ($meta->flags & Attribute::TARGET_CONSTANT),
            isOnClassConstant: (bool) ($meta->flags & Attribute::TARGET_CLASS_CONSTANT),
            isRepeatable: (bool) ($meta->flags & Attribute::IS_REPEATABLE)
        );
    }

    /**
     * Stores the provided result in the cache with the given key, then returns the result.
     *
     * @param  string  $key  The key under which the result will be stored in the cache.
     * @param  DiscoveredResult|array|null  $result  The data to be cached and returned.
     */
    protected function cache(string $key, DiscoveredResult|array|null $result): DiscoveredResult|array|null
    {
        $this->cacheStore?->put($key, Arr::wrap($result ?? []));

        return $result;
    }
}
