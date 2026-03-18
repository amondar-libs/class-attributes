<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredClass
 *
 * @template Attribute
 *
 * @extends Discovered<string>
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredClass extends Discovered
{
    /**
     * DiscoveredClass constructor.
     *
     * @param  array<int, Attribute>  $attributes
     */
    public function __construct(
        string $target,
        public array $attributes,
    ) {
        parent::__construct($target, null);
    }
}
