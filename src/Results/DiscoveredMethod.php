<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredMethod
 *
 * @internal
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredMethod
{
    /**
     * DiscoveredMethod constructor.
     *
     * @param  array<int, object>  $attributes
     */
    public function __construct(
        public string $method,
        public array $attributes,
    ) {
        //
    }

}
