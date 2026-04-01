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

use Horde_Autoloader_ClassPathMapper_PrefixString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Horde_Autoloader_ClassPathMapper_PrefixString::class)]
class PrefixStringTest extends TestCase
{
    private Horde_Autoloader_ClassPathMapper_PrefixString $mapper;

    public function setUp(): void
    {
        $this->mapper = new Horde_Autoloader_ClassPathMapper_PrefixString(
            'App',
            'dir'
        );
    }

    public static function providerClassNames(): array
    {
        return [
            ['App',         'dir/App.php'],
            ['App_Foo',     'dir/Foo.php'],
            ['App_Foo_Bar', 'dir/Foo/Bar.php'],
            ['App\Foo\Bar', 'dir/Foo/Bar.php'],
            ['app_foo',     'dir/foo.php']
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

    public function testCaseInsensitiveMatching(): void
    {
        $this->assertEquals('dir/Foo.php', $this->mapper->mapToPath('APP_Foo'));
        $this->assertEquals('dir/Bar.php', $this->mapper->mapToPath('aPp_Bar'));
        $this->assertEquals('dir/Baz.php', $this->mapper->mapToPath('aPp_Baz'));
    }
}
