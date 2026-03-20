<?php

declare (strict_types=1);
namespace Doctrine\Common\Proxy;

use function call_user_func;
use Closure;
use const DIRECTORY_SEPARATOR;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use function file_exists;
use function is_callable;
use function ltrim;
use function spl_autoload_register;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
/**
 * Special Autoloader for Proxy classes, which are not PSR-0 compliant.
 *
 * @internal
 * @deprecated The Autoloader class is deprecated since doctrine/common 3.5.
 */
class Autoloader
{
    /**
     * Resolves proxy class name to a filename based on the following pattern.
     *
     * 1. Remove Proxy namespace from class name.
     * 2. Remove namespace separators from remaining class name.
     * 3. Return PHP filename from proxy-dir with the result from 2.
     *
     * @param string $proxyNamespace
     * @param string $className
     * @psalm-param class-string $className
     *
     *
     * @throws InvalidArgumentException
     */
    public static function resolve_file(string $proxy_dir, $proxy_namespace, $class_name): string
    {
        if (strpos($class_name, $proxy_namespace) !== 0) {
            throw InvalidArgumentException::not_proxy_class($class_name, $proxy_namespace);
        }
        // remove proxy namespace from class name
        $class_name_relative_to_proxy_namespace = substr($class_name, strlen($proxy_namespace));
        // remove namespace separators from remaining class name
        $file_name = str_replace('\\', '', $class_name_relative_to_proxy_namespace);
        return $proxy_dir . DIRECTORY_SEPARATOR . $file_name . '.php';
    }
    /**
     * Registers and returns autoloader callback for the given proxy dir and namespace.
     *
     * @param string        $proxyDir
     * @param string        $proxyNamespace
     * @param callable|null $notFoundCallback Invoked when the proxy file is not found.
     *
     * @return Closure
     *
     * @throws InvalidArgumentException
     */
    public static function register($proxy_dir, $proxy_namespace, $not_found_callback = null)
    {
        $proxy_namespace = ltrim($proxy_namespace, '\\');
        if ($not_found_callback !== null && !is_callable($not_found_callback)) {
            throw InvalidArgumentException::invalid_class_not_found_callback($not_found_callback);
        }
        $autoloader = static function ($class_name) use ($proxy_dir, $proxy_namespace, $not_found_callback): void {
            if ($proxy_namespace === '') {
                return;
            }
            if (strpos($class_name, $proxy_namespace) !== 0) {
                return;
            }
            $file = Autoloader::resolve_file($proxy_dir, $proxy_namespace, $class_name);
            if ($not_found_callback && !file_exists($file)) {
                call_user_func($not_found_callback, $proxy_dir, $proxy_namespace, $class_name);
            }
            require $file;
        };
        spl_autoload_register($autoloader);
        return $autoloader;
    }
}