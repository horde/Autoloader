<?php
/**
 * @category Horde
 * @package  Autoloader
 */
class Horde_Autoloader_ClassPathMapper_PrefixTest extends Horde_Test_Case
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
