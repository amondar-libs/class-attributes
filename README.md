# PHP Attributes helpers

This package provides a small, framework-friendly toolkit to work with native PHP 8 attributes on your classes and
methods. It leverages [spatie/php-structure-discoverer](https://github.com/spatie/php-structure-discoverer) for fast and
efficient attribute discovery.

What you get:

- Simple fluent API to read class and method attributes
- Support for repeatable attributes and inheritance lookups
- Optional caching support for performance in production

Contents:

- [Attributes usage](#attributes-usage)
- [Batch Discovery](#batch-discovery)
- [Caching](#caching)
- [License](#license)

---

## Attributes usage

Below are quick, copy-pasteable examples showing how to declare attributes and how to read them using this package.

The package works with native PHP attributes (PHP >= 8.3). It provides a fluent `Amondar\ClassAttributes\Parse` helper.

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

### 3) Read attributes: Parse helper

```php
use Amondar\ClassAttributes\Parse;

// Get class attributes
$result = Parse::attribute(ClassAttribute::class)
    ->on(Example::class)
    ->get();

// $result is an instance of DiscoveredResult
// $result->attributes contains an array of ClassAttribute instances
$attribute = $result->attributes[0] ?? null;

// Include parents in search (useful for inheritance)
$result = Parse::attribute(ClassAttribute::class)
    ->on(ChildOfExample::class)
    ->ascend()
    ->get();

// Read method attributes
$result = Parse::attribute(MethodAttribute::class)
    ->on(Example::class)
    ->inMethods();

// $result->attributes contains an array of DiscoveredMethod instances
foreach ($result->attributes as $discoveredMethod) {
    echo $discoveredMethod->target; // method name, e.g. 'firstMethod'
    $attr = $discoveredMethod->attributes[0]; // MethodAttribute instance
}
```

---

## Batch Discovery

You can discover all classes using a specific attribute within given directories.

```php
use Amondar\ClassAttributes\Parse;

$parser = Parse::attribute(ClassAttribute::class);

// Find all classes that use this attribute
$usages = $parser->findUsages(__DIR__ . '/src'); 
// Returns array of \Spatie\StructureDiscoverer\Data\DiscoveredClass

// Discover and parse all attributes (both class and methods) for classes in directories
$all = $parser->all(__DIR__ . '/src', __DIR__ . '/tests');
// Returns array of DiscoveredResult
```

---

## Caching

For production environments, you can enable caching to avoid repeated reflection and file scanning.

The package uses `spatie/php-structure-discoverer` caching mechanism.
You can use any driver that implements `Spatie\StructureDiscoverer\Cache\DiscoverCacheDriver`.

```php
use Amondar\ClassAttributes\Parse;
use Spatie\StructureDiscoverer\Cache\LaravelDiscoverCacheDriver;

$parser = Parse::attribute(ClassAttribute::class)
    ->withCache(new LaravelDiscoverCacheDriver('my-cache-key'));

// Subsequent calls will be cached
$result = $parser->on(Example::class)->get();
```

> **Note:** `LaravelDiscoverCacheDriver` is just one example. You can use any driver supported
> by [spatie/php-structure-discoverer official docs](https://github.com/spatie/php-structure-discoverer#caching).

---

## License

This library is open-sourced software licensed under the MIT license. See [LICENSE.md](LICENSE.md).
