<?php

declare(strict_types=1);

/**
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Bob Mckee <bmckee@bywires.com>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Autoloader
 */

namespace Horde\Autoloader;

/**
 * Interface for autoloader class path mappers.
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @category  Horde
 * @copyright 2008-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Autoloader
 */
interface ClassPathMapper
{
    /**
     * Search for a mapping from class to file path.
     *
     * @param string $className  Classname to load.
     *
     * @return string|false  Pathname to class, or false if not found.
     */
    public function mapToPath(string $className): string|false;
}
