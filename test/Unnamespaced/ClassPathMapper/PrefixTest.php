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

namespace Horde\Autoloader\Test\Unnamespaced\ClassPathMapper;

use Horde_Autoloader_ClassPathMapper_Prefix;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Horde_Autoloader_ClassPathMapper_Prefix::class)]
class PrefixTest extends TestCase
{
    private Horde_Autoloader_ClassPathMapper_Prefix $mapper;

    public function setUp(): void
    {
        $this->mapper = new Horde_Autoloader_ClassPathMapper_Prefix('/^App(?:$|_)/i', 'dir');
    }

    public static function providerClassNames(): array
    {
        return [
            ['App',         'dir/App.php'],
            ['App_Foo',     'dir/Foo.php'],
            ['App_Foo_Bar', 'dir/Foo/Bar.php'],
        ];
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
