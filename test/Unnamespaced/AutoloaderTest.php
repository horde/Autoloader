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

namespace Horde\Autoloader\Test\Unnamespaced;

use Horde_Autoloader;
use Horde_Autoloader_ClassPathMapper;
use Horde_Autoloader_ClassPathMapper_Default;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Horde_Autoloader::class)]
class AutoloaderTest extends TestCase
{
    private Horde_Autoloader_TestHarness $autoloader;

    public function setUp(): void
    {
        $this->autoloader = new Horde_Autoloader_TestHarness();
    }

    public function testInitialStateShouldYeildNoMatches(): void
    {
        $this->assertNull($this->autoloader->mapToPath('The_Class_Name'));
    }

    public function testInitialStateShouldNotLoadAnyFiles(): void
    {
        $this->assertFalse($this->autoloader->loadClass('The_Class_Name'));
    }

    public function testShouldNotMapClassIfMapperDoesNotReturnAPath(): void
    {
        $this->autoloader->addClassPathMapper($this->getUnsuccessfulMapperMock());
        $this->assertNull($this->autoloader->mapToPath('The_Class_Name'));
    }

    public function testShouldLoadPathMapperDoesNotReturnAPath(): void
    {
        $this->autoloader->addClassPathMapper($this->getUnsuccessfulMapperMock());
        $this->assertFalse($this->autoloader->loadClass('The_Class_Name'));
    }

    public function testShouldMapClassIfAMapperReturnsAPath(): void
    {
        // trick the autoloader into thinking the returned path exists
        $this->autoloader->setFileExistsResponse(true);

        $this->autoloader->addClassPathMapper($this->getSuccessfulMapperMock());

        $this->assertEquals('The/Class/Name.php', $this->autoloader->mapToPath('The_Class_Name'));
    }

    public function testShouldNotMapClassIfAMapperReturnsAPathThatDoesNotExist(): void
    {
        // trick the autoloader into thinking the returned path does not exist
        $this->autoloader->setFileExistsResponse(false);

        $this->autoloader->addClassPathMapper($this->getSuccessfulMapperMock());

        $this->assertNull($this->autoloader->mapToPath('The_Class_Name'));
    }

    public function testShouldLoadFileIfMapperReturnsAValidPath(): void
    {
        // trick the autoloader into thinking the returned path exists and was included
        $this->autoloader->setFileExistsResponse(true);
        $this->autoloader->setIncludeResponse(true);

        $this->autoloader->addClassPathMapper($this->getSuccessfulMapperMock());

        $this->assertTrue($this->autoloader->loadClass('The_Class_Name'));
    }

    public function testShouldLoadFileIfMapperReturnsAValidPathButIncludingItFails(): void
    {
        // trick the autoloader into thinking the returned path exists and was included
        $this->autoloader->setFileExistsResponse(true);
        $this->autoloader->setIncludeResponse(false);

        $this->autoloader->addClassPathMapper($this->getSuccessfulMapperMock());

        $this->assertFalse($this->autoloader->loadClass('The_Class_Name'));
    }

    public function testCallbackExecutedAfterSuccessfulLoad(): void
    {
        $callbackExecuted = false;
        $callback = function () use (&$callbackExecuted) {
            $callbackExecuted = true;
        };

        $this->autoloader->setFileExistsResponse(true);
        $this->autoloader->setIncludeResponse(true);
        $this->autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default('.')
        );

        $this->autoloader->addCallback('Test_Class', $callback);
        $this->autoloader->loadClass('Test_Class');

        $this->assertTrue($callbackExecuted, 'Callback was not executed after successful load');
    }

    public function testCallbackNotExecutedWhenLoadFails(): void
    {
        $callbackExecuted = false;

        $this->autoloader->setFileExistsResponse(false);

        $this->autoloader->addCallback('Test_Class', function () use (&$callbackExecuted) {
            $callbackExecuted = true;
        });

        $this->autoloader->loadClass('Test_Class');

        $this->assertFalse($callbackExecuted, 'Callback should not execute on load failure');
    }

    public function testCallbackIsCaseInsensitive(): void
    {
        $callbackExecuted = false;

        $this->autoloader->setFileExistsResponse(true);
        $this->autoloader->setIncludeResponse(true);
        $this->autoloader->addClassPathMapper(
            new Horde_Autoloader_ClassPathMapper_Default('.')
        );

        // Register with UPPERCASE
        $this->autoloader->addCallback('TEST_CLASS', function () use (&$callbackExecuted) {
            $callbackExecuted = true;
        });

        // Load with mixed case
        $this->autoloader->loadClass('Test_Class');

        $this->assertTrue($callbackExecuted, 'Callback should be case-insensitive');
    }

    public function testRegisterAutoloader(): void
    {
        $autoloader = new Horde_Autoloader();
        $autoloader->registerAutoloader();

        $registered = spl_autoload_functions();
        $found = false;
        foreach ($registered as $func) {
            if (is_array($func) && $func[0] === $autoloader && $func[1] === 'loadClass') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Autoloader not registered with SPL');

        // Cleanup
        spl_autoload_unregister([$autoloader, 'loadClass']);
    }

    public function testMappersSearchedInLIFOOrder(): void
    {
        $this->autoloader->setFileExistsResponse(true);

        // First mapper added
        $firstMapper = $this->getMockBuilder(Horde_Autoloader_ClassPathMapper::class)
            ->onlyMethods(['mapToPath'])
            ->getMock();
        $firstMapper->expects($this->never())
            ->method('mapToPath');

        // Second mapper added (should be searched first - LIFO)
        $secondMapper = $this->getMockBuilder(Horde_Autoloader_ClassPathMapper::class)
            ->onlyMethods(['mapToPath'])
            ->getMock();
        $secondMapper->expects($this->once())
            ->method('mapToPath')
            ->willReturn('second/Class.php');

        // Add first, then second
        $this->autoloader->addClassPathMapper($firstMapper);
        $this->autoloader->addClassPathMapper($secondMapper);

        // Second mapper should be consulted first (LIFO = Last In First Out)
        $path = $this->autoloader->mapToPath('Test_Class');

        $this->assertEquals('second/Class.php', $path);
    }

    public function testAddClassPathMapperReturnsThis(): void
    {
        $autoloader = new Horde_Autoloader();
        $mapper = new Horde_Autoloader_ClassPathMapper_Default('.');

        $result = $autoloader->addClassPathMapper($mapper);

        $this->assertSame($autoloader, $result, 'Should return this for fluent interface');
    }

    public function testLoadClassWithEmptyString(): void
    {
        $autoloader = new Horde_Autoloader();
        $this->assertFalse($autoloader->loadClass(''));
    }

    public function testMapToPathWithEmptyString(): void
    {
        $autoloader = new Horde_Autoloader();
        $this->assertNull($autoloader->mapToPath(''));
    }

    private function getSuccessfulMapperMock()
    {
        $mapper = $this->getMockBuilder('Horde_Autoloader_ClassPathMapper')
                        ->onlyMethods(['mapToPath'])
                        ->getMock();
        $mapper->expects($this->once())
            ->method('mapToPath')
            ->with($this->equalTo('The_Class_Name'))
            ->willReturn('The/Class/Name.php');

        return $mapper;
    }

    private function getUnsuccessfulMapperMock()
    {
        $mapper = $this->getMockBuilder('Horde_Autoloader_ClassPathMapper')
                        ->onlyMethods(['mapToPath'])
                        ->getMock();
        $mapper->expects($this->once())
            ->method('mapToPath')
            ->with($this->equalTo('The_Class_Name'))
            ->willReturn(null);

        return $mapper;
    }
}

class Horde_Autoloader_TestHarness extends Horde_Autoloader
{
    private bool|null $includeResponse = null;
    private bool|null $fileExistsResponse = null;

    public function setIncludeResponse(bool $value): void
    {
        $this->includeResponse = $value;
    }

    public function setFileExistsResponse(bool $value): void
    {
        $this->fileExistsResponse = $value;
    }

    protected function _include($path)
    {
        return $this->includeResponse;
    }

    protected function _fileExists($path)
    {
        return $this->fileExistsResponse;
    }
}
