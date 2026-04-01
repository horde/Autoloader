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

namespace Horde\Autoloader;

// Bootstrap: require interface before it's used
require_once __DIR__ . '/ClassPathMapper.php';

/**
 * Horde autoloader implementation.
 *
 * Manages an application's class name to file name mapping conventions. One or
 * more class-to-filename mappers are defined, and are searched in LIFO order.
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2008-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class Autoloader
{
    /**
     * List of callback methods.
     */
    private array $callbacks = [];

    /**
     * List of classpath mappers.
     */
    private array $mappers = [];

    /**
     * Register the autoloader with PHP (in a way to play well with as many
     * configurations as possible).
     */
    public function registerAutoloader(): void
    {
        spl_autoload_register([$this, 'loadClass']);
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }
    }

    /**
     * Loads a class into the current environment by classname.
     *
     * @param string $className  Classname to load.
     *
     * @return bool  True if the class was successfully loaded.
     */
    public function loadClass(string $className): bool
    {
        if (($path = $this->mapToPath($className))
            && $this->_include($path)) {
            $className = $this->_lower($className);
            if (isset($this->callbacks[$className])) {
                call_user_func($this->callbacks[$className]);
            }
            return true;
        }

        return false;
    }

    /**
     * Adds a class path mapper to the beginning of the queue.
     *
     * @param ClassPathMapper $mapper  A mapper object.
     *
     * @return self  This instance.
     */
    public function addClassPathMapper(ClassPathMapper $mapper): self
    {
        array_unshift($this->mappers, $mapper);
        return $this;
    }

    /**
     * Add a callback to run when a class is loaded through loadClass().
     *
     * @param string $class    The classname.
     * @param callable $callback  The callback to run when the class is loaded.
     */
    public function addCallback(string $class, callable $callback): void
    {
        $this->callbacks[$this->_lower($class)] = $callback;
    }

    /**
     * Search registered mappers in LIFO order.
     *
     * @param string $className  Classname to load.
     *
     * @return string|null  Pathname to class, or null if not found.
     */
    public function mapToPath(string $className): ?string
    {
        foreach ($this->mappers as $mapper) {
            if (($path = $mapper->mapToPath($className))
                && $this->_fileExists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Include a file.
     *
     * @param string $path  Pathname of file to include.
     *
     * @return bool  Success.
     */
    protected function _include(string $path): bool
    {
        return (bool) include $path;
    }

    /**
     * Does a file exist?
     *
     * @param string $path  Pathname of file to check.
     *
     * @return bool  Does file exist?
     */
    protected function _fileExists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Locale independant strtolower() implementation.
     *
     * @param string $string The string to convert to lowercase.
     *
     * @return string  The lowercased string, based on ASCII encoding.
     */
    protected function _lower(string $string): string
    {
        $language = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $string = strtolower($string);
        setlocale(LC_CTYPE, $language);
        return $string;
    }
}
