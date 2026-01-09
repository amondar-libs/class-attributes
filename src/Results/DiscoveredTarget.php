<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

use Illuminate\Support\Collection;

/**
 * Class DiscoveredTarget
 *
 * @template Target
 * @template Class
 * @template Method
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredTarget extends Discovered
{
    /**
     * DiscoveredResult constructor.
     *
     * @param  class-string<Target>  $target
     * @param  Collection<int, Class>  $onClass
     * @param  Collection<int, Method>  $onMethods
     */
    public function __construct(
        string $target,
        public Collection $onClass,
        public Collection $onMethods,
    ) {
        parent::__construct($target);
    }

}
