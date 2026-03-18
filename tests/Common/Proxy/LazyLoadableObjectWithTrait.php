<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset representing an object that has fields with respective getters defined in a separate trait
 */
class LazyLoadableObjectWithTrait
{
    use IdentifierField;
}
