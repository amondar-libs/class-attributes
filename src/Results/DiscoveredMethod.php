<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredMethod
 *
 * @template Attribute
 *
 * @extends Discovered<string>
 *
 * @internal
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredMethod extends Discovered
{
    /**
     * DiscoveredMethod constructor.
     *
     * @param  array<int, Attribute>  $attributes
     */
    public function __construct(
        string $target,
        public array $attributes,
    ) {
        parent::__construct($target);
    }

}
