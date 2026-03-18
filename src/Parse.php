<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Enums\Target;
use Amondar\ClassAttributes\Exceptions\AttributeException;
use Amondar\ClassAttributes\Exceptions\ParseException;
use Amondar\ClassAttributes\Results\Discovered;
use Amondar\ClassAttributes\Results\DiscoveredAttribute;
use Attribute;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionParameter;
use Spatie\Attributes\Attributes;
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
        private string $attributeClassName,
        private ?string $onClass = null,
        private ?DiscoverCacheDriver $cacheStore = null,
        ?DiscoveredAttribute $discoveredAttribute = null,
    ) {
        if ($discoveredAttribute === null) {
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

            $this->discoveredAttribute = new DiscoveredAttribute(
                isOnClass: (bool) ($meta->flags & Attribute::TARGET_CLASS),
                isOnMethod: (bool) ($meta->flags & Attribute::TARGET_METHOD),
                isOnFunction: (bool) ($meta->flags & Attribute::TARGET_FUNCTION),
                isOnProperty: (bool) ($meta->flags & Attribute::TARGET_PROPERTY),
                isOnParameter: (bool) ($meta->flags & Attribute::TARGET_PARAMETER),
                isOnConstant: PHP_VERSION_ID >= 80500 && ($meta->flags & Attribute::TARGET_CONSTANT),
                isOnClassConstant: (bool) ($meta->flags & Attribute::TARGET_CLASS_CONSTANT),
                isRepeatable: (bool) ($meta->flags & Attribute::IS_REPEATABLE)
            );
        } else {
            $this->discoveredAttribute = $discoveredAttribute;
        }
    }

    /**
     * Creates a new instance of the class with the specified parameters.
     *
     * @param  class-string<Attribute>  $attributeClassName  The fully qualified class name of the attribute to be
     *                                                       used.
     * @param  class-string<Class>|null  $onClass  The class name on which the attribute should be located, or null if
     *                                             unspecified.
     * @param  DiscoverCacheDriver|null  $cacheStore  The cache driver to be used for caching discovery results, or
     *                                                null if not caching.
     * @param  DiscoveredAttribute|null  $discoveredAttribute  An optional pre-discovered attribute instance, or null.
     * @return static<Class, Attribute> A new instance of the class configured with the specified parameters.
     */
    public static function make(
        string $attributeClassName,
        ?string $onClass = null,
        ?DiscoverCacheDriver $cacheStore = null,
        ?DiscoveredAttribute $discoveredAttribute = null
    ): Parse {
        return new Parse(
            $attributeClassName,
            onClass: $onClass,
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
    public static function attribute(string $attribute): Parse
    {
        return Parse::make($attribute);
    }

    /**
     * Sets the class context for the attribute.
     *
     * @param  class-string<Class>  $onClass  The class name to set the context on.
     * @return static<Class, Attribute>
     */
    public function on(string $onClass): Parse
    {
        return Parse::make(
            $this->attributeClassName,
            onClass: $onClass,
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
    public function withCache(?DiscoverCacheDriver $cacheStore): Parse
    {
        return Parse::make(
            $this->attributeClassName,
            onClass: $this->onClass,
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

        return $discover
            ->custom(fn($d) => Attributes::has($d->getFcqn(), $this->attributeClassName))
            ->full()
            ->get();
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

    public function onParameters()
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
            $parsed = new Collection;

            foreach (Attributes::find($this->onClass, $this->attributeClassName) as $result) {
                $target = $result->target;

                $parsed->push(match (true) {
                    $target instanceof ReflectionClass => new Discovered(
                        name: $this->onClass,
                        parent: null,
                        attribute: $result->attribute,
                        target: Target::onClass
                    ),
                    $target instanceof ReflectionParameter => new Discovered(
                        name: $target->getName(),
                        parent: $target->getDeclaringClass() ? $this->onClass : null,
                        attribute: $result->attribute,
                        target: Target::parameter,
                        relatedMethod: $target->getDeclaringFunction()->getName(),
                    ),
                    default => new Discovered(
                        name: $target->getName(),
                        parent: $this->onClass,
                        attribute: $result->attribute,
                        target: Target::detectFromReflection($target),
                    )
                });
            }

            return $this->cache($cacheKey, $parsed);
        }

        return $this->restoreFromCache($cacheKey);
    }

    /**
     * Retrieves and returns all discovered results based on the provided directories.
     *
     * @param  string  ...$dirs  A variable number of directories to search for usages.
     * @return Collection<class-string, Collection<string, Discovered>>
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

                $all->put($this->onClass, $parse->get()->groupBy(fn(Discovered $d) => $d->target->value));
            }

            return $this->cache($cacheKey, $all);
        }

        return $this->restoreFromCache($cacheKey);
    }

    public function getCacheKey(...$directories): string
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
}
