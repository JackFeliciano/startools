<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * An amount in cents, so a model never carries a rounding error. Currency
 * formatting happens once, at the edge, and the arithmetic stays integer.
 */
final class Money
{
    public function __construct(public readonly int $cents)
    {
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public function plus(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function minus(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function times(float $factor): self
    {
        return new self((int) round($this->cents * $factor));
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function toFloat(): float
    {
        return $this->cents / 100;
    }

    /** Two decimal places with a thousands separator, so a reader can scan a table. */
    public function format(): string
    {
        return number_format($this->toFloat(), 2, '.', ',');
    }
}
