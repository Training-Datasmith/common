<?php

declare (strict_types=1);
namespace Doctrine\Common\Proxy\Exception;

use function sprintf;
use Throwable;
use UnexpectedValueException as BaseUnexpectedValueException;
/**
 * Proxy Unexpected Value Exception.
 *
 * @deprecated The UnexpectedValueException class is deprecated since doctrine/common 3.5.
 */
class UnexpectedValueException extends Base_Unexpected_Value_Exception implements Proxy_Exception
{
    public static function proxy_directory_not_writable(string $proxy_directory): self
    {
        return new self(sprintf('Your proxy directory "%s" must be writable', $proxy_directory));
    }
    /**
     * @param string $className
     * @param string $methodName
     * @psalm-param class-string $className
     *
     */
    public static function invalid_parameter_type_hint($class_name, $method_name, string $parameter_name, ?Throwable $previous = null): self
    {
        return new self(sprintf('The type hint of parameter "%s" in method "%s" in class "%s" is invalid.', $parameter_name, $method_name, $class_name), 0, $previous);
    }
    /**
     * @param string $className
     * @psalm-param class-string $className
     *
     */
    public static function invalid_return_type_hint($class_name, string $method_name, ?Throwable $previous = null): self
    {
        return new self(sprintf('The return type of method "%s" in class "%s" is invalid.', $method_name, $class_name), 0, $previous);
    }
}