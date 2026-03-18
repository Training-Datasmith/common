<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

class LazyLoadableObjectWithPHP82UnionAndIntersectionType
{
    private (\stdClass&\Stringable)|null $identifierFieldUnionAndIntersectionType = null;

    public function getIdentifierFieldUnionAndIntersectionType(): (\stdClass&\Stringable)|null
    {
        return $this->identifierFieldUnionAndIntersectionType;
    }
}
