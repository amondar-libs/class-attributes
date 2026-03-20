<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Enums\Target;
use Amondar\ClassAttributes\Exceptions\ParseException;
use Amondar\ClassAttributes\Results\Discovered;
use Amondar\ClassAttributes\Results\DiscoveredAttribute;
use Attribute;
use Illuminate\Support\Collection;
use Spatie\StructureDiscoverer\Cache\DiscoverCacheDriver;
use Spatie\StructureDiscoverer\Discover;

/**
 * Class Attribute
 *
 * @template Class
 *
 * @immutable
 *
 * @author Amondar-SO
 */
final readonly class Parse
{
    public DiscoveredAttribute $discoveredAttribute;

    public function __construct(
        public string $attributeClassName,
        public ?string $onClass = null,
        public bool $ascend = false,
        public ?DiscoverCacheDriver $cacheStore = null,
        ?DiscoveredAttribute $discoveredAttribute = null,
    ) {
        $this->discoveredAttribute = $discoveredAttribute ?? DiscoveredAttribute::from($this->attributeClassName);
    }

    /**
     * Creates a new instance of the class with the specified parameters.
     *
     * @param  class-string<Attribute>  $attributeClassName  The fully qualified class name of the attribute to be
     *                                                       used.
     * @param  class-string<Class>|null  $onClass  The class name on which the attribute should be located,
     *                                             or null if unspecified.
     * @param  DiscoverCacheDriver|null  $cacheStore  The cache driver to be used for caching discovery results,
     *                                                or null if not caching.
     * @param  DiscoveredAttribute|null  $discoveredAttribute  An optional pre-discovered attribute instance, or null.
     * @return static<Class, Attribute> A new instance of the class configured with the specified parameters.
     */
    public static function make(
        string $attributeClassName,
        ?string $onClass = null,
        bool $ascend = false,
        ?DiscoverCacheDriver $cacheStore = null,
        ?DiscoveredAttribute $discoveredAttribute = null
    ): self {
        return new self(
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
    public static function attribute(string $attribute): self
    {
        return self::make($attribute);
    }

    /**
     * Sets the class context for the attribute.
     *
     * @param  class-string<Class>  $onClass  The class name to set the context on.
     * @return static<Class, Attribute>
     */
    public function on(string $onClass): self
    {
        return self::make(
            $this->attributeClassName,
            onClass: $onClass,
            ascend: $this->ascend,
            cacheStore: $this->cacheStore,
            discoveredAttribute: $this->discoveredAttribute
        );
    }

    /**
     * Enables ascending behavior for the current operation.
     *
     * @return static<Class, Attribute>
     */
    public function ascend(): self
    {
        return self::make(
            $this->attributeClassName,
            onClass: $this->onClass,
            ascend: true,
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
    public function withCache(?DiscoverCacheDriver $cacheStore): self
    {
        return self::make(
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
    public function withoutCache(): Parse
    {
        return Parse::make(
            $this->attributeClassName,
            onClass: $this->onClass,
            ascend: $this->ascend,
            discoveredAttribute: $this->discoveredAttribute
        );
    }

    /**
     * Searches for and returns usages based on the specified directories.
     *
     * @param  string  ...$directories  A variable number of directories to search for usages.
     * @return Collection<int, \Spatie\StructureDiscoverer\Data\DiscoveredClass>
     *
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    public function findUsages(...$directories): Collection
    {
        $discover = Discover::in(...$directories)->classes();

        if ($this->cacheStore) {
            $discover = $discover->withCache(
                id: $this->getCacheKey(...$directories),
                cache: $this->cacheStore
            );
        }

        $parser = $this->makeParser();

        $discovered = $discover
            ->custom(fn($d) => $parser->existsOn($d->getFcqn()))
            ->full()
            ->get();

        return new Collection($discovered);
    }

    public function onMethods(): Collection
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('onMethods');
        }

        return $this->get()->filter(fn(Discovered $discovered) => $discovered->target === Target::method);
    }

    public function onClass(): Collection
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('onClass');
        }

        return $this->get()->filter(fn(Discovered $discovered) => $discovered->target === Target::onClass);
    }

    public function onProperties(): Collection
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('onProperties');
        }

        return $this->get()->filter(fn(Discovered $discovered) => $discovered->target === Target::property);
    }

    public function onConstants(): Collection
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('onConstants');
        }

        return $this->get()->filter(fn(Discovered $discovered) => $discovered->target === Target::constant);
    }

    public function onParameters(): Collection
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('onConstants');
        }

        return $this->get()->filter(fn(Discovered $discovered) => $discovered->target === Target::parameter);
    }

    /**
     * Retrieves and returns discovered attributes.
     *
     * @return Collection<int, Discovered>
     *
     * @throws ParseException If no target class is found for discovery.
     */
    public function get(): Collection
    {
        if ($this->onClass === null) {
            throw ParseException::noClassTargetFound('get');
        }

        $cacheKey = $this->getCacheKey();

        if ( ! $this->cacheStore?->has($cacheKey)) {
            return $this->cache(
                $cacheKey,
                new Collection($this->makeParser()->findOn($this->onClass))
            );
        }

        return $this->restoreFromCache($cacheKey);
    }

    /**
     * Retrieves and returns all discovered results based on the provided directories.
     *
     * @param  string  ...$dirs  A variable number of directories to search for usages.
     * @return Collection<class-string, Collection<string, Collection<int, Discovered>>>
     *
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    public function in(string ...$dirs): Collection
    {
        $cacheKey = $this->getCacheKey(...$dirs);

        if ( ! $this->cacheStore?->has($cacheKey)) {
            $all = new Collection;

            foreach ($this->findUsages(...$dirs) as $usage) {
                $class = $usage->getFcqn();
                $parse = $this->on($class);

                $all->put($class, new Collection($parse->get()->groupBy('target')));
            }

            return $this->cache($cacheKey, $all);
        }

        return $this->restoreFromCache($cacheKey);
    }

    public function getCacheKey(string ...$directories): string
    {
        $key = hash('xxh3', $this->attributeClassName) . '::';

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

    private function cache(string $key, Collection $result): Collection
    {
        $this->cacheStore?->put($key, $result->toArray());

        return $result;
    }

    private function restoreFromCache(string $key): Collection
    {
        return new Collection($this->cacheStore?->get($key) ?? []);
    }

    private function makeParser(): Support\Attribute
    {
        $parser = Support\Attribute::for($this->discoveredAttribute);

        return $this->ascend ? $parser->ascend() : $parser;
    }
}
