<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredMethod
 *
 * @template T
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
     * @param  array<int, T>  $attributes
     */
    public function __construct(
        string $target,
        public array $attributes,
    ) {
        parent::__construct($target);
    }

}
