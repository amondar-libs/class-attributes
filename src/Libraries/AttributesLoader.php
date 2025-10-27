<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Libraries;

use Amondar\ClassAttributes\Contracts\AttributesLoaderContract;
use Amondar\ClassAttributes\Enums\LoadType;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use ReflectionException;

/**
 * Class AttributesLoader
 *
 * @author Amondar-SO
 */
class AttributesLoader implements AttributesLoaderContract
{
    /**
     * @var array<string, array{attribute: string, type: LoadType, loader: ?Closure}>
     */
    private array $toLoad = [];

    /**
     * Creates and returns a new instance of the called class.
     *
     * @return static A new instance of the called class.
     */
    public static function new(): static
    {
        return new static;
    }

    /**
     * Adds a configuration to the 'toLoad' property for handling data loading.
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute
     * @param Closure(TAttribute|Collection<class-string<TAttribute>, TAttribute|Collection<int, TAttribute>>):
     *                                                                                           mixed|null
     *                                                                                           $customLoader
     * @return static Returns the current instance for method chaining.
     */
    public function add(string $attribute, LoadType $type = LoadType::SimpleClass, bool $ascend = false, ?Closure $customLoader = null): static
    {
        $this->toLoad[] = [
            'attribute' => $attribute,
            'type'      => $type,
            'loader'    => $customLoader,
            'ascend'    => $ascend,
        ];

        return $this;
    }

    /**
     * Loads data based on the configurations provided in the 'toLoad' property.
     *
     * @param  string  $abstract  The abstract entity or class name to load attributes or methods from.
     * @return Collection<class-string, object> An associative array where keys are attributes and values are the
     *                                          corresponding loaded data.
     *
     * @throws ReflectionException
     */
    public function load(string $abstract): Collection
    {
        $result = new Collection;

        $abstract = App::getInstance()->getBinding($abstract) ?? $abstract;

        foreach ($this->toLoad as $loadSettings) {
            $loader = $loadSettings[ 'loader' ] ?? null;

            $data = match ($loadSettings[ 'type' ]) {
                LoadType::SimpleClass => (new Attributes($abstract))
                    ->loadFromClass($loadSettings[ 'attribute' ], $loadSettings[ 'ascend' ]),

                LoadType::Method => (new Attributes($abstract))
                    ->loadFromMethods($loadSettings[ 'attribute' ]),

                LoadType::RepeatableClass => (new Attributes($abstract))
                    ->loadAsRepeatable($loadSettings[ 'attribute' ], $loadSettings[ 'ascend' ]),
            };

            if (
                (
                    $data instanceof Collection && $data->isNotEmpty()
                ) || (
                    ! $data instanceof Collection && ! empty($data)
                )
            ) {
                $result->put(
                    $loadSettings[ 'attribute' ],
                    $loader instanceof Closure ? $loader($data) : $data
                );
            }
        }

        return $result;
    }
}
