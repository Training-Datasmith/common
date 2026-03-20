<?php

declare(strict_types=1);

/**
 * Example 02 — Class_Utils::get_class() for proxy-aware class resolution.
 *
 * When Doctrine ORM wraps an entity in a lazy-loading proxy, the object's
 * class is a generated subclass. Class_Utils::get_class() resolves the real
 * entity class so you can look it up in the metadata factory without caring
 * whether the object is proxied.
 *
 * Run:  php examples/02_class_utils.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Util\Class_Utils;

// --- A plain domain entity ----------------------------------------------------
final class User
{
    public function __construct(public readonly int $id, public readonly string $name) {}
}

// --- Simulate what ORM does: create a proxy subclass at runtime ---------------
// In real use this generated class lives in the proxy cache directory.
eval('
class UserProxy extends User implements \Doctrine\Persistence\Proxy {
    private bool $__initialised = false;
    public function __is_initialized(): bool { return $this->__initialised; }
    public function __set_initialized(bool $v): void { $this->__initialised = $v; }
    public function __load(): void { $this->__initialised = true; }
}
');

$real_user  = new User(1, 'Alice');
$proxy_user = new UserProxy(2, 'Bob (proxy)');

// --- Without Class_Utils: get_class() returns the proxy class name -----------
echo 'get_class($real_user):  ' . get_class($real_user) . PHP_EOL;   // User
echo 'get_class($proxy_user): ' . get_class($proxy_user) . PHP_EOL;  // UserProxy

// --- With Class_Utils: always returns the real class name --------------------
echo PHP_EOL;
echo 'Class_Utils::get_class($real_user):  ' . Class_Utils::get_class($real_user) . PHP_EOL;   // User
echo 'Class_Utils::get_class($proxy_user): ' . Class_Utils::get_class($proxy_user) . PHP_EOL;  // User

// --- Typical usage in framework code ------------------------------------------
function get_repository_for(object $entity): string
{
    $class = Class_Utils::get_class($entity);
    return $class . 'Repository';  // UserRepository — even for proxied entities
}

echo PHP_EOL . 'Repository for real user:  ' . get_repository_for($real_user) . PHP_EOL;
echo 'Repository for proxy user: ' . get_repository_for($proxy_user) . PHP_EOL;
