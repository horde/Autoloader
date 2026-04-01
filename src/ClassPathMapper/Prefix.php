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
 * Provides a classmapper that implements generic pattern for different
 * mapping types within the application directory.
 *
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2008-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class Prefix implements ClassPathMapper
{
    /**
     * Constructor
     *
     * @param string $pattern      PCRE pattern.
     * @param string $includePath  Include path.
     */
    public function __construct(
        private readonly string $pattern,
        private readonly string $includePath
    ) {}

    /**
     * Maps class name to file path.
     *
     * @param string $className  Classname to map.
     *
     * @return string|false  Pathname to class file, or false if not found.
     */
    public function mapToPath(string $className): string|false
    {
        if (!preg_match($this->pattern, $className, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        return (strcasecmp($matches[0][0], $className) === 0)
            ? $this->includePath . '/' . $className . '.php'
            : str_replace(['\\', '_'], '/', substr($className, 0, $matches[0][1]))
                  . $this->includePath . '/'
                  . str_replace(['\\', '_'], '/', substr($className, $matches[0][1] + strlen($matches[0][0])))
                  . '.php';
    }
}
