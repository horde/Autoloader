<?php
/**
 * Copyright 2008-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Bob Mckee <bmckee@bywires.com>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Autoloader
 */

require_once dirname(__FILE__, 2) . '/Autoloader.php';
require_once dirname(__FILE__, 1) . '/ClassPathMapper.php';
require_once dirname(__FILE__, 1) . '/ClassPathMapper/Default.php';

/**
 * Default autoloader definition that uses the include path with default
 * class path mappers.
 *
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Bob Mckee <bmckee@bywires.com>
 * @category  Horde
 * @copyright 2008-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
class Horde_Autoloader_Default extends Horde_Autoloader
{
    /**
     */
    public function __construct()
    {
        $paths = array_map(
            'realpath',
            array_diff(
                array_unique(explode(PATH_SEPARATOR, get_include_path())),
                array('.')
            )
        );

        foreach (array_reverse($paths) as $path) {
            $this->addClassPathMapper(
                new Horde_Autoloader_ClassPathMapper_Default($path)
            );
        }
    }

}

/* Load default autoloader and register. */
$__autoloader = new Horde_Autoloader_Default();
$__autoloader->registerAutoloader();
