<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Support;

use Amondar\ClassAttributes\Results\DiscoveredResult;

/**
 * Class Discover
 *
 * @author Amondar-SO
 */
abstract readonly class Discover
{
    abstract public function discover(bool $isRepeatable): ?DiscoveredResult;

    protected function removeDuplicates(array $attributes): array
    {
        $result = [];

        if (count($attributes) === 1) {
            return $attributes;
        }

        foreach ($attributes as $attribute) {
            $data = get_object_vars($attribute);

            if ($data !== []) {
                $key = hash('xxh3', serialize($attribute));

                if ( ! isset($result[ $key ])) {
                    $result[ $key ] = $attribute;
                }
            } elseif ( ! isset($result[ $key = class_basename($attribute) ])) {
                $result[ $key ] = $attribute;
            }
        }

        return array_values($result);
    }
}
