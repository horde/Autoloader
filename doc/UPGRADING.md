# Upgrading to Modern Horde\Autoloader

This guide helps you migrate from the legacy PSR-0 autoloader (`lib/`) to the modern PSR-4/PSR-0 autoloader (`src/`).

## Table of Contents

- [Why Upgrade?](#why-upgrade)
- [Quick Start](#quick-start)
- [API Changes](#api-changes)
- [PSR-4 Support](#psr-4-support)
- [Migration Strategies](#migration-strategies)
- [Backward Compatibility](#backward-compatibility)

## Why Upgrade?

The modern autoloader in `src/` offers:

- **PSR-4 Support**: Modern namespace-to-directory mapping
- **PHP 8.0+ Features**: Full type safety with typed properties and return types
- **Better Performance**: Optimized for modern PHP
- **Strict Types**: `declare(strict_types=1)` throughout
- **Future-Proof**: Active development and new features

The legacy `lib/` autoloader remains available for backward compatibility.

## Why Not Composer?

If your code is best served by using Composer's builtin PSR-0 and PSR-4 autoloader, use it. It's what Horde.org itself does.
If you have specific reasons why you want to use Horde Autoloader, migrate from the lib/ implementation to the src/ implementation.

## Quick Start

### Before (PSR-0 Legacy)

```php
// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();
$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/lib')
);
$autoloader->registerAutoloader();
```

### After (Modern with PSR-4)

```php
// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();
$psr4 = new Psr4(enablePsr0Fallback: false);
$psr4->addNamespace('Acme\Log', __DIR__ . '/vendor/acme/log/src');
$autoloader->addClassPathMapper($psr4);
$autoloader->registerAutoloader();
```

## API Changes

### Namespace Changes

| Legacy (PSR-0)                              | Modern (PSR-4)                          |
|---------------------------------------------|-----------------------------------------|
| `Horde_Autoloader`                          | `Horde\Autoloader\Autoloader`           |
| `Horde_Autoloader_ClassPathMapper`          | `Horde\Autoloader\ClassPathMapper`      |
| `Horde_Autoloader_ClassPathMapper_Default`  | `Horde\Autoloader\ClassPathMapper\DefaultMapper` |
| `Horde_Autoloader_ClassPathMapper_Application` | `Horde\Autoloader\ClassPathMapper\Application` |
| `Horde_Autoloader_ClassPathMapper_Prefix`   | `Horde\Autoloader\ClassPathMapper\Prefix` |
| `Horde_Autoloader_ClassPathMapper_PrefixString` | `Horde\Autoloader\ClassPathMapper\PrefixString` |
| N/A                                         | `Horde\Autoloader\ClassPathMapper\Psr4` (NEW) |

### Method Signatures

All methods now have full type declarations:

```php
// Before (no types)
public function addClassPathMapper($mapper)
{
    // ...
}

// After (fully typed)
public function addClassPathMapper(ClassPathMapper $mapper): self
{
    // ...
}
```

### Property Access

Properties use typed declarations:

```php
// Before
private $_mappers = array();

// After
private array $mappers = [];
```

### Constructor Changes

Modern mappers use constructor property promotion:

```php
// Before
public function __construct($includePath)
{
    $this->_includePath = $includePath;
}

// After
public function __construct(
    private readonly string $includePath
) {}
```

## PSR-4 Support

The major new feature is PSR-4 autoloading:

### Key Differences: PSR-0 vs PSR-4

| Feature | PSR-0 | PSR-4 |
|---------|-------|-------|
| Underscores | Converted to `/` | Preserved as-is |
| Namespace prefix | No mapping | Maps to base directory |
| Top-level classes | Allowed | Rejected (requires namespace) |
| Multiple base dirs | No | Yes |

### PSR-0 Example

```php
// Class: Vendor_Package_Class_Name
// Maps to: vendor/Vendor/Package/Class/Name.php
//          (underscores → directory separators)
```

### PSR-4 Example

```php
// Namespace prefix: Vendor\Package → /vendor/package/src
// Class: \Vendor\Package\Class_Name
// Maps to: /vendor/package/src/Class_Name.php
//         (underscore PRESERVED in filename)
```

### Using PSR-4

```php
// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();
$psr4 = new Psr4(enablePsr0Fallback: false);

// Register namespace prefix → base directory mapping
$psr4->addNamespace('Acme\Log', '/vendor/acme/log/src');
$psr4->addNamespace('Symfony\Component', '/vendor/symfony/component');

$autoloader->addClassPathMapper($psr4);
$autoloader->registerAutoloader();
```

### Multiple Base Directories

PSR-4 supports multiple base directories for the same namespace:

```php
$psr4 = new Psr4(enablePsr0Fallback: false);

// Register multiple base directories for same namespace
$psr4->addNamespace('Acme\Package', '/vendor/acme/package/src');
$psr4->addNamespace('Acme\Package', '/vendor/acme/package/tests');

// Searches both directories:
// \Acme\Package\Foo → tries src/Foo.php, then tests/Foo.php
```

### PSR-0 Fallback

Enable PSR-0 fallback for top-level classes:

```php
// With fallback for legacy top-level classes
$psr4 = new Psr4(
    enablePsr0Fallback: true,
    psr0BasePath: __DIR__ . '/lib'
);

$psr4->addNamespace('Vendor\Package', '/vendor/package/src');

// Namespaced: uses PSR-4
// \Vendor\Package\Class_Name → /vendor/package/src/Class_Name.php

// Top-level: falls back to PSR-0
// Old_Style_Class → lib/Old/Style/Class.php
```

## Migration Strategies

### Strategy 1: Pure PSR-4 Migration

1. Restructure code to use namespaces
2. Move files to match PSR-4 structure
3. Update class names to use namespaces
4. Configure PSR-4 mapper

```php
// Before: lib/My/Old/Class.php
class My_Old_Class {}

// After: src/My/Old/NewClass.php
namespace My\Old;
class NewClass {}
```

### Strategy 2: Application-Specific

Keep using Application mapper for MVC patterns:

```php
// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Application;

$autoloader = new Autoloader();

$appMapper = new Application(__DIR__ . '/app');
$appMapper->addMapping('Controller', 'controllers');
$appMapper->addMapping('Model', 'models');

$autoloader->addClassPathMapper($appMapper);
$autoloader->registerAutoloader();

// MyApp_UserList_Controller → app/controllers/UserList.php
```

### Strategy 3: Gradual Migration (Not Recommended)

Use both old and new autoloaders side-by-side:

```php
// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;
use Horde\Autoloader\ClassPathMapper\DefaultMapper;

$autoloader = new Autoloader();

// Modern PSR-4 for new code
$psr4 = new Psr4(enablePsr0Fallback: false);
$psr4->addNamespace('MyApp\Modern', __DIR__ . '/src');

// Legacy PSR-0 for old code
$psr0 = new DefaultMapper(__DIR__ . '/lib');

// Add both (LIFO: last added searched first)
$autoloader->addClassPathMapper($psr0);  // Legacy fallback
$autoloader->addClassPathMapper($psr4);  // Modern first

$autoloader->registerAutoloader();
```

When you MUST run multiple autoloaders stacked on each other, i.e. Horde Autoloader and Composer Autoloader or any legacy project specific loaders,
try to keep it as simple as possible. Try to make sure any specific namespace or class prefix is only served by one implementation.
Horde Autoloader is built to play nice with others but we cannot always assume the inverse case to be true.

## Backward Compatibility

### Using Legacy Classes

The legacy `lib/` classes still work:

```php
// Old code continues to work unchanged
$autoloader = new Horde_Autoloader();
$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/lib')
);
$autoloader->registerAutoloader();
```

### Mixing Old and New

You can use both in the same project:

```php
// Mix legacy and modern
// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;  // Modern

$autoloader = new Autoloader();
$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/lib')  // Legacy mapper
);
$autoloader->registerAutoloader();
```

### Return Type Compatibility

The modern implementation uses union types (`string|false`) which are compatible with the untyped legacy returns.

### No Breaking Changes

The core autoloader behavior is identical - only the implementation details changed:

- Same LIFO order for mappers
- Same callback mechanism
- Same SPL registration
- Same file loading behavior

## Troubleshooting

### PSR-4 Class Not Found

**Problem**: Class not loading with PSR-4

**Check**:
1. Namespace prefix registered? `$psr4->addNamespace('Vendor\Package', '/path')`
2. Class has namespace? PSR-4 requires at least `Vendor\Namespace`
3. File path correct? Verify base directory + relative class path
4. File exists? Check filesystem

### Underscore Issues

**Problem**: Class with underscore not found

**PSR-0**: Underscore → directory separator
```php
// My_Class_Name → My/Class/Name.php
```

**PSR-4**: Underscore preserved
```php
// \Vendor\Package\My_Class_Name → /vendor/package/src/My_Class_Name.php
```

### Top-Level Class Rejected

**Problem**: Top-level class fails with PSR-4

**Solution**: Enable PSR-0 fallback or use pure PSR-0 mapper

```php
// Option 1: PSR-4 with fallback
$psr4 = new Psr4(enablePsr0Fallback: true, psr0BasePath: '/lib');

// Option 2: Use PSR-0 for top-level classes
$autoloader->addClassPathMapper(new DefaultMapper('/lib'));
```

## See Also

- [Examples (Legacy PSR-0)](examples-lib-psr0.php)
- [Examples (Modern)](examples-src-modern.php)
- [PSR-4 Specification](https://www.php-fig.org/psr/psr-4/)
- [PSR-0 Specification](https://www.php-fig.org/psr/psr-0/)
