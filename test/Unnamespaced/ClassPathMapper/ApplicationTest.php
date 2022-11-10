<?php
/**
 * @category Horde
 * @package  Autoloader
 */

namespace Horde\Autoloader\Test\Unnamespaced\ClassPathMapper;
use PHPUnit\Framework\TestCase;
use \Horde_Autoloader_ClassPathMapper_Application;

class ApplicationTest extends TestCase
{
    private $_mapper;

    public function setUp(): void
    {
        $this->_mapper = new Horde_Autoloader_ClassPathMapper_Application(
            'app' // directory to app dir
        );
        $this->_mapper->addMapping('Suffix', 'subdir');
    }

    public function providerValidClassNames()
    {
        return array(
            array('Module_Action_Suffix', 'app/subdir/Action.php'),
            array('MyModule_Action_Suffix', 'app/subdir/Action.php'),
            array('Module_MyAction_Suffix', 'app/subdir/MyAction.php'),
            array('MyModule_MyAction_Suffix', 'app/subdir/MyAction.php'),
        );
    }

    /**
     * @dataProvider providerValidClassNames
     */
    public function testShouldMapValidAppClassToAppPath($validClassName, $classPath)
    {
        $this->assertEquals(
            $classPath,
            $this->_mapper->mapToPath($validClassName)
        );
    }

    public function providerInvalidClassNames()
    {
        return array(
            array('Module_Action_BadSuffix'),
            array('module_Action_Suffix'),
            array('Module_action_Suffix'),
            array('Module-Action-Suffix'),
            array(''),
        );
    }

    /**
     * @dataProvider providerInvalidClassNames
     */
    public function testShouldIgnoreInvalidAppClassNames($invalidClassName)
    {
        $this->assertNull($this->_mapper->mapToPath($invalidClassName));
    }
}
