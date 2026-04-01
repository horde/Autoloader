<?php

/**
 * Modern Autoloader Examples (src/)
 *
 * These examples demonstrate usage of the modern PSR-4/PSR-0 autoloader
 * from the src/ directory using Horde\Autoloader namespace.
 *
 * All features are fully typed with PHP 8.0+ support.
 *
 * IMPORTANT: Autoloader bootstraps itself - don't use composer's autoloader!
 * The src/ files use require_once internally to bootstrap their dependencies.
 */

// ============================================================================
// Example 1: PSR-4 Autoloader (Recommended)
// ============================================================================

// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();

// Create PSR-4 mapper
$psr4 = new Psr4(enablePsr0Fallback: false);

// Register namespace prefixes → base directories
$psr4->addNamespace('Acme\Log', __DIR__ . '/vendor/acme/log/src');
$psr4->addNamespace('Symfony\Component\HttpFoundation', __DIR__ . '/vendor/symfony/http-foundation');

$autoloader->addClassPathMapper($psr4);
$autoloader->registerAutoloader();

// PSR-4 mapping (underscores preserved!):
// \Acme\Log\Writer\FileWriter → vendor/acme/log/src/Writer/FileWriter.php
// \Acme\Log\Writer\File_Writer → vendor/acme/log/src/Writer/File_Writer.php

// ============================================================================
// Example 2: PSR-4 with Multiple Base Directories
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();
$psr4 = new Psr4(enablePsr0Fallback: false);

// Same namespace can map to multiple directories (src and tests)
$psr4->addNamespace('Acme\Package', __DIR__ . '/vendor/acme/package/src');
$psr4->addNamespace('Acme\Package', __DIR__ . '/vendor/acme/package/tests');

$autoloader->addClassPathMapper($psr4);
$autoloader->registerAutoloader();

// Searches both directories:
// \Acme\Package\ClassName → vendor/acme/package/src/ClassName.php
// \Acme\Package\ClassNameTest → vendor/acme/package/tests/ClassNameTest.php

// ============================================================================
// Example 3: PSR-4 with PSR-0 Fallback
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();

// Enable PSR-0 fallback for top-level classes (no namespace)
$psr4 = new Psr4(enablePsr0Fallback: true, psr0BasePath: __DIR__ . '/lib');

// Register namespaced classes
$psr4->addNamespace('Vendor\Package', __DIR__ . '/vendor/package/src');

$autoloader->addClassPathMapper($psr4);
$autoloader->registerAutoloader();

// Namespaced class uses PSR-4:
// \Vendor\Package\Class_Name → vendor/package/src/Class_Name.php (underscore preserved)

// Top-level class falls back to PSR-0:
// Old_Style_Class → lib/Old/Style/Class.php (underscore converted)

// ============================================================================
// Example 4: PSR-0 DefaultMapper (Legacy Support)
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/DefaultMapper.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\DefaultMapper;

$autoloader = new Autoloader();

// PSR-0 mapper converts underscores to directory separators
$psr0 = new DefaultMapper(__DIR__ . '/lib');

$autoloader->addClassPathMapper($psr0);
$autoloader->registerAutoloader();

// PSR-0 mapping (underscores converted):
// My_Class_Name → lib/My/Class/Name.php
// Vendor_Package_Class → lib/Vendor/Package/Class.php

// ============================================================================
// Example 5: Application-Specific Mapper
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Application.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Application;

$autoloader = new Autoloader();

// Application mapper for MVC pattern
$appMapper = new Application(__DIR__ . '/app');
$appMapper->addMapping('Controller', 'controllers');
$appMapper->addMapping('Model', 'models');
$appMapper->addMapping('View', 'views');

$autoloader->addClassPathMapper($appMapper);
$autoloader->registerAutoloader();

// Pattern: AppName_ActionName_Suffix
// MyApp_UserList_Controller → app/controllers/UserList.php
// MyApp_User_Model → app/models/User.php

// ============================================================================
// Example 6: Prefix-Based Mapper
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Prefix.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Prefix;

$autoloader = new Autoloader();

// Regex prefix matching
$prefixMapper = new Prefix(
    pattern: '/^App(?:$|_)/i',
    includePath: __DIR__ . '/lib/app'
);

$autoloader->addClassPathMapper($prefixMapper);
$autoloader->registerAutoloader();

// App → lib/app/App.php
// App_Foo_Bar → lib/app/Foo/Bar.php

// ============================================================================
// Example 7: String Prefix Mapper (Case-Insensitive, Fast)
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/PrefixString.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\PrefixString;

$autoloader = new Autoloader();

// Simple string matching (faster than regex)
$stringMapper = new PrefixString(
    prefix: 'Vendor_Package',
    includePath: __DIR__ . '/vendor/package/lib'
);

$autoloader->addClassPathMapper($stringMapper);
$autoloader->registerAutoloader();

// Vendor_Package_Class → vendor/package/lib/Class.php
// vendor_package_class → vendor/package/lib/class.php (case-insensitive)

// ============================================================================
// Example 8: Combined PSR-4 and PSR-0 (Best Practice)
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';
require_once __DIR__ . '/../src/ClassPathMapper/DefaultMapper.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\DefaultMapper;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();

// PSR-4 for modern namespaced code
$psr4 = new Psr4(enablePsr0Fallback: false);
$psr4->addNamespace('Acme\Log', __DIR__ . '/vendor/acme/log/src');
$psr4->addNamespace('Vendor\Package', __DIR__ . '/vendor/package/src');

// PSR-0 for legacy underscore-based code
$psr0 = new DefaultMapper(__DIR__ . '/lib');

// Add both mappers (LIFO order - last added searched first)
$autoloader->addClassPathMapper($psr0);  // Added first, searched last
$autoloader->addClassPathMapper($psr4);  // Added last, searched first

$autoloader->registerAutoloader();

// Modern classes use PSR-4
// Legacy classes fall through to PSR-0

// ============================================================================
// Example 9: Callbacks After Class Load
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;

$autoloader = new Autoloader();

$psr4 = new Psr4(enablePsr0Fallback: false);
$psr4->addNamespace('App\Logger', __DIR__ . '/src/Logger');

$autoloader->addClassPathMapper($psr4);

// Register callback to run after specific class loads
$autoloader->addCallback('App\Logger\FileLogger', function(): void {
    echo "FileLogger was loaded!\n";
});

$autoloader->registerAutoloader();

// ============================================================================
// Example 10: Fluent Interface with Named Parameters
// ============================================================================

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../src/ClassPathMapper/Psr4.php';
require_once __DIR__ . '/../src/ClassPathMapper/PrefixString.php';

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;
use Horde\Autoloader\ClassPathMapper\PrefixString;

$autoloader = (new Autoloader())
    ->addClassPathMapper(
        (new Psr4(enablePsr0Fallback: false))
            ->addNamespace('Acme\Log', __DIR__ . '/vendor/acme/log/src')
            ->addNamespace('Vendor\Package', __DIR__ . '/vendor/package/src')
    )
    ->addClassPathMapper(
        new PrefixString(
            prefix: 'Legacy_',
            includePath: __DIR__ . '/lib'
        )
    );

$autoloader->registerAutoloader();
