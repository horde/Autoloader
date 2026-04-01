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

namespace Horde\Autoloader\Test\Integration\Modern;

use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\Psr4;
use Horde\AutoloadTestFixture\Cache\FileCache;
use Horde\AutoloadTestFixture\Database\Connection;
use Horde\AutoloadTestFixture\Log\Writer\FileWriter;
use Horde\AutoloadTestFixture\Log\Writer\File_Writer;
use Horde\AutoloadTestFixture\Log\Writer\MemoryWriter;
use Horde\AutoloadTestFixture\Package\TestClass;
use Horde\AutoloadTestFixture\Session\Handler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Autoloader::class)]
#[CoversClass(Psr4::class)]
class Psr4RealFileLoadingTest extends TestCase
{
    private string $fixturesDir;

    public function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/Fixtures/Psr4/Horde/AutoloadTestFixture';
    }

    public function testLoadPsr4ClassFile(): void
    {
        $this->assertFalse(
            class_exists('Horde\AutoloadTestFixture\Log\Writer\FileWriter', false),
            'FileWriter must not be pre-loaded by composer'
        );

        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Log', $this->fixturesDir . '/Log');

        $autoloader->addClassPathMapper($psr4Mapper);

        $result = $autoloader->loadClass('Horde\AutoloadTestFixture\Log\Writer\FileWriter');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Horde\AutoloadTestFixture\Log\Writer\FileWriter', false));

        $instance = new FileWriter();
        $this->assertEquals('psr4-file-writer', $instance->getValue());
    }

    public function testLoadPsr4ClassWithUnderscoreInName(): void
    {
        $this->assertFalse(class_exists('Horde\AutoloadTestFixture\Log\Writer\File_Writer', false));

        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Log', $this->fixturesDir . '/Log');

        $autoloader->addClassPathMapper($psr4Mapper);

        $result = $autoloader->loadClass('Horde\AutoloadTestFixture\Log\Writer\File_Writer');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Horde\AutoloadTestFixture\Log\Writer\File_Writer', false));

        $instance = new File_Writer();
        $this->assertEquals('psr4-underscore', $instance->getValue());
    }

    public function testMultipleNamespacePrefixes(): void
    {
        $this->assertFalse(class_exists('Horde\AutoloadTestFixture\Package\TestClass', false));
        $this->assertFalse(class_exists('Horde\AutoloadTestFixture\Cache\FileCache', false));

        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);

        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Package', $this->fixturesDir . '/Package');
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Cache', $this->fixturesDir . '/Cache');

        $autoloader->addClassPathMapper($psr4Mapper);

        $result1 = $autoloader->loadClass('Horde\AutoloadTestFixture\Package\TestClass');
        $this->assertTrue($result1);

        $result2 = $autoloader->loadClass('Horde\AutoloadTestFixture\Cache\FileCache');
        $this->assertTrue($result2);

        $instance1 = new TestClass();
        $this->assertEquals('psr4-package', $instance1->getValue());

        $instance2 = new FileCache();
        $this->assertEquals('psr4-cache', $instance2->getValue());
    }

    public function testLongestPrefixMatchingWithRealFiles(): void
    {
        $this->assertFalse(class_exists('Horde\AutoloadTestFixture\Log\Writer\MemoryWriter', false));

        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);

        // Register both shorter and longer prefixes
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture', $this->fixturesDir);
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Log', $this->fixturesDir . '/Log');

        $autoloader->addClassPathMapper($psr4Mapper);

        // Should use the longer, more specific prefix
        $result = $autoloader->loadClass('Horde\AutoloadTestFixture\Log\Writer\MemoryWriter');

        $this->assertTrue($result);

        $instance = new MemoryWriter();
        $this->assertEquals('psr4-memory', $instance->getValue());
    }

    public function testShorterPrefixUsedWhenLongerDoesNotMatch(): void
    {
        $this->assertFalse(class_exists('Horde\AutoloadTestFixture\Database\Connection', false));

        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);

        // Register both shorter and longer prefixes
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture', $this->fixturesDir);
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Log', $this->fixturesDir . '/Log');

        $autoloader->addClassPathMapper($psr4Mapper);

        // Should use the shorter prefix since class is in Database namespace
        $result = $autoloader->loadClass('Horde\AutoloadTestFixture\Database\Connection');

        $this->assertTrue($result);

        $instance = new Connection();
        $this->assertEquals('psr4-database', $instance->getValue());
    }

    public function testRegisterAutoloaderWithPsr4(): void
    {
        $this->assertFalse(class_exists('Horde\AutoloadTestFixture\Session\Handler', false));

        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture\Session', $this->fixturesDir . '/Session');

        $autoloader->addClassPathMapper($psr4Mapper);
        $autoloader->registerAutoloader();

        // Trigger autoloading by referencing the class
        $this->assertTrue(class_exists('Horde\AutoloadTestFixture\Session\Handler'));

        $instance = new Handler();
        $this->assertEquals('psr4-session', $instance->getValue());

        // Cleanup
        spl_autoload_unregister([$autoloader, 'loadClass']);
    }

    public function testPsr4RejectsTopLevelClass(): void
    {
        $autoloader = new Autoloader();
        $psr4Mapper = new Psr4(enablePsr0Fallback: false);
        $psr4Mapper->addNamespace('Horde\AutoloadTestFixture', $this->fixturesDir);

        $autoloader->addClassPathMapper($psr4Mapper);

        // Top-level class without namespace should fail
        $result = $autoloader->loadClass('TopLevelClass');

        $this->assertFalse($result);
    }
}
