<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * A named version of the model. A founder shows three of these to an investor:
 * the plan, a slower one and a faster one, so the reader can see which
 * assumption carries the outcome.
 */
final class Scenario
{
    public function __construct(
        public readonly string $name,
        public readonly Forecast $forecast,
        public readonly Readiness $readiness,
    ) {
    }

    public function label(): string
    {
        return $this->name;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'months' => $this->forecast->toArray(),
            'customers_end' => $this->forecast->customersAtEnd(),
            'break_even' => $this->forecast->breakEvenMonth(),
            'payback' => $this->forecast->paybackMonth(),
            'total_profit' => $this->forecast->totalProfit()->format(),
            'worst_cash' => $this->forecast->worstCumulative()->format(),
            'investable' => $this->readiness->isInvestable(),
            'gaps' => $this->readiness->gaps,
        ];
    }
}
