<?php

declare (strict_types=1);
namespace Doctrine\Common;

use Exception;
/**
 * Base exception class for the Doctrine\Common package.
 *
 * @deprecated since doctrine/common 3.0 — Use package-specific exception hierarchies instead
 *             (e.g. Doctrine\DBAL\Exception for DBAL errors, Doctrine\ORM\Exception for ORM errors).
 *             This class will be removed in version 4.0.
 *
 * @since 2.0
 */
class Common_Exception extends Exception
{
}