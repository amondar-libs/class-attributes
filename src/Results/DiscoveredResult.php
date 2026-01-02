<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredResult
 *
 * @template Target
 * @template Attribute
 *
 * @extends Discovered<Target>
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
     * @param  class-string<Target>  $target
     * @param  array<int, Attribute>  $attributes
     */
    public function __construct(
        string $target,
        public array $attributes,
    ) {
        parent::__construct($target);
    }

}
