<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

class Php8MagicCloneClass
{
    public function __clone(): void
    {
    }
}
