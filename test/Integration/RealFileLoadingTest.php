<?php

declare(strict_types=1);

/**
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @package  Autoloader
 */

namespace Horde\Autoloader\Test\Integration;

use Horde_Autoloader;
use Horde_Autoloader_ClassPathMapper_Default;
use Horde_Autoloader_ClassPathMapper_Prefix;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Fixture_Nested_Class;
use Fixture_Prefixed_Module_Action;
use Fixture_Simple;
use Fixture_Third;

#[CoversClass(Horde_Autoloader::class)]
class RealFileLoadingTest extends TestCase
{
    private string $fixturesDir;

    public function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/Fixtures';
    }

    public function testLoadRealClassFile(): void
    {
        // Verify this specific fixture is NOT pre-loaded
        $this->assertFalse(
            class_exists('Fixture_Simple', false),
            'Fixture_Simple must not be pre-loaded by composer'
        );

        $autoloader = new Horde_Autoloader();
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default($this->fixturesDir)
        );

        $result = $autoloader->loadClass('Fixture_Simple');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Fixture_Simple', false));

        // Verify we can actually use the class
        $instance = new Fixture_Simple();
        $this->assertEquals('simple', $instance->getValue());
    }

    public function testLoadNestedClassFile(): void
    {
        $this->assertFalse(class_exists('Fixture_Nested_Class', false));

        $autoloader = new Horde_Autoloader();
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default($this->fixturesDir)
        );

        $result = $autoloader->loadClass('Fixture_Nested_Class');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Fixture_Nested_Class', false));

        // Verify we can actually use the class
        $instance = new Fixture_Nested_Class();
        $this->assertEquals('nested', $instance->getValue());
    }

    public function testLoadWithCallbackExecutes(): void
    {
        $this->assertFalse(class_exists('Fixture_Second', false));

        $callbackData = null;

        $autoloader = new Horde_Autoloader();
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default($this->fixturesDir)
        );
        $autoloader->addCallback('Fixture_Second', function () use (&$callbackData) {
            $callbackData = 'callback executed';
        });

        $autoloader->loadClass('Fixture_Second');

        $this->assertEquals('callback executed', $callbackData);
    }

    public function testLoadNonexistentFileReturnsFalse(): void
    {
        $this->assertFalse(class_exists('Nonexistent_Class', false));

        $autoloader = new Horde_Autoloader();
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default($this->fixturesDir)
        );

        $result = $autoloader->loadClass('Nonexistent_Class');

        $this->assertFalse($result);
        $this->assertFalse(class_exists('Nonexistent_Class', false));
    }

    public function testMultipleMappersRealFiles(): void
    {
        $this->assertFalse(class_exists('Fixture_Prefixed_Module_Action', false));

        $autoloader = new Horde_Autoloader();

        // Add default mapper
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default($this->fixturesDir)
        );

        // Add prefix mapper (should be searched first - LIFO)
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Prefix(
                '/^Fixture_Prefixed/',
                $this->fixturesDir . '/Fixture/Prefixed'
            )
        );

        $result = $autoloader->loadClass('Fixture_Prefixed_Module_Action');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Fixture_Prefixed_Module_Action', false));

        // Verify we can actually use the class
        $instance = new Fixture_Prefixed_Module_Action();
        $this->assertEquals('prefixed', $instance->getValue());
    }

    public function testRegisterAutoloaderWithRealFiles(): void
    {
        $this->assertFalse(class_exists('Fixture_Third', false));

        $autoloader = new Horde_Autoloader();
        $autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default($this->fixturesDir)
        );

        $autoloader->registerAutoloader();

        // Trigger autoloading by referencing the class
        $this->assertTrue(class_exists('Fixture_Third'));

        // Verify we can instantiate it
        $instance = new Fixture_Third();
        $this->assertEquals('third', $instance->getValue());

        // Cleanup
        spl_autoload_unregister([$autoloader, 'loadClass']);
    }
}
