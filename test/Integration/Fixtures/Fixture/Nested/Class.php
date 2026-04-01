<?php

/**
 * Nested fixture class for integration testing.
 *
 * This file must NOT be in composer's autoloader.
 */
class Fixture_Nested_Class
{
    public function getValue(): string
    {
        return 'nested';
    }
}
