# Architecture: doctrine/common

## Purpose

Doctrine Common is the shared utility library consumed by all other Doctrine projects. It provides proxy generation for lazy-loading, a legacy class autoloader (deprecated), a debugging utility (deprecated), and the `Comparable` interface. Most of its original functionality has been split into focused sub-packages (`doctrine/persistence`, `doctrine/collections`, `doctrine/deprecations`).

## Directory Structure

```
src/
  Class_Loader.php         — PSR-0 class autoloader (DEPRECATED since 3.x; use Composer)
  Common_Exception.php     — Base exception for the common package (DEPRECATED)
  Comparable.php           — Interface for semantic value-object comparison
  Proxy/
    Proxy.php                        — Marker interface for generated proxy objects
    Proxy_Definition.php             — Value object: proxy class name, namespace, proxy dir, interfaces
    Abstract_Proxy_Factory.php       — Base factory: manages caching, auto-generation, and registration
    Proxy_Generator.php              — Generates proxy PHP source code from class metadata (DEPRECATED since 3.5)
    Autoloader.php                   — Registers a proxy-specific class autoloader function
    Exception/
      Proxy_Exception.php            — Base proxy exception
      Invalid_Argument_Exception.php — Thrown on bad proxy factory configuration
      Unexpected_Value_Exception.php — Thrown when generated file content is wrong
      Out_Of_Bounds_Exception.php    — Thrown when proxy directory is inaccessible
  Util/
    Class_Utils.php        — Helper: resolves the real class behind a proxy via `get_parent_class()`
    Debug.php              — var_dump wrapper (DEPRECATED since 3.x; use symfony/var-dumper)

tests/
```

## Key Design Decisions

1. **Proxy generation is now deprecated.** Doctrine ORM 3.x uses `doctrine/persistence` proxies. `Proxy_Generator` and `Abstract_Proxy_Factory` remain for BC but will be removed in a future major.

2. **Atomic file writing.** `Proxy_Generator` writes to a temp file then `rename()`s it into place, preventing partial reads of a proxy file if two processes regenerate simultaneously.

3. **`Comparable` as a value-object contract.** Returns −1 / 0 / 1, following the same convention as the spaceship operator `<=>`, usable in `usort` callbacks.

4. **`Class_Utils::getClass()`** resolves the real entity class behind a lazy proxy by walking `get_parent_class()` until a non-proxy class is found. This is the canonical way for the rest of the stack to obtain the "real" class name.

## Extension Points

- Implement `Comparable` on value objects to enable semantic sorting without coupling to identity.
- Extend `Abstract_Proxy_Factory` to customise proxy generation or registration hooks.

## Dependency Flow

```
Doctrine\Common\Proxy\Abstract_Proxy_Factory
  └──> Proxy_Generator  (generates PHP source)
  └──> Autoloader       (registers proxy namespace autoloader)
  └──> Proxy_Definition (configuration value object)

Doctrine\Common\Util\Class_Utils
  └──> resolves proxy → real class via get_parent_class()
```

## Deprecation Status

| Class | Status |
|-------|--------|
| `Class_Loader` | Deprecated — use Composer autoloading |
| `Common_Exception` | Deprecated — use package-specific exceptions |
| `Debug` | Deprecated — use `symfony/var-dumper` |
| `Proxy_Generator` | Deprecated since 3.5 — ORM 3.x uses `doctrine/persistence` proxies |
