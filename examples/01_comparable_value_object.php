<?php

declare(strict_types=1);

/**
 * Example 01 — Implementing the Comparable interface on a value object.
 *
 * Shows how to implement Doctrine\Common\Comparable on a Money value object
 * and use it for sorting and equality comparisons.
 *
 * Run:  php examples/01_comparable_value_object.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Comparable;

/**
 * Immutable monetary amount with currency.
 *
 * Implements Comparable so that Money objects of the same currency can be
 * sorted and compared semantically.
 */
final class Money implements Comparable
{
    public function __construct(
        private readonly int    $amount_cents,
        private readonly string $currency,
    ) {}

    /**
     * Compares this Money to another Money object.
     *
     * Returns:
     *   0  — amounts are equal (same currency + same value)
     *   1  — $other is less than this
     *  -1  — $other is greater than this
     *
     * @throws \InvalidArgumentException if currencies differ.
     */
    public function compare_to(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new \InvalidArgumentException('Can only compare Money to Money.');
        }
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                "Cannot compare {$this->currency} with {$other->currency}."
            );
        }
        return $this->amount_cents <=> $other->amount_cents;
    }

    public function __toString(): string
    {
        return sprintf('%s %.2f', $this->currency, $this->amount_cents / 100);
    }
}

// --- Create a list of amounts and sort using compare_to -----------------------
$amounts = [
    new Money(5000, 'USD'),   // $50.00
    new Money(1000, 'USD'),   // $10.00
    new Money(25000, 'USD'),  // $250.00
    new Money(750, 'USD'),    // $7.50
];

usort($amounts, static fn(Money $a, Money $b): int => $a->compare_to($b));

echo 'Sorted amounts (ascending):' . PHP_EOL;
foreach ($amounts as $money) {
    echo '  ' . $money . PHP_EOL;
}

// --- Equality check -----------------------------------------------------------
$price1 = new Money(1999, 'EUR');
$price2 = new Money(1999, 'EUR');
$price3 = new Money(2000, 'EUR');

echo PHP_EOL . 'price1 == price2: ' . ($price1->compare_to($price2) === 0 ? 'yes' : 'no') . PHP_EOL;
echo 'price1 == price3: ' . ($price1->compare_to($price3) === 0 ? 'yes' : 'no') . PHP_EOL;
