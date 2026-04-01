# Horde Autoloader

PSR-0 and PSR-4 compliant autoloader for PHP 8.0+

## Why Horde Autoloader?

Horde Autoloader predates PSR-0 and is mostly maintained for legacy and educational purposes.
Horde itself primarily uses composer autoloader as does everybody else.
Remaining calls into Horde Autoloader from the Horde Framework are going to be removed step by step.

If you need a config-is-code, self-contained, PSR-0 capable autoloader and composer autoloader is not an option, give it a try.
Your reasons are yours.

## Features

- **PSR-4 Support** - Autoloading with namespaces
- **PSR-0 Support** - PEAR-style autoloading without namespaces, underscores as subdir separators.
- **Classpath Mappers** - Application, Prefix, PrefixString patterns
- **PHP 8.0+** - Full type safety and modern features
- **LIFO Ordering** - Last-added mapper searched first
- **Callback Support** - Execute code after class loads
- **No Dependencies** - Self-contained library

## Quick Start

### Modern (PSR-4 with PSR-0 support)

```php
// Autoloader bootstraps itself - require files explicitly
require_once __DIR__ . '/vendor/horde/autoloader/src/Autoloader.php';
require_once __DIR__ . '/vendor/horde/autoloader/src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();
$psr4 = new Psr4(enablePsr0Fallback: false);
$psr4->addNamespace('Acme\Log', __DIR__ . '/vendor/acme/log/src');
$autoloader->addClassPathMapper($psr4);
$autoloader->registerAutoloader();
```

### Legacy (PSR-0 based PSR-0)

```php
// Autoloader bootstraps itself - require files explicitly
require_once __DIR__ . '/vendor/horde/autoloader/lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();
$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/lib')
);
$autoloader->registerAutoloader();
```

## Installation

```bash
composer require horde/autoloader
```

## Documentation

- **[Examples (Legacy PSR-0)](doc/examples-lib-psr0.php)** - PSR-0 usage examples
- **[Examples (Modern)](doc/examples-src-modern.php)** - PSR-4 and modern features
- **[Upgrading Guide](doc/UPGRADING.md)** - Migration from PSR-0 to PSR-4
- **[Changelog](doc/changelog.yml)** - Version history

## Requirements

- PHP 8.0 or higher

## Key Differences: PSR-0 vs PSR-4

| Feature | PSR-0 | PSR-4 |
|---------|-------|-------|
| Underscores | Converted to `/` | Preserved as-is |
| Namespace mapping | Simple path | Prefix → base directory |
| Top-level classes | Allowed | Rejected (requires namespace) |
| Multiple base dirs | No | Yes |

### PSR-0 Example
```php
// Class: Vendor_Package_Class_Name
// Maps to: vendor/Vendor/Package/Class/Name.php
```

### PSR-4 Example
```php
// Prefix: Vendor\Package → /vendor/package/src
// Class: \Vendor\Package\Class_Name
// Maps to: /vendor/package/src/Class_Name.php (underscore preserved!)
```

## Available Mappers

### Modern (src/)

- **`Psr4`** - PSR-4 compliant autoloader with namespace prefix mapping
- **`DefaultMapper`** - PSR-0 compliant mapper (underscores → directories)
- **`Application`** - Application-specific MVC pattern mapper
- **`Prefix`** - Regex-based prefix matching
- **`PrefixString`** - Fast case-insensitive string prefix matching

### Legacy (lib/)

- **`Horde_Autoloader_ClassPathMapper_Default`** - PSR-0 mapper
- **`Horde_Autoloader_ClassPathMapper_Application`** - Application mapper
- **`Horde_Autoloader_ClassPathMapper_Prefix`** - Regex prefix
- **`Horde_Autoloader_ClassPathMapper_PrefixString`** - String prefix

## Testing

```bash
# Run all tests
phpunit

# Run specific test suites
phpunit --testsuite unit        # Legacy PSR-0 tests
phpunit --testsuite modern      # Modern PSR-4 tests
phpunit --testsuite integration # Real file loading tests
```

## License

LGPL-2.1-only

## Links

- **Homepage**: https://www.horde.org/libraries/Horde_Autoloader
- **Repository**: https://github.com/horde/Autoloader
- **PSR-4 Specification**: https://www.php-fig.org/psr/psr-4/
- **PSR-0 Specification**: https://www.php-fig.org/psr/psr-0/

## Credits

- **Authors**: Bob Mckee, Chuck Hagenbuch
- **Maintainers**: Jan Schneider, Ralf Lang