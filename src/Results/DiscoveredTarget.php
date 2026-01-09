<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

use Illuminate\Support\Collection;

/**
 * Class DiscoveredTarget
 *
 * @template Target of object
 *
 * @template-covariant Attribute
 *
 * @extends Discovered<Target>
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredTarget extends Discovered
{
    /**
     * DiscoveredResult constructor.
     *
     * @param  class-string<Target>  $target
     * @param  Collection<int, Attribute>  $onClass
     * @param  Collection<int, DiscoveredMethod<Attribute>>  $onMethods
     */
    public function __construct(
        string $target,
        public Collection $onClass,
        public Collection $onMethods,
    ) {
        parent::__construct($target);
    }

}
