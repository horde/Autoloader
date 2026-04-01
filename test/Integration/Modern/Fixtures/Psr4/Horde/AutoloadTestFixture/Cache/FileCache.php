<?php

namespace Horde\AutoloadTestFixture\Cache;

class FileCache
{
    public function getValue(): string
    {
        return 'psr4-cache';
    }
}
