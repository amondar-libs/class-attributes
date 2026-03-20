<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

use Amondar\ClassAttributes\Enums\Target;

/**
 * Class Discovered
 *
 * @template TAttribute of object
 *
 * @author Amondar-SO
 */
final readonly class Discovered
{
    /**
     * Discovered constructor.
     *
     * @param  string|class-string  $name
     * @param  class-string|null  $parent
     * @param  TAttribute  $attribute
     */
    public function __construct(
        public string $name,
        public ?string $parent,
        public object $attribute,
        public Target $target,
        public ?string $relatedFunction = null,
    ) {}

}
