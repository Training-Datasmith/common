<?php

declare (strict_types=1);
namespace Doctrine\Common\Proxy\Exception;

use Doctrine\Persistence\Proxy;
use function get_class;
use function gettype;
use InvalidArgumentException as BaseInvalidArgumentException;
use function is_object;
use function sprintf;
/**
 * Proxy Invalid Argument Exception.
 *
 * @deprecated The InvalidArgumentException class is deprecated since doctrine/common 3.5.
 */
class InvalidArgumentException extends Base_Invalid_Argument_Exception implements Proxy_Exception
{
    public static function proxy_directory_required(): self
    {
        return new self('You must configure a proxy directory. See docs for details');
    }
    /**
     * @param string $proxyNamespace
     * @psalm-param class-string $className
     *
     */
    public static function not_proxy_class(string $class_name, $proxy_namespace): self
    {
        return new self(sprintf('The class "%s" is not part of the proxy namespace "%s"', $class_name, $proxy_namespace));
    }
    public static function invalid_placeholder(string $name): self
    {
        return new self(sprintf('Provided placeholder for "%s" must be either a string or a valid callable', $name));
    }
    public static function proxy_namespace_required(): self
    {
        return new self('You must configure a proxy namespace');
    }
    public static function unitialized_proxy_expected(Proxy $proxy): self
    {
        return new self(sprintf('Provided proxy of type "%s" must not be initialized.', get_class($proxy)));
    }
    /**
     * @param mixed $callback
     */
    public static function invalid_class_not_found_callback($callback): self
    {
        $type = is_object($callback) ? get_class($callback) : gettype($callback);
        return new self(sprintf('Invalid \$notFoundCallback given: must be a callable, "%s" given', $type));
    }
    /**
     * @psalm-param class-string $className
     *
     */
    public static function class_must_not_be_abstract(string $class_name): self
    {
        return new self(sprintf('Unable to create a proxy for an abstract class "%s".', $class_name));
    }
    /**
     * @psalm-param class-string $className
     *
     */
    public static function class_must_not_be_final(string $class_name): self
    {
        return new self(sprintf('Unable to create a proxy for a final class "%s".', $class_name));
    }
    /**
     * @psalm-param class-string $className
     *
     */
    public static function class_must_not_be_read_only(string $class_name): self
    {
        return new self(sprintf('Unable to create a proxy for a readonly class "%s".', $class_name));
    }
    /** @param mixed $value */
    public static function invalid_auto_generate_mode(string $value): self
    {
        return new self(sprintf('Invalid auto generate mode "%s" given.', $value));
    }
}