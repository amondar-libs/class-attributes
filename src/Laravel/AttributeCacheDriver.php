<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Laravel;

use Illuminate\Contracts\Cache\Repository;
use Spatie\StructureDiscoverer\Cache\DiscoverCacheDriver;

/**
 * Class AttributeCacheDriver
 *
 * @author Amondar-SO
 */
class AttributeCacheDriver implements DiscoverCacheDriver
{
    public function __construct(
        public string $name,
        public ?string $store = null,
    ) {}

    public function has(string $id): bool
    {
        return $this->resolveCacheRepository()->has($this->resolveCacheKey($id));
    }

    /** @return array<mixed> */
    public function get(string $id): array
    {
        return $this->resolveCacheRepository()->get($this->resolveCacheKey($id));
    }

    /** @param  array<mixed> $discovered */
    public function put(string $id, array $discovered): void
    {
        $this->resolveCacheRepository()->put($this->resolveCacheKey($id), $discovered);
    }

    public function forget(string $id): void
    {
        $this->resolveCacheRepository()->forget($this->resolveCacheKey($id));
    }

    public function flush(): void
    {
        $this->resolveCacheRepository()->flush();
    }

    private function resolveCacheRepository(): Repository
    {
        $store = cache()->store($this->store);

        return $store->supportsTags()
            ? $store->tags([$this->name])
            : $store;
    }

    private function resolveCacheKey(string $id): string
    {
        return "{$this->name}-parsed-attributes-{$id}";
    }
}
