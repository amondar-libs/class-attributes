<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Conditions\AttributeDiscoverCondition;
use Amondar\ClassAttributes\Exceptions\AttributeException;
use Amondar\ClassAttributes\Exceptions\ParseException;
use Amondar\ClassAttributes\Results\DiscoveredAttribute;
use Amondar\ClassAttributes\Results\DiscoveredMethod;
use Amondar\ClassAttributes\Results\DiscoveredResult;
use Amondar\ClassAttributes\Results\DiscoveredTarget;
use Amondar\ClassAttributes\Support\ClassWithAttributeDiscover;
use Amondar\ClassAttributes\Support\MethodsWithAttributeDiscover;
use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\StructureDiscoverer\Cache\DiscoverCacheDriver;
use Spatie\StructureDiscoverer\Discover;

/**
 * Class Attribute
 *
 * @template Class
 * @template Attribute
 *
 * @immutable
 *
 * @author Amondar-SO
 */
class Parse
{
    /**
     * Constructor for initializing the core properties of the class.
     *
     *
     * @param  class-string<Attribute>  $attributeClassName  The fully qualified class name of the attribute to be
     *                                                       discovered.
     * @param  class-string<Class>|null  $onClass  An optional class name to restrict the discovery scope.
     * @param  bool  $ascend  Whether to ascend through parent classes during the discovery process.
     * @param  DiscoverCacheDriver|null  $cacheStore  An optional cache driver instance for storing discovery results.
     * @param  DiscoveredAttribute|null  $discoveredAttribute  An optional pre-discovered attribute instance.
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
     * Creates a new instance of the class with the specified parameters.
     *
     * @param  class-string<Attribute>  $attributeClassName  The fully qualified class name of the attribute to be
     *                                                       used.
     * @param  class-string<Class>|null  $onClass  The class name on which the attribute should be located, or null if
     *                                             unspecified.
     * @param  bool  $ascend  Whether to traverse up the class hierarchy during discovery.
     * @param  DiscoverCacheDriver|null  $cacheStore  The cache driver to be used for caching discovery results, or
     *                                                null if not caching.
     * @param  DiscoveredAttribute|null  $discoveredAttribute  An optional pre-discovered attribute instance, or null.
     * @return static<Class, Attribute> A new instance of the class configured with the specified parameters.
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
     * Creates and returns an instance of the class with the given attribute.
     *
     * @param  class-string<Attribute>  $attribute  The name of the attribute class.
     * @return static<Class, Attribute>
     */
    public static function attribute(string $attribute): static
    {
        return static::make($attribute);
    }

    /**
     * Sets the ascended property to true and returns a new instance.
     *
     * @return static<Class, Attribute>
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
     * @param  class-string<Class>  $onClass  The class name to set the context on.
     * @return static<Class, Attribute>
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
     * @return static<Class, Attribute>
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
     *
     * @return static<Class, Attribute>
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
     * Discovers and returns the result with methods associated with the specified attribute class name.
     *
     * @return DiscoveredResult<DiscoveredMethod<Attribute>>|null
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
     * Retrieves and returns discovered attributes.
     *
     * @return DiscoveredResult<Class, Attribute>|null
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
     * @param  string  ...$dirs  A variable number of directories to search for usages.
     * @return Collection<int, DiscoveredTarget<object, Attribute>>
     *
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    public function all(...$dirs): Collection
    {
        $all = [];

        $attribute = $this->discoverAttribute();

        foreach ($this->findUsages(...$dirs) as $usage) {
            $class = $usage->getFcqn();
            $parse = $this->withoutCache()->on($class);

            $result = [
                'class'   => [],
                'methods' => [],
            ];

            if ($attribute->isOnClass) {
                $result['class'] = $parse->get()?->attributes ?? [];
            }

            if ($attribute->isOnMethod) {
                $result['methods'] = $parse->inMethods()?->attributes ?? [];
            }

            if ($result['class'] !== [] || $result['methods'] !== []) {
                $all[] = new DiscoveredTarget(
                    target: $class,
                    onClass: collect($result['class']),
                    onMethods: collect($result['methods'])
                );
            }
        }

        $cacheKey = $this->getCacheKey(...$dirs);

        return new Collection(
            ! $this->cacheStore?->has($cacheKey) ?
            $this->cache($cacheKey, $all)
            : $this->cacheStore?->get($cacheKey)
        );
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
