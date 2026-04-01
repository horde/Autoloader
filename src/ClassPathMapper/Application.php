<?php

declare(strict_types=1);

/**
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Bob Mckee <bmckee@bywires.com>
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
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2008-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class Application implements ClassPathMapper
{
    /**
     * The following constants are for naming the positions in the regex for
     * easy readability later.
     */
    public const APPLICATION_POS = 1;
    public const ACTION_POS = 2;
    public const SUFFIX_POS = 3;

    public const NAME_SEGMENT = '([0-9A-Z][0-9A-Za-z]+)+';

    protected string $appDir;
    protected string $classMatchRegex = '';
    protected array $mappings = [];

    /**
     * Constructor.
     */
    public function __construct(string $appDir)
    {
        $this->appDir = rtrim($appDir, '/') . '/';
    }

    /**
     * Add a mapping from class suffix to subdirectory.
     */
    public function addMapping(string $classSuffix, string $subDir): void
    {
        $this->mappings[$classSuffix] = $subDir;
        $this->classMatchRegex = '/^' . self::NAME_SEGMENT . '_'
            . self::NAME_SEGMENT . '_'
            . '(' . implode('|', array_keys($this->mappings)) . ')$/';
    }

    /**
     * Maps class name to file path.
     *
     * @param string $className  Classname to map.
     *
     * @return string|false  Pathname to class file, or false if not found.
     */
    public function mapToPath(string $className): string|false
    {
        if (preg_match($this->classMatchRegex, $className, $matches)) {
            return $this->appDir . $this->mappings[$matches[self::SUFFIX_POS]] . '/' . $matches[self::ACTION_POS] . '.php';
        }
        return false;
    }

    /**
     * String representation of class.
     */
    public function __toString(): string
    {
        return self::class . ' ' . $this->classMatchRegex . ' [' . $this->appDir . ']';
    }
}
