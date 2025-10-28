# PHP Attributes helpers

This package provides a small, framework-friendly toolkit to work with native PHP 8 attributes on your classes and methods.

What you get:
- Simple helpers to read class and method attributes
- Support for repeatable attributes and inheritance lookups
- A tiny, opt-in cache that can pre-scan namespaces and store attribute results for fast lookup

Contents:
- Attributes usage
- AttributesCache usage
- Troubleshooting
- License

---

## Attributes usage

Below are quick, copy-pasteable examples showing how to declare attributes and how to read them using this package.

The package works with native PHP attributes (PHP >= 8.0). It provides:
- Low-level helpers: `Amondar\ClassAttributes\Reflector` and `Amondar\ClassAttributes\Libraries\Attributes`
- A convenience loader for batching: `Amondar\ClassAttributes\Libraries\AttributesLoader`

### 1) Define your attributes

Create attribute classes using native PHP Attribute metadata.

```php
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ClassAttribute
{
    public function __construct(public array $someData) {}
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ClassAttributeRepeatable
{
    public function __construct(public array $someData) {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class MethodAttribute
{
    public function __construct(public string $description) {}
}
```

### 2) Annotate your classes/methods

```php
#[ClassAttribute(['someData' => 'someValue'])]
class Example
{
    #[MethodAttribute('First method description')]
    public function firstMethod() {}

    #[MethodAttribute('Second method description')]
    public function secondMethod() {}
}
```

### 3) Read attributes: low-level Reflector

```php
use Amondar\ClassAttributes\Reflector;

// Single class attribute instance (or null)
$attr = Reflector::getClassAttribute(Example::class, ClassAttribute::class);

// All class attributes (non-repeatable returns a 1-item collection)
$attrs = Reflector::getClassAttributes(Example::class, ClassAttribute::class);

// Include parents in search (useful for inheritance)
$inherited = Reflector::getClassAttributes(ChildOfExample::class, ClassAttribute::class, includeParents: true);
// $inherited is a Collection keyed by class name -> Collection of attributes

// Methods that have a specific attribute
$methodsWith = Reflector::getMethodsWithAttribute(Example::class, MethodAttribute::class);
// Returns a Collection: methodName => Collection of MethodAttribute
```

Notes:
- All collections are `Illuminate\Support\Collection` instances.
- Reflector methods may throw `ReflectionException` if classes cannot be inspected.

### 4) Read attributes: fluent Attributes helper

```php
use Amondar\ClassAttributes\Libraries\Attributes;

$helper = new Attributes(Example::class);

// Single, non-repeatable class attribute (or null)
$classAttr = $helper->loadFromClass(ClassAttribute::class);

// Repeatable class attributes
$repeatable = $helper->loadAsRepeatable(ClassAttributeRepeatable::class);
// Returns Collection<int, ClassAttributeRepeatable>

// Inheritance-aware repeatable search
$repeatableInherited = $helper->loadAsRepeatable(ClassAttributeRepeatable::class, ascend: true);
// Returns Collection<int, ClassAttributeRepeatable> (flattened over parents)

// Methods with attribute
$methods = $helper->loadFromMethods(MethodAttribute::class);
// Collection<string methodName, Collection<int, MethodAttribute>>
```

### 5) Batch loading with AttributesLoader

If you need multiple attributes at once (and optional transformations), use the loader.

```php
use Amondar\ClassAttributes\Libraries\AttributesLoader;
use Amondar\ClassAttributes\Enums\LoadType;
use Illuminate\Support\Collection;

$result = AttributesLoader::new()
    ->add(ClassAttribute::class, LoadType::SimpleClass) // single class attribute
    ->add(ClassAttributeRepeatable::class, LoadType::RepeatableClass, ascend: true) // repeatable + ascend
    ->add(
        MethodAttribute::class,
        LoadType::Method,
        customLoader: function (Collection $methods) {
            // Example transform: map to [methodName => description]
            return $methods->map(fn (Collection $attrs) => $attrs->map->description);
        }
    )
    ->load(Example::class);

// $result is a Collection keyed by attribute FQCN
//   - ClassAttribute::class => ClassAttribute instance
//   - ClassAttributeRepeatable::class => Collection<int, ClassAttributeRepeatable>
//   - MethodAttribute::class => Collection<string methodName, Collection<int, string description>>
```

Tips:
- The `ascend` flag lets you include parent classes when searching for class attributes.
- The `customLoader` lets you post-process the collected data and store any shape you find useful.

---

## AttributesCache usage

`AttributesCache` is an in-memory helper that can pre-scan selected namespaces for classes and build a "per-class" map of attribute data using a configured `AttributesLoader`. It is optimized for framework usage (e.g., Laravel), but it remains framework-agnostic in spirit.

What it does:
- You provide one or more PSR-4 namespaces and, for each, an `AttributesLoader` that describes what attributes to collect from each class in that namespace.
- It scans classes in those namespaces, collects the requested attributes, and keeps them in memory for fast lookups.

Important notes:
- Cache is in-memory only (per PHP process/request). There is no persistent storage. In a typical Laravel app, each HTTP request starts with a fresh cache; in long‑running workers you may want to restart the process if your attributes change.
- There is no explicit "clear" method; re-create/rebind the cache service or restart the process to refresh.

### 1) Register service provider (Laravel)

If you use Laravel, the package service provider is auto-discovered. It binds the cache singleton that backs the facade and triggers `AttributesCache::load()` after the application is booted. You can still call `AttributesCache::load()` manually if you build the cache at a specific moment.

### 2) Prepare an AttributesLoader for your namespace

Describe what you want to load from classes in a given namespace. For example, collect a class-level attribute and all method-level attributes.

```php
use Amondar\ClassAttributes\Libraries\AttributesLoader;
use Amondar\ClassAttributes\Enums\LoadType;

$loader = AttributesLoader::new()
    ->add(\App\Domain\Attributes\ClassAttribute::class, LoadType::SimpleClass)
    ->add(\App\Domain\Attributes\MethodAttribute::class, LoadType::Method);
```

- `LoadType::SimpleClass` — load a single, non-repeatable class attribute instance (optionally with inheritance via the `ascend` flag).
- `LoadType::RepeatableClass` — load repeatable class attributes (optionally with inheritance via the `ascend` flag).
- `LoadType::Method` — load method attributes and return a map of `methodName => attributes`.

You can provide a custom transformation callback when calling `add()` to store any shape you need.

### 3) Register one or more namespaces

Use the facade to register a namespace and associate it with its loader. Keys are namespace strings, values are the configured `AttributesLoader`.

```php
use Amondar\ClassAttributes\AttributesCache;

AttributesCache::addNamespace([
    'App\\Domain\\Services' => $loader,
]);
```

You can add multiple namespaces by calling `addNamespace()` again or by passing more pairs in the same array.

Where to put this:
- In Laravel, do it early in the app lifecycle, e.g., in a service provider `boot()` method or a module bootstrap file.

### 4) Build the cache (scan and load)

Call `load()` once after registering namespaces (unless you rely on auto-loading performed by the package service provider after boot).

```php
AttributesCache::load();
```

- Subsequent calls are no-ops while the cache is non-empty.

### 5) Read from the cache

To read cached data for a specific class, use `get($abstract, $attributeFqcn)`. It returns whatever shape your loader stored for that attribute (often an object instance or a Collection).

```php
// Using the facade (proxied to the singleton)
$single = AttributesCache::get(\App\Domain\Services\SomeService::class, \App\Domain\Attributes\ClassAttribute::class);

// Or via DI/container using the contract (Laravel example)
use Amondar\ClassAttributes\Contracts\AttributesCacheContract;

$cache = app(AttributesCacheContract::class);
$single = $cache->get(\App\Domain\Services\SomeService::class, \App\Domain\Attributes\ClassAttribute::class);
```

If you configured the loader to load repeatable class attributes:

```php
$repeatables = AttributesCache::get(
    \App\Domain\Services\SomeService::class,
    \App\Domain\Attributes\ClassAttributeRepeatable::class
);
// Typically a Collection<int, ClassAttributeRepeatable>
```

If you configured method attributes with a custom transformation:

```php
$methodsMap = AttributesCache::get(
    \App\Domain\Services\SomeService::class,
    \App\Domain\Attributes\MethodAttribute::class
);
// Example shape: Collection<string methodName, Collection<int, string description>>
```

### 6) Putting it all together (Laravel example)

```php
namespace App\Providers;

use Amondar\ClassAttributes\AttributesCache;
use Amondar\ClassAttributes\Libraries\AttributesLoader;
use Amondar\ClassAttributes\Enums\LoadType;
use Illuminate\Support\ServiceProvider;

class DomainAttributesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $loader = AttributesLoader::new()
            ->add(\App\Domain\Attributes\ClassAttribute::class, LoadType::SimpleClass)
            ->add(\App\Domain\Attributes\ClassAttributeRepeatable::class, LoadType::RepeatableClass, ascend: true)
            ->add(\App\Domain\Attributes\MethodAttribute::class, LoadType::Method);

        AttributesCache::addNamespace([
            'App\\Domain\\Services' => $loader,
        ]);

        AttributesCache::load();
    }
}
```

Now, anywhere in your application you can query the cache:

```php
$cfg = AttributesCache::get(\App\Domain\Services\EmailSender::class, \App\Domain\Attributes\ClassAttribute::class);
```

Troubleshooting:
- Ensure your classes reside under the namespace(s) you registered and are autoloadable.
- If nothing shows up, verify your PSR-4 autoload config and that your attribute classes and targets are imported correctly.
- For long-running processes, restart workers after changing attribute annotations.

---

## License

This library is open-sourced software licensed under the MIT license. See [LICENSE.md](LICENSE.md).
