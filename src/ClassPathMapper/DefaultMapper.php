<?php

declare(strict_types=1);

/**
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Autoloader
 */

namespace Horde\Autoloader\ClassPathMapper;

use Horde\Autoloader\ClassPathMapper;

// Bootstrap: require interface
require_once __DIR__ . '/../ClassPathMapper.php';

/**
 * Provides classmapper that maps classes to paths following the PHP Framework
 * Interop Group PSR-0 reference implementation.
 *
 * Under this guideline, the following rules apply:
 *
 * - Each namespace separator is converted to a DIRECTORY_SEPARATOR when
 *   loading from the file system.
 * - Each "_" character in the CLASS NAME is converted to a
 *   DIRECTORY_SEPARATOR. The "_" character has no special meaning in the
 *   namespace.
 * - The fully-qualified namespace and class is suffixed with ".php" when
 *   loading from the file system.
 *
 * Examples:
 *
 * - \Doctrine\Common\IsolatedClassLoader =>
 *   /path/to/project/lib/vendor/Doctrine/Common/IsolatedClassLoader.php
 * - \namespace\package\Class_Name =>
 *   /path/to/project/lib/vendor/namespace/package/Class/Name.php
 * - \namespace\package_name\Class_Name =>
 *   /path/to/project/lib/vendor/namespace/package_name/Class/Name.php
 *
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2008-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class DefaultMapper implements ClassPathMapper
{
    /**
     * Constructor.
     */
    public function __construct(
        private readonly string $includePath
    ) {}

    /**
     * Maps class name to file path.
     *
     * @param string $className  Classname to map.
     *
     * @return string|false  Pathname to class file.
     */
    public function mapToPath(string $className): string|false
    {
        // @FIXME: Follow reference implementation
        return $this->includePath . DIRECTORY_SEPARATOR
            . str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className)
            . '.php';
    }
}
