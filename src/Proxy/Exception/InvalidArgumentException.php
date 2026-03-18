<?php

declare(strict_types=1);

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
class InvalidArgumentException extends BaseInvalidArgumentException implements ProxyException
{
    public static function proxyDirectoryRequired(): self
    {
        return new self('You must configure a proxy directory. See docs for details');
    }

    /**
     * @param string $proxyNamespace
     * @psalm-param class-string $className
     *
     */
    public static function notProxyClass(string $className, $proxyNamespace): self
    {
        return new self(sprintf('The class "%s" is not part of the proxy namespace "%s"', $className, $proxyNamespace));
    }

    public static function invalidPlaceholder(string $name): self
    {
        return new self(sprintf('Provided placeholder for "%s" must be either a string or a valid callable', $name));
    }

    public static function proxyNamespaceRequired(): self
    {
        return new self('You must configure a proxy namespace');
    }

    public static function unitializedProxyExpected(Proxy $proxy): self
    {
        return new self(sprintf('Provided proxy of type "%s" must not be initialized.', get_class($proxy)));
    }

    /**
     * @param mixed $callback
     */
    public static function invalidClassNotFoundCallback($callback): self
    {
        $type = is_object($callback) ? get_class($callback) : gettype($callback);

        return new self(sprintf('Invalid \$notFoundCallback given: must be a callable, "%s" given', $type));
    }

    /**
     * @psalm-param class-string $className
     *
     */
    public static function classMustNotBeAbstract(string $className): self
    {
        return new self(sprintf('Unable to create a proxy for an abstract class "%s".', $className));
    }

    /**
     * @psalm-param class-string $className
     *
     */
    public static function classMustNotBeFinal(string $className): self
    {
        return new self(sprintf('Unable to create a proxy for a final class "%s".', $className));
    }

    /**
     * @psalm-param class-string $className
     *
     */
    public static function classMustNotBeReadOnly(string $className): self
    {
        return new self(sprintf('Unable to create a proxy for a readonly class "%s".', $className));
    }

    /** @param mixed $value */
    public static function invalidAutoGenerateMode(string $value): self
    {
        return new self(sprintf('Invalid auto generate mode "%s" given.', $value));
    }
}
