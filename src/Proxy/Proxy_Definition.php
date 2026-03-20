<?php

declare (strict_types=1);
namespace Doctrine\Common\Proxy;

use ReflectionProperty;
/**
 * Definition structure how to create a proxy.
 *
 * @deprecated The ProxyDefinition class is deprecated since doctrine/common 3.5.
 */
class Proxy_Definition
{
    /** @var string */
    public $proxy_class_name;
    /** @var array<string> */
    public $identifier_fields;
    /** @var ReflectionProperty[] */
    public $reflection_fields;
    /** @var callable */
    public $initializer;
    /** @var callable */
    public $cloner;
    /**
     * @param string                            $proxyClassName
     * @param array<string>                     $identifierFields
     * @param array<string, ReflectionProperty> $reflectionFields
     * @param callable                          $initializer
     * @param callable                          $cloner
     */
    public function __construct($proxy_class_name, array $identifier_fields, array $reflection_fields, $initializer, $cloner)
    {
        $this->proxy_class_name = $proxy_class_name;
        $this->identifier_fields = $identifier_fields;
        $this->reflection_fields = $reflection_fields;
        $this->initializer = $initializer;
        $this->cloner = $cloner;
    }
}