<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * One month of the forecast. Every figure here comes from Assumptions, so the
 * page can show the arithmetic that produced it.
 */
final class Projection
{
    public function __construct(
        public readonly int $month,
        public readonly int $newCustomers,
        public readonly int $churnedCustomers,
        public readonly int $customers,
        public readonly Money $revenue,
        public readonly Money $variableCost,
        public readonly Money $fixedCost,
        public readonly Money $profit,
        public readonly Money $cumulative,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'month' => $this->month,
            'new' => $this->newCustomers,
            'churned' => $this->churnedCustomers,
            'customers' => $this->customers,
            'revenue' => $this->revenue->format(),
            'variable' => $this->variableCost->format(),
            'fixed' => $this->fixedCost->format(),
            'profit' => $this->profit->format(),
            'cumulative' => $this->cumulative->format(),
        ];
    }
}
