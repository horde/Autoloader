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

use Horde\Autoloader\ClassPathMapper\DefaultMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultMapper::class)]
class DefaultTest extends TestCase
{
    private DefaultMapper $mapper;

    public function setUp(): void
    {
        $this->mapper = new DefaultMapper('dir');
    }

    public static function providerClassNames(): array
    {
        return array_map(
            function ($a) {
                $a[1] = str_replace('/', DIRECTORY_SEPARATOR, $a[1]);
                return $a;
            },
            [
                ['Module_Action_Suffix', 'dir/Module/Action/Suffix.php'],
                ['MyModule_Action_Suffix', 'dir/MyModule/Action/Suffix.php'],
                ['Module_MyAction_Suffix', 'dir/Module/MyAction/Suffix.php'],
                ['MyModule_MyAction_Suffix', 'dir/MyModule/MyAction/Suffix.php'],
                ['Module\Action\Suffix', 'dir/Module/Action/Suffix.php'],
                ['MyModule\Action\Suffix', 'dir/MyModule/Action/Suffix.php'],
                ['Module\MyAction\Suffix', 'dir/Module/MyAction/Suffix.php'],
                ['MyModule\MyAction\Suffix', 'dir/MyModule/MyAction/Suffix.php'],
            ]
        );
    }

    #[DataProvider('providerClassNames')]
    public function testShouldMapClassToPath(string $className, string $classPath): void
    {
        $this->assertEquals(
            $classPath,
            $this->mapper->mapToPath($className)
        );
    }
}
