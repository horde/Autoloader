<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Psr4::class)]
class Psr4Test extends TestCase
{
    private Psr4 $mapper;

    public function setUp(): void
    {
        $this->mapper = new Psr4(enablePsr0Fallback: false);
    }

    public function testAddNamespaceReturnsThis(): void
    {
        $result = $this->mapper->addNamespace('Acme\Log', '/path/to/acme-log/src');

        $this->assertSame($this->mapper, $result, 'Should return this for fluent interface');
    }

    public function testGetPrefixesReturnsRegisteredNamespaces(): void
    {
        $this->mapper->addNamespace('Acme\Log', '/path/to/acme-log/src');
        $this->mapper->addNamespace('Vendor\Package', '/path/to/vendor-package/lib');

        $prefixes = $this->mapper->getPrefixes();

        $this->assertArrayHasKey('Acme\Log\\', $prefixes);
        $this->assertArrayHasKey('Vendor\Package\\', $prefixes);
    }

    public static function providerPsr4Mappings(): array
    {
        $ds = DIRECTORY_SEPARATOR;
        return [
            // Basic namespace mapping
            [
                'Acme\Log',
                '/vendor/acme-log/src',
                'Acme\Log\Writer\FileWriter',
                "/vendor/acme-log/src{$ds}Writer{$ds}FileWriter.php",
            ],
            // Underscores preserved in PSR-4 (not converted to directory separators)
            [
                'Acme\Log',
                '/vendor/acme-log/src',
                'Acme\Log\Writer\File_Writer',
                "/vendor/acme-log/src{$ds}Writer{$ds}File_Writer.php",
            ],
            // Deep namespace hierarchy
            [
                'Symfony\Component\HttpFoundation',
                '/vendor/symfony/http-foundation',
                'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler',
                "/vendor/symfony/http-foundation{$ds}Session{$ds}Storage{$ds}Handler{$ds}NativeFileSessionHandler.php",
            ],
            // Single level after namespace prefix
            [
                'Vendor\Package',
                '/vendor/package/lib',
                'Vendor\Package\ClassName',
                "/vendor/package/lib{$ds}ClassName.php",
            ],
        ];
    }

    #[DataProvider('providerPsr4Mappings')]
    public function testShouldMapPsr4ClassToPath(
        string $namespacePrefix,
        string $baseDir,
        string $className,
        string $expectedPath
    ): void {
        $this->mapper->addNamespace($namespacePrefix, $baseDir);

        $result = $this->mapper->mapToPath($className);

        $this->assertEquals($expectedPath, $result);
    }

    public function testShouldRejectTopLevelClassWithoutNamespace(): void
    {
        $this->mapper->addNamespace('Acme', '/vendor/acme/src');

        // PSR-4 requires at least vendor\namespace
        $result = $this->mapper->mapToPath('TopLevelClass');

        $this->assertFalse($result);
    }

    public function testShouldRejectClassWithUnregisteredNamespace(): void
    {
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');

        // Different namespace not registered
        $result = $this->mapper->mapToPath('Vendor\Package\ClassName');

        $this->assertFalse($result);
    }

    public function testShouldRejectClassNameExactlyMatchingPrefix(): void
    {
        $this->mapper->addNamespace('Vendor\Package', '/vendor/package/src');

        // Class name exactly matches namespace prefix (no relative class part)
        // PSR-4 requires vendor\namespace\class structure
        $result = $this->mapper->mapToPath('Vendor\Package');

        $this->assertFalse($result);
    }

    public function testShouldHandleLeadingBackslash(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');

        // Leading backslash should be stripped
        $result = $this->mapper->mapToPath('\Acme\Log\Writer\FileWriter');

        $this->assertEquals("/vendor/acme-log/src{$ds}Writer{$ds}FileWriter.php", $result);
    }

    public function testShouldNormalizeNamespacePrefix(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        // Add without trailing backslash
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');

        $prefixes = $this->mapper->getPrefixes();

        // Should be normalized with trailing backslash
        $this->assertArrayHasKey('Acme\Log\\', $prefixes);

        // Should still work for mapping
        $result = $this->mapper->mapToPath('Acme\Log\Writer\FileWriter');
        $this->assertEquals("/vendor/acme-log/src{$ds}Writer{$ds}FileWriter.php", $result);
    }

    public function testShouldNormalizeBaseDirectory(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        // Add with trailing slash
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src/');

        $result = $this->mapper->mapToPath('Acme\Log\Writer\FileWriter');

        // Should not have double directory separator
        $this->assertEquals("/vendor/acme-log/src{$ds}Writer{$ds}FileWriter.php", $result);
        $this->assertStringNotContainsString($ds . $ds, $result);
    }

    public function testMultipleBaseDirectoriesForSameNamespace(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        // Same namespace can map to multiple base directories
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/tests');

        $prefixes = $this->mapper->getPrefixes();

        $this->assertCount(2, $prefixes['Acme\Log\\']);
        $this->assertContains('/vendor/acme-log/src' . $ds, $prefixes['Acme\Log\\']);
        $this->assertContains('/vendor/acme-log/tests' . $ds, $prefixes['Acme\Log\\']);
    }

    public function testPrependBaseDirectory(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/override', prepend: true);

        $prefixes = $this->mapper->getPrefixes();

        // Prepended directory should be first
        $this->assertEquals('/vendor/acme-log/override' . $ds, $prefixes['Acme\Log\\'][0]);
        $this->assertEquals('/vendor/acme-log/src' . $ds, $prefixes['Acme\Log\\'][1]);
    }

    public function testLongestMatchingPrefixWins(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        // Register both a shorter and longer prefix
        $this->mapper->addNamespace('Acme', '/vendor/acme/lib');
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');

        // Should match the longer, more specific prefix
        $result = $this->mapper->mapToPath('Acme\Log\Writer\FileWriter');

        $this->assertEquals("/vendor/acme-log/src{$ds}Writer{$ds}FileWriter.php", $result);
    }

    public function testShorterPrefixUsedWhenLongerDoesNotMatch(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->mapper->addNamespace('Acme', '/vendor/acme/lib');
        $this->mapper->addNamespace('Acme\Log', '/vendor/acme-log/src');

        // Should match the shorter prefix since class is in Acme\Database
        $result = $this->mapper->mapToPath('Acme\Database\Connection');

        $this->assertEquals("/vendor/acme/lib{$ds}Database{$ds}Connection.php", $result);
    }

    public function testPsr0FallbackForTopLevelClasses(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        // Enable PSR-0 fallback
        $mapper = new Psr4(enablePsr0Fallback: true, psr0BasePath: '/vendor/lib');

        // Top-level class should use PSR-0 fallback
        $result = $mapper->mapToPath('TopLevel_Class_Name');

        // PSR-0 converts underscores to directory separators
        $this->assertEquals("/vendor/lib{$ds}TopLevel{$ds}Class{$ds}Name.php", $result);
    }

    public function testPsr0FallbackDisabled(): void
    {
        // Explicitly disable PSR-0 fallback
        $mapper = new Psr4(enablePsr0Fallback: false);

        // Top-level class should be rejected
        $result = $mapper->mapToPath('TopLevel_Class_Name');

        $this->assertFalse($result);
    }

    public function testPsr0FallbackNotUsedForNamespacedClasses(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $mapper = new Psr4(enablePsr0Fallback: true, psr0BasePath: '/vendor/lib');
        $mapper->addNamespace('Vendor\Package', '/vendor/package/src');

        // Namespaced class should use PSR-4, not PSR-0
        $result = $mapper->mapToPath('Vendor\Package\Class_Name');

        // Underscore should be preserved (PSR-4 behavior)
        $this->assertEquals("/vendor/package/src{$ds}Class_Name.php", $result);
        // Should NOT be converted to directory separator (PSR-0 behavior)
        $this->assertStringNotContainsString("{$ds}Class{$ds}Name.php", $result);
    }

    public function testUnderscorePreservedInPsr4(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->mapper->addNamespace('Vendor\Package', '/vendor/package/src');

        // Multiple underscores in different positions
        $result = $this->mapper->mapToPath('Vendor\Package\My_Class_Name_Here');

        // Underscores should be preserved as-is
        $this->assertEquals("/vendor/package/src{$ds}My_Class_Name_Here.php", $result);
    }
}
