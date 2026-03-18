<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class InvalidTypeHintClass
{
    public function invalidTypeHintMethod(InvalidHint $foo)
    {
    }
}
