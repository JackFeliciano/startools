<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * Three scenarios built from one set of assumptions, where the slow and the
 * fast versions move the customer count and leave every other figure alone.
 */
final class Book
{
    /** @var list<Scenario> */
    public readonly array $scenarios;

    public function __construct(public readonly Assumptions $base, public readonly string $stage)
    {
        $this->scenarios = [
            $this->scenario('slow', 0.7),
            $this->scenario('plan', 1.0),
            $this->scenario('fast', 1.4),
        ];
    }

    private function scenario(string $name, float $factor): Scenario
    {
        $rows = [];
        foreach ($this->base->customersByMonth as $row) {
            $rows[] = ['month' => $row['month'], 'new' => (int) round($row['new'] * $factor)];
        }

        $assumptions = new Assumptions(
            pricePerCustomer: $this->base->pricePerCustomer,
            monthlyChurnBasisPoints: $this->base->monthlyChurnBasisPoints,
            costPerCustomer: $this->base->costPerCustomer,
            customersByMonth: $rows,
            fixedMonthlyCost: $this->base->fixedMonthlyCost,
            months: $this->base->months,
        );
        $forecast = new Forecast($assumptions);

        return new Scenario($name, $forecast, Readiness::assess($forecast, $this->stage));
    }

    public function get(string $name): Scenario
    {
        foreach ($this->scenarios as $scenario) {
            if ($scenario->name === $name) {
                return $scenario;
            }
        }

        throw new \InvalidArgumentException('no scenario named ' . $name);
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(static fn (Scenario $s): array => $s->toArray(), $this->scenarios);
    }
}
