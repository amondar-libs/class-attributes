<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Results;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Class DiscoveredCollection
 *
 * @template TValue
 *
 * @implements ArrayAccess<int, TValue>
 *
 * @author Amondar-SO
 */
class DiscoveredCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The items contained in the collection.
     *
     * @var array<int, TValue>
     */
    protected $items = [];

    /**
     * DiscoveredCollection constructor.
     *
     * @param  array<int, TValue>  $items
     */
    public function __construct($items = [])
    {
        $this->items = $items instanceof DiscoveredCollection ? $items->all() : $items;
    }

    /**
     * Filters the items in the collection based on the specified target value.
     *
     * @param  string  $target  The target value used to filter the collection.
     */
    public function whereTarget(string $target): static
    {
        return new static(array_filter($this->items, fn($item) => $item->target === $target));
    }

    /**
     * Adds one or more values to the collection.
     *
     * @param  TValue  ...$values  The values to be added to the collection.
     */
    public function push(...$values): static
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Put an item in the collection by key.
     *
     * @param  int  $key
     * @param  TValue  $value
     */
    public function put($key, $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Returns a new collection with re-indexed values from the current collection.
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * Retrieves all items from the collection.
     *
     * @return array<int, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator<int, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  int  $key
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  int  $key
     * @return TValue
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  int|null  $key
     * @param  TValue  $value
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  int  $key
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }
}
