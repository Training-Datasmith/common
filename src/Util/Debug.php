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
     * @link https://xdebug.org/
     *
     * @param mixed $var       The variable to dump.
     * @param int   $maxDepth  The maximum nesting level for object properties.
     * @param bool  $stripTags Whether output should strip HTML tags.
     * @param bool  $echo      Send the dumped value to the output buffer
     *
     * @return string
     */
    public static function dump($var, $max_depth = 2, $strip_tags = true, $echo = true)
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
     * @param mixed $var
     * @param int   $maxDepth
     *
     * @return mixed
     */
    public static function export($var, $max_depth)
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
     * Fill the $return variable with class attributes
     * Based on obj2array function from {@see https://secure.php.net/manual/en/function.get-object-vars.php#47075}
     *
     * @param object $var
     * @param int    $maxDepth
     */
    private static function fill_return_with_class_attributes($var, stdClass $return, $max_depth): stdClass
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
     * Returns a string representation of an object.
     *
     * @param object $obj
     */
    public static function to_string($obj): string
    {
        return method_exists($obj, '__toString') ? (string) $obj : get_class($obj) . '@' . spl_object_hash($obj);
    }
}