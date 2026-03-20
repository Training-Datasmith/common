<?php

declare (strict_types=1);
namespace Doctrine\Common;

use function class_exists;
use const DIRECTORY_SEPARATOR;
use const E_USER_DEPRECATED;
use function interface_exists;
use function is_array;
use function is_file;
use function reset;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function str_replace;
use function stream_resolve_include_path;
use function strpos;
use function trait_exists;
use function trigger_error;
@trigger_error(Class_Loader::class . ' is deprecated.', E_USER_DEPRECATED);
/**
 * A <tt>ClassLoader</tt> is an autoloader for class files that can be
 * installed on the SPL autoload stack. It is a class loader that either loads only classes
 * of a specific namespace or all namespaces and it is suitable for working together
 * with other autoloaders in the SPL autoload stack.
 *
 * If no include path is configured through the constructor or {@link setIncludePath}, a ClassLoader
 * relies on the PHP <code>include_path</code>.
 *
 * @deprecated The ClassLoader is deprecated and will be removed in version 4.0 of doctrine/common.
 */
class Class_Loader
{
    /**
     * PHP file extension.
     *
     * @var string
     */
    protected $file_extension = '.php';
    /**
     * Current namespace.
     *
     * @var string|null
     */
    protected $namespace;
    /**
     * Current include path.
     *
     * @var string|null
     */
    protected $include_path;
    /**
     * PHP namespace separator.
     *
     * @var string
     */
    protected $namespace_separator = '\\';
    /**
     * Creates a new <tt>ClassLoader</tt> that loads classes of the
     * specified namespace from the specified include path.
     *
     * If no include path is given, the ClassLoader relies on the PHP include_path.
     * If neither a namespace nor an include path is given, the ClassLoader will
     * be responsible for loading all classes, thereby relying on the PHP include_path.
     *
     * @param string|null $ns          The namespace of the classes to load.
     * @param string|null $includePath The base include path to use.
     */
    public function __construct($ns = null, $include_path = null)
    {
        $this->namespace = $ns;
        $this->include_path = $include_path;
    }
    /**
     * Sets the namespace separator used by classes in the namespace of this ClassLoader.
     *
     * @param string $sep The separator to use.
     */
    public function set_namespace_separator($sep): void
    {
        $this->namespace_separator = $sep;
    }
    /**
     * Gets the namespace separator used by classes in the namespace of this ClassLoader.
     *
     * @return string
     */
    public function get_namespace_separator()
    {
        return $this->namespace_separator;
    }
    /**
     * Sets the base include path for all class files in the namespace of this ClassLoader.
     *
     * @param string|null $includePath
     */
    public function set_include_path($include_path): void
    {
        $this->include_path = $include_path;
    }
    /**
     * Gets the base include path for all class files in the namespace of this ClassLoader.
     *
     * @return string|null
     */
    public function get_include_path()
    {
        return $this->include_path;
    }
    /**
     * Sets the file extension of class files in the namespace of this ClassLoader.
     *
     * @param string $fileExtension
     */
    public function set_file_extension($file_extension): void
    {
        $this->file_extension = $file_extension;
    }
    /**
     * Gets the file extension of class files in the namespace of this ClassLoader.
     *
     * @return string
     */
    public function get_file_extension()
    {
        return $this->file_extension;
    }
    /**
     * Registers this ClassLoader on the SPL autoload stack.
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }
    /**
     * Removes this ClassLoader from the SPL autoload stack.
     */
    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }
    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     * @psalm-param class-string $className
     *
     * @return bool TRUE if the class has been successfully loaded, FALSE otherwise.
     */
    public function load_class($class_name)
    {
        if (self::type_exists($class_name)) {
            return true;
        }
        if (!$this->can_load_class($class_name)) {
            return false;
        }
        require ($this->include_path !== null ? $this->include_path . DIRECTORY_SEPARATOR : '') . str_replace($this->namespace_separator, DIRECTORY_SEPARATOR, $class_name) . $this->file_extension;
        return self::type_exists($class_name);
    }
    /**
     * Asks this ClassLoader whether it can potentially load the class (file) with
     * the given name.
     *
     * @param string $className The fully-qualified name of the class.
     * @psalm-param class-string $className
     *
     * @return bool TRUE if this ClassLoader can load the class, FALSE otherwise.
     */
    public function can_load_class($class_name)
    {
        if ($this->namespace !== null && strpos($class_name, $this->namespace . $this->namespace_separator) !== 0) {
            return false;
        }
        $file = str_replace($this->namespace_separator, DIRECTORY_SEPARATOR, $class_name) . $this->file_extension;
        if ($this->include_path !== null) {
            return is_file($this->include_path . DIRECTORY_SEPARATOR . $file);
        }
        return stream_resolve_include_path($file) !== false;
    }
    /**
     * Checks whether a class with a given name exists. A class "exists" if it is either
     * already defined in the current request or if there is an autoloader on the SPL
     * autoload stack that is a) responsible for the class in question and b) is able to
     * load a class file in which the class definition resides.
     *
     * If the class is not already defined, each autoloader in the SPL autoload stack
     * is asked whether it is able to tell if the class exists. If the autoloader is
     * a <tt>ClassLoader</tt>, {@link canLoadClass} is used, otherwise the autoload
     * function of the autoloader is invoked and expected to return a value that
     * evaluates to TRUE if the class (file) exists. As soon as one autoloader reports
     * that the class exists, TRUE is returned.
     *
     * Note that, depending on what kinds of autoloaders are installed on the SPL
     * autoload stack, the class (file) might already be loaded as a result of checking
     * for its existence. This is not the case with a <tt>ClassLoader</tt>, who separates
     * these responsibilities.
     *
     * @param string $className The fully-qualified name of the class.
     * @psalm-param class-string $className
     *
     * @return bool TRUE if the class exists as per the definition given above, FALSE otherwise.
     */
    public static function class_exists($class_name)
    {
        return self::type_exists($class_name, true);
    }
    /**
     * Gets the <tt>ClassLoader</tt> from the SPL autoload stack that is responsible
     * for (and is able to load) the class with the given name.
     *
     * @param string $className The name of the class.
     * @psalm-param class-string $className
     *
     * @return ClassLoader|null The <tt>ClassLoader</tt> for the class or NULL if no such <tt>ClassLoader</tt> exists.
     */
    public static function get_class_loader($class_name): ?\Doctrine\Common\Class_Loader
    {
        foreach (spl_autoload_functions() as $loader) {
            if (!is_array($loader)) {
                continue;
            }
            $class_loader = reset($loader);
            if ($class_loader instanceof Class_Loader && $class_loader->can_load_class($class_name)) {
                return $class_loader;
            }
        }
        return null;
    }
    /**
     * Checks whether a given type exists
     *
     * @param string $type
     *
     */
    private static function type_exists($type, bool $autoload = false): bool
    {
        return class_exists($type, $autoload) || interface_exists($type, $autoload) || trait_exists($type, $autoload);
    }
}