<?php

/**
 * Simple fixture class for integration testing.
 *
 * This file must NOT be in composer's autoloader.
 * It is used to test that Horde_Autoloader can actually
 * locate and include real class files.
 */
class Fixture_Simple
{
    public function getValue(): string
    {
        return 'simple';
    }
}
