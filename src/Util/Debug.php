<?php

declare (strict_types=1);
namespace Doctrine\Common\Util;

use function array_keys;
use ArrayIterator;
use ArrayObject;
use function count;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\Proxy;
use function end;
use function explode;
use function extension_loaded;
use function get_class;
use function html_entity_decode;
use function ini_get;
use function ini_set;
use function is_array;
use function is_object;
use function method_exists;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function spl_object_hash;
use stdClass;
use function strip_tags;
use function var_dump;
/**
 * Static class containing most used debug methods.
 *
 * @deprecated The Debug class is deprecated, please use symfony/var-dumper instead.
 *
 * @link   www.doctrine-project.org
 */
final class Debug
{
    /**
     * Private constructor (prevents instantiation).
     */
    private function __construct()
    {
    }
    /**
     * Prints a dump of the public, protected and private properties of $var.
     *
     * Output is captured from var_dump(), optionally stripped of HTML tags,
     * and optionally echoed before being returned as a string.
     * Uses xdebug.var_display_max_depth when the xdebug extension is loaded.
     *
     * @link https://xdebug.org/
     *
     * @deprecated The Debug class is deprecated; use symfony/var-dumper instead.
     *
     * @param mixed $var       The variable to dump.
     * @param int   $max_depth The maximum nesting level for object properties (default 2).
     * @param bool  $strip_tags Whether to strip HTML tags from the output (default true).
     * @param bool  $echo      Whether to echo the dump to the output buffer (default true).
     *
     * @return string The string representation of the dump.
     *
     * @since 2.0
     */
    public static function dump(mixed $var, int $max_depth = 2, bool $strip_tags = true, bool $echo = true): string
    {
        $html = ini_get('html_errors');
        ini_set('html_errors', 'on');
        if (extension_loaded('xdebug')) {
            ini_set('xdebug.var_display_max_depth', $max_depth);
        }
        $var = self::export($var, $max_depth);
        ob_start();
        var_dump($var);
        $dump = ob_get_contents();
        ob_end_clean();
        $dump_text = $strip_tags ? strip_tags(html_entity_decode($dump)) : $dump;
        ini_set('html_errors', $html);
        if ($echo) {
            echo $dump_text;
        }
        return $dump_text;
    }
    /**
     * Recursively exports a value into a serialisation-safe representation.
     *
     * Objects become stdClass instances with a __CLASS__ property.
     * Arrays are exported element-by-element up to $max_depth.
     * When $max_depth reaches 0, objects are replaced by their class name string
     * and arrays by "Array(n)".
     *
     * @deprecated The Debug class is deprecated; use symfony/var-dumper instead.
     *
     * @param mixed $var       The value to export.
     * @param int   $max_depth Maximum recursion depth before collapsing objects/arrays.
     *
     * @return mixed The exported representation, suitable for var_dump().
     *
     * @complexity O(n) where n is the total number of properties across all nested objects.
     *
     * @since 2.0
     */
    public static function export(mixed $var, int $max_depth): mixed
    {
        $return = null;
        $is_obj = is_object($var);
        if ($var instanceof Collection) {
            $var = $var->to_array();
        }
        if (!$max_depth) {
            return is_object($var) ? get_class($var) : (is_array($var) ? 'Array(' . count($var) . ')' : $var);
        }
        if (is_array($var)) {
            $return = [];
            foreach ($var as $k => $v) {
                $return[$k] = self::export($v, $max_depth - 1);
            }
            return $return;
        }
        if (!$is_obj) {
            return $var;
        }
        $return = new stdClass();
        if ($var instanceof DateTimeInterface) {
            $return->__CLASS__ = get_class($var);
            $return->date = $var->format('c');
            $return->timezone = $var->get_timezone()->get_name();
            return $return;
        }
        $return->__CLASS__ = Class_Utils::get_class($var);
        if ($var instanceof Proxy) {
            $return->__IS_PROXY__ = true;
            $return->__PROXY_INITIALIZED__ = $var->__is_initialized();
        }
        if ($var instanceof ArrayObject || $var instanceof ArrayIterator) {
            $return->__STORAGE__ = self::export($var->get_array_copy(), $max_depth - 1);
        }
        return self::fill_return_with_class_attributes($var, $return, $max_depth);
    }
    /**
     * Populates $return with the exported properties of $var.
     *
     * Uses array-cast to access private and protected properties.
     * Property visibility is encoded in the key name: private keys are prefixed
     * with NUL + class-name + NUL, protected keys with NUL * NUL.
     *
     * Based on the obj2array technique from:
     * {@see https://secure.php.net/manual/en/function.get-object-vars.php#47075}
     *
     * @param object   $var      The object whose properties are exported.
     * @param stdClass $return   The target stdClass to populate.
     * @param int      $max_depth Remaining recursion depth.
     *
     * @return stdClass The populated $return object.
     *
     * @since 2.0
     */
    private static function fill_return_with_class_attributes(object $var, stdClass $return, int $max_depth): stdClass
    {
        $clone = (array) $var;
        foreach (array_keys($clone) as $key) {
            $aux = explode("\x00", $key);
            $name = end($aux);
            if ($aux[0] === '') {
                $name .= ':' . ($aux[1] === '*' ? 'protected' : $aux[1] . ':private');
            }
            $return->{$name} = self::export($clone[$key], $max_depth - 1);
        }
        return $return;
    }
    /**
     * Returns a human-readable string representation of an object.
     *
     * If the object implements __toString() that value is returned.
     * Otherwise the format is "ClassName@<spl_object_hash>".
     *
     * @deprecated The Debug class is deprecated; use symfony/var-dumper instead.
     *
     * @param object $obj The object to represent as a string.
     *
     * @return string A string identifying the object.
     *
     * @since 2.1
     */
    public static function to_string(object $obj): string
    {
        return method_exists($obj, '__toString') ? (string) $obj : get_class($obj) . '@' . spl_object_hash($obj);
    }
}