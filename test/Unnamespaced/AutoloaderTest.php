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
