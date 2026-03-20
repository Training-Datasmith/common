<?php

declare (strict_types=1);
namespace Doctrine\Common\Proxy\Exception;

use OutOfBoundsException as BaseOutOfBoundsException;
use function sprintf;
/**
 * Proxy Invalid Argument Exception.
 *
 * @deprecated The OutOfBoundsException class is deprecated since doctrine/common 3.5.
 */
class OutOfBoundsException extends Base_Out_Of_Bounds_Exception implements Proxy_Exception
{
    /**
     * @param string $className
     * @psalm-param class-string $className
     *
     */
    public static function missing_primary_key_value($class_name, string $id_field): self
    {
        return new self(sprintf('Missing value for primary key %s on %s', $id_field, $class_name));
    }
}