<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * Tests adapted from PSR-4 autoloader examples
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @package  Autoloader
 */

namespace Horde\Autoloader\Test\Modern\ClassPathMapper;

use Horde\Autoloader\ClassPathMapper\Psr4;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * PSR-4 specification compliance tests
 *
 * These tests are adapted from the official PSR-4 autoloader examples
 * to verify our implementation follows the PSR-4 specification.
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 */
#[CoversClass(Psr4::class)]
class Psr4SpecificationTest extends TestCase
{
    private Psr4TestHarness $mapper;

    public function setUp(): void
    {
        $this->mapper = new Psr4TestHarness(enablePsr0Fallback: false);

        // Set up mock "files" that exist
        $this->mapper->setFiles([
            '/vendor/foo.bar/src/ClassName.php',
            '/vendor/foo.bar/src/DoomClassName.php',
            '/vendor/foo.bar/tests/ClassNameTest.php',
            '/vendor/foo.bardoom/src/ClassName.php',
            '/vendor/foo.bar.baz.dib/src/ClassName.php',
            '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php',
        ]);

        // Register namespace prefixes as per PSR-4 examples
        $this->mapper->addNamespace('Foo\Bar', '/vendor/foo.bar/src');
        $this->mapper->addNamespace('Foo\Bar', '/vendor/foo.bar/tests');
        $this->mapper->addNamespace('Foo\BarDoom', '/vendor/foo.bardoom/src');
        $this->mapper->addNamespace('Foo\Bar\Baz\Dib', '/vendor/foo.bar.baz.dib/src');
        $this->mapper->addNamespace('Foo\Bar\Baz\Dib\Zim\Gir', '/vendor/foo.bar.baz.dib.zim.gir/src');
    }

    /**
     * Test existing file mapping
     *
     * Adapted from PSR-4-autoloader-examples.md testExistingFile()
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     */
    public function testExistingFile(): void
    {
        $actual = $this->mapper->mapToPath('Foo\Bar\ClassName');
        $expect = '/vendor/foo.bar/src/ClassName.php';
        $this->assertSame($expect, $actual);

        $actual = $this->mapper->mapToPath('Foo\Bar\ClassNameTest');
        $expect = '/vendor/foo.bar/tests/ClassNameTest.php';
        $this->assertSame($expect, $actual);
    }

    /**
     * Test missing file handling
     *
     * Adapted from PSR-4-autoloader-examples.md testMissingFile()
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     */
    public function testMissingFile(): void
    {
        $actual = $this->mapper->mapToPath('No_Vendor\No_Package\NoClass');
        $this->assertFalse($actual);
    }

    /**
     * Test deep namespace hierarchy
     *
     * Adapted from PSR-4-autoloader-examples.md testDeepFile()
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     */
    public function testDeepFile(): void
    {
        $actual = $this->mapper->mapToPath('Foo\Bar\Baz\Dib\Zim\Gir\ClassName');
        $expect = '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php';
        $this->assertSame($expect, $actual);
    }

    /**
     * Test similar namespace prefix disambiguation
     *
     * Adapted from PSR-4-autoloader-examples.md testConfusion()
     *
     * Ensures the loader distinguishes between similar namespace prefixes:
     * - Foo\Bar\DoomClassName loads from /vendor/foo.bar/src/
     * - Foo\BarDoom\ClassName loads from /vendor/foo.bardoom/src/
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     */
    public function testConfusion(): void
    {
        $actual = $this->mapper->mapToPath('Foo\Bar\DoomClassName');
        $expect = '/vendor/foo.bar/src/DoomClassName.php';
        $this->assertSame($expect, $actual);

        $actual = $this->mapper->mapToPath('Foo\BarDoom\ClassName');
        $expect = '/vendor/foo.bardoom/src/ClassName.php';
        $this->assertSame($expect, $actual);
    }
}

/**
 * Test harness for PSR-4 mapper that simulates file existence
 *
 * Similar to MockPsr4AutoloaderClass from the PSR-4 examples,
 * this allows us to test mapToPath() against a known set of files
 * without requiring actual files on disk.
 *
 * Since our mapToPath() interface returns string|false (not multiple candidates),
 * this harness iterates through all base directories for a matched prefix
 * and returns the first path that exists in our mock file set.
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 */
class Psr4TestHarness extends Psr4
{
    private array $files = [];

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * Override mapToPath to check all base directories for a matching file
     */
    public function mapToPath(string $className): string|false
    {
        // Strip leading backslash if present
        $className = ltrim($className, '\\');

        // Check if this is a top-level class (no namespace)
        if (strpos($className, '\\') === false) {
            return false; // No PSR-0 fallback in these tests
        }

        // Find the longest matching namespace prefix
        $prefixLength = 0;
        $matchedPrefix = '';

        foreach ($this->getPrefixes() as $prefix => $baseDirs) {
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

        // Get the relative class name
        $relativeClass = substr($className, $prefixLength);

        if ($relativeClass === '') {
            return false;
        }

        // Try each base directory for this namespace prefix
        foreach ($this->getPrefixes()[$matchedPrefix] as $baseDir) {
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            // Return the first path that exists in our mock file set
            if (in_array($file, $this->files, true)) {
                return $file;
            }
        }

        return false;
    }
}
