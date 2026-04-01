<?php

declare(strict_types=1);

/**
 * Copyright 2014-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @package  Autoloader
 */

namespace Horde\Autoloader\Test\Modern\ClassPathMapper;

use Horde\Autoloader\ClassPathMapper\PrefixString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrefixString::class)]
class PrefixStringTest extends TestCase
{
    private PrefixString $mapper;

    public function setUp(): void
    {
        $this->mapper = new PrefixString(
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
            ['app_foo',     'dir/foo.php'],
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
