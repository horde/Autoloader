<?php

declare(strict_types=1);

/**
 * Bootstrap file for Horde\Autoloader test suite.
 *
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @package  Autoloader
 */

// Load composer autoloader
$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',    // Standalone component
    __DIR__ . '/../../../autoload.php',     // Installed via composer
];

foreach ($autoloadCandidates as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        break;
    }
}

if (!class_exists('Horde_Autoloader')) {
    fwrite(STDERR, 'Unable to find composer autoloader. Run: composer install' . PHP_EOL);
    exit(1);
}
