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

namespace Horde\Autoloader\Test\Modern\ClassPathMapper;

use Horde\Autoloader\ClassPathMapper\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    private Application $mapper;

    public function setUp(): void
    {
        $this->mapper = new Application(
            'app' // directory to app dir
        );
        $this->mapper->addMapping('Suffix', 'subdir');
    }

    public static function providerValidClassNames(): array
    {
        return [
            ['Module_Action_Suffix', 'app/subdir/Action.php'],
            ['MyModule_Action_Suffix', 'app/subdir/Action.php'],
            ['Module_MyAction_Suffix', 'app/subdir/MyAction.php'],
            ['MyModule_MyAction_Suffix', 'app/subdir/MyAction.php'],
        ];
    }

    #[DataProvider('providerValidClassNames')]
    public function testShouldMapValidAppClassToAppPath(string $validClassName, string $classPath): void
    {
        $this->assertEquals(
            $classPath,
            $this->mapper->mapToPath($validClassName)
        );
    }

    public static function providerInvalidClassNames(): array
    {
        return [
            ['Module_Action_BadSuffix'],
            ['module_Action_Suffix'],
            ['Module_action_Suffix'],
            ['Module-Action-Suffix'],
            [''],
        ];
    }

    #[DataProvider('providerInvalidClassNames')]
    public function testShouldIgnoreInvalidAppClassNames(string $invalidClassName): void
    {
        $this->assertFalse($this->mapper->mapToPath($invalidClassName));
    }

    public function testToString(): void
    {
        $string = (string) $this->mapper;

        $this->assertStringContainsString('Horde\Autoloader\ClassPathMapper\Application', $string);
        $this->assertStringContainsString('app', $string);
    }
}
