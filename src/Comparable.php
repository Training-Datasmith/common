<?php

declare (strict_types=1);
namespace Doctrine\Common;

/**
 * Comparable interface that allows to compare two value objects to each other for similarity.
 *
 * Implementations should compare semantically (by value), not by identity (===).
 * For example, two different DateTime instances that represent the same point in time
 * should return 0.
 *
 * The return value convention mirrors the spaceship operator (<=>):
 *   -1 — $other is greater than $this
 *    0 — $other is semantically equal to $this
 *    1 — $other is less than $this
 *
 * @link   www.doctrine-project.org
 * @since  2.0
 */
interface Comparable
{
    /**
     * Compares the current object to the passed $other.
     *
     * Returns 0 if they are semantically equal, 1 if the other object
     * is less than the current one, or -1 if it is more than the current one.
     *
     * This method should not check for identity using ===, only for semantical equality — for
     * example, when two different DateTime instances point to the exact same Date + TZ.
     *
     * @param mixed $other The value to compare against; may be any type, but implementations
     *                     typically throw \InvalidArgumentException for incompatible types.
     *
     * @return int<-1, 1> -1, 0, or 1 as described above.
     *
     * @throws \InvalidArgumentException If $other cannot be compared to this object.
     *
     * @since 2.0
     */
    public function compare_to(mixed $other): int;
}