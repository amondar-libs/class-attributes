<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredConstant
 *
 * @template Attribute
 *
 * @extends Discovered<string>
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredConstant extends Discovered
{
    /**
     * DiscoveredConstant constructor.
     *
     * @param  array<int, Attribute>  $attributes
     */
    public function __construct(
        string $target,
        string $parent,
        public array $attributes,
    ) {
        parent::__construct($target, $parent);
    }
}
