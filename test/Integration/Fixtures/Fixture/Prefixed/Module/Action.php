<?php

/**
 * Prefixed fixture class for integration testing.
 *
 * This file must NOT be in composer's autoloader.
 */
class Fixture_Prefixed_Module_Action
{
    public function getValue(): string
    {
        return 'prefixed';
    }
}
