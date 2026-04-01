<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Autoloader
 */

namespace Horde\Autoloader\ClassPathMapper;

use Horde\Autoloader\ClassPathMapper;

// Bootstrap: require interface
require_once __DIR__ . '/../ClassPathMapper.php';

/**
 * Provides classmapper that maps classes to paths following PSR-4.
 *
 * PSR-4 autoloader implementation with the following characteristics:
 *
 * - Maps namespace prefixes to base directories
 * - Underscores have no special meaning (treated as regular characters)
 * - Only backslash (\) is the namespace separator
 * - Requires at least vendor\namespace (no top-level classes by default)
 * - Can optionally fall back to PSR-0 for top-level classes
 *
 * PSR-4 rules:
 *
 * - The fully-qualified class name must have a top-level namespace (vendor)
 * - The namespace prefix may consist of one or more namespaces
 * - The namespace prefix must match a registered base directory
 * - After the namespace prefix, the remaining class name is appended to the base directory
 * - Namespace separators are converted to DIRECTORY_SEPARATOR
 * - Underscores in the class name have NO special meaning
 * - The resulting path is suffixed with .php
 *
 * Examples:
 *
 * Given namespace prefix "Acme\Log\" maps to "/path/to/packages/acme-log/src/":
 *
 * - \Acme\Log\Writer\FileWriter =>
 *   /path/to/packages/acme-log/src/Writer/FileWriter.php
 * - \Acme\Log\Writer\File_Writer =>
 *   /path/to/packages/acme-log/src/Writer/File_Writer.php (underscore preserved)
 *
 * @category  Horde
 * @copyright 2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class Psr4 implements ClassPathMapper
{
    /**
     * Namespace prefix to base directory mappings.
     *
     * @var array<string, string[]> Array of namespace prefix => array of base directories
     */
    private array $prefixes = [];

    /**
     * Optional PSR-0 fallback mapper for top-level classes.
     */
    private ?ClassPathMapper $psr0Fallback = null;

    /**
     * Constructor.
     *
     * @param bool $enablePsr0Fallback  Enable PSR-0 fallback for top-level classes
     * @param string|null $psr0BasePath Base path for PSR-0 fallback (required if fallback enabled)
     */
    public function __construct(
        private readonly bool $enablePsr0Fallback = true,
        ?string $psr0BasePath = null
    ) {
        if ($this->enablePsr0Fallback && $psr0BasePath !== null) {
            require_once __DIR__ . '/DefaultMapper.php';
            $this->psr0Fallback = new DefaultMapper($psr0BasePath);
        }
    }

    /**
     * Add a namespace prefix to base directory mapping.
     *
     * Allows multiple base directories for the same namespace prefix.
     *
     * @param string $prefix      The namespace prefix (e.g., "Acme\Log\")
     * @param string $baseDir     The base directory for the namespace prefix
     * @param bool $prepend       If true, prepend the base directory instead of append
     *
     * @return self  This instance for fluent interface
     */
    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): self
    {
        // Normalize the namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // Normalize the base directory with trailing separator
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Initialize the namespace prefix array if needed
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = [];
        }

        // Add the base directory
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } else {
            $this->prefixes[$prefix][] = $baseDir;
        }

        return $this;
    }

    /**
     * Maps class name to file path following PSR-4.
     *
     * @param string $className  Fully-qualified class name
     *
     * @return string|false  Pathname to class file, or false if not found
     */
    public function mapToPath(string $className): string|false
    {
        // Strip leading backslash if present
        $className = ltrim($className, '\\');

        // Check if this is a top-level class (no namespace)
        if (strpos($className, '\\') === false) {
            // Fall back to PSR-0 if enabled
            if ($this->psr0Fallback !== null) {
                return $this->psr0Fallback->mapToPath($className);
            }
            // PSR-4 requires at least vendor\namespace
            return false;
        }

        // Try each registered namespace prefix
        $prefixLength = 0;
        $matchedPrefix = '';

        // Find the longest matching namespace prefix
        foreach ($this->prefixes as $prefix => $baseDirs) {
            $len = strlen($prefix);
            if (strncmp($className, $prefix, $len) === 0 && $len > $prefixLength) {
                $matchedPrefix = $prefix;
                $prefixLength = $len;
            }
        }

        // No matching namespace prefix found
        if ($prefixLength === 0) {
            return false;
        }

        // Get the relative class name (part after the namespace prefix)
        $relativeClass = substr($className, $prefixLength);

        // If the class name exactly matches the prefix with no relative part, reject it
        // PSR-4 requires at least vendor\namespace\class
        if ($relativeClass === '') {
            return false;
        }

        // Try each base directory for this namespace prefix
        // Return the first path generated (the calling Autoloader checks file existence)
        foreach ($this->prefixes[$matchedPrefix] as $baseDir) {
            // Convert namespace separators to directory separators
            // Note: Underscores are NOT converted (PSR-4 difference from PSR-0)
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            // Return this candidate path
            // The Autoloader will check if the file exists via _fileExists()
            return $file;
        }

        return false;
    }

    /**
     * Get all registered namespace prefixes and their base directories.
     *
     * @return array<string, string[]>  Array of namespace prefix => array of base directories
     */
    public function getPrefixes(): array
    {
        return $this->prefixes;
    }
}
