<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredResult
 *
 * @template T
 *
 * @internal
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredResult extends Discovered
{
    /**
     * DiscoveredResult constructor.
     *
     * @param  array<int, T>  $attributes
     */
    public function __construct(
        string $target,
        public array $attributes,
    ) {
        parent::__construct($target);
    }

}
