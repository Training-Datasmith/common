<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

trait IdentifierField
{
    /** @var int */
    private $identifierFieldInTrait;

    public function getIdentifierFieldInTrait(): int
    {
        return $this->identifierFieldInTrait;
    }
}
