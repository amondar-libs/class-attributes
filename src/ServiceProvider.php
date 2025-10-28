<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Contracts\AttributesCacheContract;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Class PhpAttributesServiceProvider
 *
 * @author Amondar-SO
 */
final class ServiceProvider extends PackageServiceProvider
{
    /**
     * Configure package
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('php-attributes');
    }

    public function packageBooted(): void
    {
        Application::macro('getBinding', function (string $abstract) {
            $concrete = $this->bindings[ $abstract ] ?? null;

            return match (true) {
                is_array($concrete) => Arr::get(
                    (new ReflectionClosure($concrete[ 'concrete' ]))->getUseVariables(),
                    'concrete'
                ),
                default => $concrete,
            };
        });

        // Run loading for all namespaces registered in `register` sections of service providers.
        AttributesCache::load();
    }

    /**
     * Register any application services.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(
            AttributesCacheContract::class,
            fn() => new Libraries\AttributesCache
        );
    }
}
