<?php

declare(strict_types=1);

/**
 * Copyright 2014-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Autoloader
 */

namespace Horde\Autoloader\ClassPathMapper;

use Horde\Autoloader\ClassPathMapper;

// Bootstrap: require interface
require_once __DIR__ . '/../ClassPathMapper.php';

/**
 * Provides a classmapper that implements prefix matching using a simple
 * string search within a base application directory.
 *
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2014-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class PrefixString implements ClassPathMapper
{
    /**
     * Constructor
     *
     * @param string $prefix       Prefix.
     * @param string $includePath  Include path.
     */
    public function __construct(
        private readonly string $prefix,
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
        if ($this->_ipos($className, $this->prefix) === 0) {
            $len = strlen($this->prefix);
            if ($len === strlen($className)) {
                return $this->includePath . '/' . $className . '.php';
            } elseif (($c = $className[$len])
                      && ($c == '_') || ($c == '\\')) {
                return $this->includePath . '/'
                    . str_replace($c, '/', substr($className, $len + 1)) . '.php';
            }
        }

        return false;
    }

    /**
     * Locale independant stripos() implementation.
     *
     * @param string $haystack  The string to search through.
     * @param string $needle    The string to search for.
     *
     * @return int|false  The position of first case-insensitive occurrence.
     */
    protected function _ipos(string $haystack, string $needle): int|false
    {
        $language = setlocale(LC_CTYPE, '0');
        setlocale(LC_CTYPE, 'C');
        $pos = stripos($haystack, $needle);
        setlocale(LC_CTYPE, $language);
        return $pos;
    }
}
