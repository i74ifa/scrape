<?php

namespace App\Services;

use JsonSerializable;
use Stringable;

/**
 * Value object returned by Currency::convert().
 *
 * Holds a converted amount together with its target currency so callers can
 * chain a formatting step, e.g. Currency::convert(...)->format().
 *
 * In a JSON response (and when cast to a string) it renders as the formatted
 * amount with its currency symbol. Use value() when you need the raw float for
 * storage or math.
 */
class Money implements JsonSerializable, Stringable
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency,
    ) {}

    /**
     * The raw converted amount (use this when storing to the DB or doing math).
     */
    public function value(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * The amount rendered with its currency symbol, e.g. "1,234 ر.ي".
     */
    public function format(): string
    {
        return Currency::format($this->amount, $this->currency);
    }

    public function jsonSerialize(): mixed
    {
        return $this->format();
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
