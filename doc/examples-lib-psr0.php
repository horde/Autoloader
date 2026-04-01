<?php

/**
 * PSR-0 Autoloader Examples (lib/)
 *
 * These examples demonstrate usage of the legacy PSR-0 autoloader
 * from the lib/ directory using Horde_Autoloader classes.
 *
 * IMPORTANT: Autoloader bootstraps itself - don't use composer's autoloader!
 * Instead, require the autoloader files explicitly.
 */

// ============================================================================
// Example 1: Most Basic Usage
// ============================================================================

// Bootstrap: require autoloader files explicitly
require_once __DIR__ . '/../lib/Horde/Autoloader.php';

// Create autoloader instance
$autoloader = new Horde_Autoloader();

// Add default PSR-0 mapper (converts underscores to directory separators)
$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/classes')
);

// Register with SPL
$autoloader->registerAutoloader();

// Now classes autoload automatically
// My_Class_Name → classes/My/Class/Name.php
// $obj = new My_Class_Name();

// ============================================================================
// Example 2: Application-Specific Mapper
// ============================================================================

require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();

// Application mapper for controller/action pattern
$appMapper = new Horde_Autoloader_ClassPathMapper_Application(__DIR__ . '/app');
$appMapper->addMapping('Controller', 'controllers');
$appMapper->addMapping('Model', 'models');

$autoloader->addClassPathMapper($appMapper);
$autoloader->registerAutoloader();

// MyApp_UserList_Controller → app/controllers/UserList.php
// MyApp_User_Model → app/models/User.php

// ============================================================================
// Example 3: Prefix-Based Mapper
// ============================================================================

require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();

// Map classes with specific prefix to a directory
$prefixMapper = new Horde_Autoloader_ClassPathMapper_Prefix(
    '/^App(?:$|_)/i',  // Regex: starts with "App" followed by underscore or end
    __DIR__ . '/lib/app'
);

$autoloader->addClassPathMapper($prefixMapper);
$autoloader->registerAutoloader();

// App → lib/app/App.php
// App_Foo → lib/app/Foo.php
// App_Foo_Bar → lib/app/Foo/Bar.php

// ============================================================================
// Example 4: String Prefix Mapper (Case-Insensitive)
// ============================================================================

require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();

// Simple string prefix matching (faster than regex)
$stringMapper = new Horde_Autoloader_ClassPathMapper_PrefixString(
    'Vendor_Package',
    __DIR__ . '/vendor/package/lib'
);

$autoloader->addClassPathMapper($stringMapper);
$autoloader->registerAutoloader();

// Vendor_Package_Class → vendor/package/lib/Class.php
// vendor_package_class → vendor/package/lib/class.php (case-insensitive)

// ============================================================================
// Example 5: Multiple Mappers (LIFO Order)
// ============================================================================

require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();

// Add mappers in order (last added is searched first - LIFO)
$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/lib')
);

$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_PrefixString(
        'MyApp',
        __DIR__ . '/src'
    )
);

$autoloader->registerAutoloader();

// MyApp_Class searched in src/ first (added last)
// Other_Class searched in lib/

// ============================================================================
// Example 6: Callbacks After Class Load
// ============================================================================

require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = new Horde_Autoloader();

$autoloader->addClassPathMapper(
    new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/classes')
);

// Register callback to run after class is loaded
$autoloader->addCallback('Logger_Class', function() {
    echo "Logger_Class was just loaded!\n";
});

$autoloader->registerAutoloader();

// First use of Logger_Class will trigger the callback
// $logger = new Logger_Class();

// ============================================================================
// Example 7: Fluent Interface
// ============================================================================

require_once __DIR__ . '/../lib/Horde/Autoloader.php';

$autoloader = (new Horde_Autoloader())
    ->addClassPathMapper(
        new Horde_Autoloader_ClassPathMapper_Default(__DIR__ . '/lib')
    )
    ->addClassPathMapper(
        new Horde_Autoloader_ClassPathMapper_PrefixString('MyApp', __DIR__ . '/src')
    );

$autoloader->registerAutoloader();
