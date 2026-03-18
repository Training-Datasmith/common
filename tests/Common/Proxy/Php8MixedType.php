<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

class Php8MixedType
{
    public function foo(mixed $bar): mixed
    {
        return 1;
    }
}
