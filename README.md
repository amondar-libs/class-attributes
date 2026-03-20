# PHP Attributes helpers

This package provides a small, framework-friendly toolkit to work with native PHP 8 attributes on your classes and
methods. It leverages [spatie/php-structure-discoverer](https://github.com/spatie/php-structure-discoverer) for fast and
efficient attribute discovery.

What you get:

- Simple fluent API to read class, method, property, constant, and parameter attributes
- Support for repeatable attributes and inheritance lookups
- Filtering helpers: `onClass()`, `onMethods()`, `onProperties()`, `onConstants()`, `onParameters()`
- Optional caching support for performance in production

Installation:

```bash
composer require amondar-libs/class-attributes
```

Contents:

- [Attributes usage](#attributes-usage)
- [Attribute class usage](#attribute-class-usage)
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

#[Attribute(Attribute::TARGET_METHOD)]
class MethodAttribute
{
    public function __construct(public string $description) {}
}

#[Attribute(Attribute::TARGET_ALL)]
class TagAttribute
{
    public function __construct(public string $description) {}
}
```

### 2) Annotate your classes/methods

```php
#[ClassAttribute(['someData' => 'someValue'])]
class Example
{
    #[TagAttribute('MyConst')]
    public const MY_CONST = 'my-const';

    #[TagAttribute('isOk')]
    public bool $isOk = true;

    #[MethodAttribute('First method description')]
    public function firstMethod() {}

    #[MethodAttribute('Second method description')]
    public function secondMethod() {}
}
```

### 3) Read attributes: Parse helper

The `get()` method returns a `Collection` of `Discovered` objects. Each `Discovered` instance contains:

- `name` — the name of the target (class name, method name, property name, etc.)
- `parent` — the parent/declaring class name (FCQN)
- `attribute` — the attribute instance
- `target` — a `Target` enum case (`onClass`, `method`, `property`, `constant`, `parameter`)

```php
use Amondar\ClassAttributes\Parse;

// Get all discovered attributes on a class (class-level, methods, properties, constants)
$results = Parse::attribute(TagAttribute::class)
    ->on(Example::class)
    ->get();

// $results is a Collection of Discovered instances
foreach ($results as $discovered) {
    echo $discovered->name;              // e.g. 'MY_CONST', 'isOk', 'firstMethod'
    echo $discovered->target->value;     // e.g. 'constant', 'property', 'method'
    echo $discovered->attribute->description; // e.g. 'MyConst'
}
```

### 4) Filter by target

Use dedicated filter methods to narrow results to a specific target type:

```php
// Only class-level attributes
$classAttrs = Parse::attribute(ClassAttribute::class)
    ->on(Example::class)
    ->onClass();

// Only method attributes
$methodAttrs = Parse::attribute(MethodAttribute::class)
    ->on(Example::class)
    ->onMethods();

// Only property attributes
$propAttrs = Parse::attribute(TagAttribute::class)
    ->on(Example::class)
    ->onProperties();

// Only constant attributes
$constAttrs = Parse::attribute(TagAttribute::class)
    ->on(Example::class)
    ->onConstants();

// Only parameter attributes
$paramAttrs = Parse::attribute(RequestAttribute::class)
    ->on(Example::class)
    ->onParameters();
```

### 5) Inheritance lookup

Include parent class attributes in the search using `ascend()`:

```php
$results = Parse::attribute(TagAttribute::class)
    ->on(ChildOfExample::class)
    ->ascend()
    ->get();
```

> That will include attributes from `Example` class and all its parents. 
> Can be useful for resolving repeatable attributes, for example, when a child can add some new functionality.

---

## Attribute class usage

Under the hood, the `Parse` facade delegates to `Amondar\ClassAttributes\Support\Attribute` — a lower-level, reflection-based class you can use directly for fine-grained control.

### Creating an instance

Use the static `for()` factory, passing either a class-string or a `DiscoveredAttribute` instance:

```php
use Amondar\ClassAttributes\Support\Attribute;

$attr = Attribute::for(TagAttribute::class);
```

### Checking if an attribute exists

The `existsOn()` method returns `true` if the attribute is present anywhere on the class (class-level, methods, properties, constants, or parameters):

```php
$attr->existsOn(Example::class); // true / false
```

### Finding all attribute usages on a class

`findOn()` returns an array of `Discovered` objects for every occurrence of the attribute on the given class:

```php
$results = $attr->findOn(Example::class);
// array<Discovered>
```

### Targeted lookups

Each method accepts a class-string, an object, or a `ReflectionClass` instance. They return an array of `Discovered` objects (or `bool` when the `$exist` flag is `true`):

```php
// Class-level attributes only
$attr->on(Example::class);

// Method attributes only (optionally include parameter attributes)
$attr->onMethods(Example::class, includeParameters: true);

// Property attributes only
$attr->onProperties(Example::class);

// Constant attributes only
$attr->onConstants(Example::class);

// Parameter attributes only
$attr->onParameters(Example::class);
```

You can also pass a visibility filter using PHP's `ReflectionMethod`/`ReflectionProperty` filter constants:

```php
$attr->onMethods(Example::class, filter: ReflectionMethod::IS_PUBLIC);
$attr->onProperties(Example::class, filter: ReflectionProperty::IS_PUBLIC);
$attr->onConstants(Example::class, filter: ReflectionClassConstant::IS_PUBLIC);
```

### Inheritance lookup

Just like the `Parse` helper, you can include parent class attributes with `ascend()`:

```php
$attr = Attribute::for(TagAttribute::class)->ascend();

$results = $attr->findOn(ChildOfExample::class);
// Includes attributes from ChildOfExample and all its parents
```

---

## Batch Discovery

You can discover all classes using a specific attribute within given directories.

```php
use Amondar\ClassAttributes\Parse;

$parser = Parse::attribute(ClassAttribute::class);

// Find all classes that use this attribute
$usages = $parser->findUsages(__DIR__ . '/src');
// Returns Collection of \Spatie\StructureDiscoverer\Data\DiscoveredClass

// Discover and parse all attributes for classes in directories
$all = $parser->in(__DIR__ . '/src', __DIR__ . '/tests');
// Returns Collection<class-string, Collection<string, Collection<int, Discovered>>>
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
> Current package ships with `Amondar\\ClassAttributes\\Laravel\\AttributeCacheDriver` out of the box.
---

## License

This library is open-sourced software licensed under the MIT license. See [LICENSE.md](LICENSE.md).
