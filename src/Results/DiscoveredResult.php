<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredResult
 *
 * @internal
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredResult
{
    /**
     * DiscoveredResult constructor.
     *
     * @param  array<int, object | DiscoveredMethod>  $attributes
     */
    public function __construct(
        public string $targetClass,
        public array $attributes,
    ) {
        //
    }

}
