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

namespace Horde\Autoloader\Test\Integration\Modern;

use Fixture_Modern_Nested_Class;
use Fixture_Modern_Prefixed_Module_Action;
use Fixture_Modern_Second;
use Fixture_Modern_Simple;
use Fixture_Modern_Third;
use Horde\Autoloader\Autoloader;
use Horde\Autoloader\ClassPathMapper\DefaultMapper;
use Horde\Autoloader\ClassPathMapper\Prefix;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Autoloader::class)]
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
            class_exists('Fixture_Modern_Simple', false),
            'Fixture_Modern_Simple must not be pre-loaded by composer'
        );

        $autoloader = new Autoloader();
        $autoloader->addClassPathMapper(
            new DefaultMapper($this->fixturesDir)
        );

        $result = $autoloader->loadClass('Fixture_Modern_Simple');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Fixture_Modern_Simple', false));

        // Verify we can actually use the class
        $instance = new Fixture_Modern_Simple();
        $this->assertEquals('simple', $instance->getValue());
    }

    public function testLoadNestedClassFile(): void
    {
        $this->assertFalse(class_exists('Fixture_Modern_Nested_Class', false));

        $autoloader = new Autoloader();
        $autoloader->addClassPathMapper(
            new DefaultMapper($this->fixturesDir)
        );

        $result = $autoloader->loadClass('Fixture_Modern_Nested_Class');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Fixture_Modern_Nested_Class', false));

        // Verify we can actually use the class
        $instance = new Fixture_Modern_Nested_Class();
        $this->assertEquals('nested', $instance->getValue());
    }

    public function testLoadWithCallbackExecutes(): void
    {
        $this->assertFalse(class_exists('Fixture_Modern_Second', false));

        $callbackData = null;

        $autoloader = new Autoloader();
        $autoloader->addClassPathMapper(
            new DefaultMapper($this->fixturesDir)
        );
        $autoloader->addCallback('Fixture_Modern_Second', function () use (&$callbackData) {
            $callbackData = 'callback executed';
        });

        $autoloader->loadClass('Fixture_Modern_Second');

        $this->assertEquals('callback executed', $callbackData);
    }

    public function testLoadNonexistentFileReturnsFalse(): void
    {
        $this->assertFalse(class_exists('Nonexistent_Modern_Class', false));

        $autoloader = new Autoloader();
        $autoloader->addClassPathMapper(
            new DefaultMapper($this->fixturesDir)
        );

        $result = $autoloader->loadClass('Nonexistent_Modern_Class');

        $this->assertFalse($result);
        $this->assertFalse(class_exists('Nonexistent_Modern_Class', false));
    }

    public function testMultipleMappersRealFiles(): void
    {
        $this->assertFalse(class_exists('Fixture_Modern_Prefixed_Module_Action', false));

        $autoloader = new Autoloader();

        // Add default mapper
        $autoloader->addClassPathMapper(
            new DefaultMapper($this->fixturesDir)
        );

        // Add prefix mapper (should be searched first - LIFO)
        $autoloader->addClassPathMapper(
            new Prefix(
                '/^Fixture_Modern_Prefixed/',
                $this->fixturesDir . '/Fixture/Modern/Prefixed'
            )
        );

        $result = $autoloader->loadClass('Fixture_Modern_Prefixed_Module_Action');

        $this->assertTrue($result);
        $this->assertTrue(class_exists('Fixture_Modern_Prefixed_Module_Action', false));

        // Verify we can actually use the class
        $instance = new Fixture_Modern_Prefixed_Module_Action();
        $this->assertEquals('prefixed', $instance->getValue());
    }

    public function testRegisterAutoloaderWithRealFiles(): void
    {
        $this->assertFalse(class_exists('Fixture_Modern_Third', false));

        $autoloader = new Autoloader();
        $autoloader->addClassPathMapper(
            new DefaultMapper($this->fixturesDir)
        );

        $autoloader->registerAutoloader();

        // Trigger autoloading by referencing the class
        $this->assertTrue(class_exists('Fixture_Modern_Third'));

        // Verify we can instantiate it
        $instance = new Fixture_Modern_Third();
        $this->assertEquals('third', $instance->getValue());

        // Cleanup
        spl_autoload_unregister([$autoloader, 'loadClass']);
    }
}
