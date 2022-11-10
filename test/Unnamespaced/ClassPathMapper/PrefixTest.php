<?php
/**
 * @category Horde
 * @package  Autoloader
 */

namespace Horde\Autoloader\Test\Unnamespaced\ClassPathMapper;
use PHPUnit\Framework\TestCase;
use \Horde_Autoloader_ClassPathMapper_Prefix;

class PrefixTest extends TestCase
{
    private $_mapper;

    public function setUp(): void
    {
        $this->_mapper = new Horde_Autoloader_ClassPathMapper_Prefix('/^App(?:$|_)/i', 'dir');
    }

    public function providerClassNames()
    {
        return array(
            array('App',         'dir/App.php'),
            array('App_Foo',     'dir/Foo.php'),
            array('App_Foo_Bar', 'dir/Foo/Bar.php'),
        );
    }

    /**
     * @dataProvider providerClassNames
     */
    public function testShouldMapClassToPath($className, $classPath)
    {
        $this->assertEquals(
            $classPath,
            $this->_mapper->mapToPath($className)
        );
    }
}
