<?php

declare(strict_types=1);

namespace Doctrine\Common\Proxy\Exception;

use OutOfBoundsException as BaseOutOfBoundsException;

use function sprintf;

/**
 * Proxy Invalid Argument Exception.
 *
 * @deprecated The OutOfBoundsException class is deprecated since doctrine/common 3.5.
 */
class OutOfBoundsException extends BaseOutOfBoundsException implements ProxyException
{
    /**
     * @param string $className
     * @psalm-param class-string $className
     *
     */
    public static function missingPrimaryKeyValue($className, string $idField): self
    {
        return new self(sprintf('Missing value for primary key %s on %s', $idField, $className));
    }
}
