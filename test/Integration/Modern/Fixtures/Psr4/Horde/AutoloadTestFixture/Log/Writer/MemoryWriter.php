<?php

namespace Horde\AutoloadTestFixture\Log\Writer;

class MemoryWriter
{
    public function getValue(): string
    {
        return 'psr4-memory';
    }
}
