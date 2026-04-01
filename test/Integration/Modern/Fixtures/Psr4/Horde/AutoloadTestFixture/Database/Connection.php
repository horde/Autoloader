<?php

namespace Horde\AutoloadTestFixture\Database;

class Connection
{
    public function getValue(): string
    {
        return 'psr4-database';
    }
}
