<?php

declare (strict_types=1);
namespace Doctrine\Common\Util;

use Doctrine\Persistence\Proxy;
use function get_class;
use function get_parent_class;
use function ltrim;
use ReflectionClass;
use function rtrim;
use function strrpos;
use function substr;
/**
 * Class and reflection related functionality for objects that
 * might or not be proxy objects at the moment.
 */
class Class_Utils
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @param string $className
     * @psalm-param class-string<Proxy<T>>|class-string<T> $className
     *
     * @return string
     * @psalm-return class-string<T>
     *
     * @template T of object
     */
    public static function get_real_class($class_name)
    {
        $pos = strrpos($class_name, '\\' . Proxy::MARKER . '\\');
        if ($pos === false) {
            /** @psalm-var class-string<T> */
            return $class_name;
        }
        return substr($class_name, $pos + Proxy::MARKER_LENGTH + 2);
    }
    /**
     * Gets the real class name of an object (even if its a proxy).
     *
     * @param object $object
     * @psalm-param Proxy<T>|T $object
     *
     * @return string
     * @psalm-return class-string<T>
     *
     * @template T of object
     */
    public static function get_class($object)
    {
        return self::get_real_class(get_class($object));
    }
    /**
     * Gets the real parent class name of a class or object.
     *
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return string
     * @psalm-return class-string
     */
    public static function get_parent_class($class_name)
    {
        return get_parent_class(self::get_real_class($class_name));
    }
    /**
     * Creates a new reflection class.
     *
     * @param string $className
     * @psalm-param class-string $className
     */
    public static function new_reflection_class($class_name): \ReflectionClass
    {
        return new ReflectionClass(self::get_real_class($class_name));
    }
    /**
     * Creates a new reflection object.
     *
     * @param object $object
     *
     * @return ReflectionClass
     */
    public static function new_reflection_object($object)
    {
        return self::new_reflection_class(self::get_class($object));
    }
    /**
     * Given a class name and a proxy namespace returns the proxy name.
     *
     * @param string $className
     * @param string $proxyNamespace
     * @psalm-param class-string $className
     *
     * @psalm-return class-string
     */
    public static function generate_proxy_class_name($class_name, $proxy_namespace): string
    {
        return rtrim($proxy_namespace, '\\') . '\\' . Proxy::MARKER . '\\' . ltrim($class_name, '\\');
    }
}