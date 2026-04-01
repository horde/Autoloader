<?php

namespace Horde\AutoloadTestFixture\Session;

class Handler
{
    public function getValue(): string
    {
        return 'psr4-session';
    }
}
