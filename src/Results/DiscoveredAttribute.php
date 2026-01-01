<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

/**
 * Class DiscoveredAttribute
 *
 * @internal
 *
 * @author Amondar-SO
 */
final readonly class DiscoveredAttribute
{
    /**
     * DiscoveredAttribute constructor.
     */
    public function __construct(
        public bool $isOnClass,
        public bool $isOnMethod,
        public bool $isOnFunction,
        public bool $isOnProperty,
        public bool $isOnParameter,
        public bool $isOnConstant,
        public bool $isOnClassConstant,
        public bool $isRepeatable,
    ) {
        //
    }

}
