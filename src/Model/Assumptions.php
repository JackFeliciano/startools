<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * The inputs a founder commits to. The model reads nothing else, so a reader
 * can challenge each number by name instead of arguing with an output.
 */
final class Assumptions
{
    /**
     * @param list<array{month: int, new: int}> $customersByMonth
     */
    public function __construct(
        public readonly Money $pricePerCustomer,
        public readonly int $monthlyChurnBasisPoints,
        public readonly Money $costPerCustomer,
        public readonly array $customersByMonth,
        public readonly Money $fixedMonthlyCost,
        public readonly int $months,
    ) {
        if ($months < 1) {
            throw new \InvalidArgumentException('a model needs at least one month');
        }
        if ($monthlyChurnBasisPoints < 0 || $monthlyChurnBasisPoints > 10000) {
            throw new \InvalidArgumentException('churn is expressed in basis points between 0 and 10000');
        }
        if ($pricePerCustomer->isNegative() || $costPerCustomer->isNegative()) {
            throw new \InvalidArgumentException('price and cost per customer cannot be negative');
        }
        if (count($customersByMonth) !== $months) {
            throw new \InvalidArgumentException('one new customer figure is required for every month');
        }
    }

    public function churnRate(): float
    {
        return $this->monthlyChurnBasisPoints / 10000;
    }
}
