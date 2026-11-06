<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * The month by month run of the business. Churn applies to the customers who
 * were already there, and the new ones arrive after it, so a model can never
 * count a customer as churned in the month he signed up.
 */
final class Forecast
{
    /** @var list<Projection> */
    public readonly array $months;

    public function __construct(public readonly Assumptions $assumptions)
    {
        $this->months = $this->run();
    }

    /** @return list<Projection> */
    private function run(): array
    {
        $months = [];
        $customers = 0;
        $cumulative = new Money(0);

        foreach ($this->assumptions->customersByMonth as $index => $row) {
            $churned = (int) round($customers * $this->assumptions->churnRate());
            $customers = $customers - $churned + $row['new'];

            $revenue = $this->assumptions->pricePerCustomer->times($customers);
            $variable = $this->assumptions->costPerCustomer->times($customers);
            $fixed = $this->assumptions->fixedMonthlyCost;
            $profit = $revenue->minus($variable)->minus($fixed);
            $cumulative = $cumulative->plus($profit);

            $months[] = new Projection(
                month: $row['month'],
                newCustomers: $row['new'],
                churnedCustomers: $churned,
                customers: $customers,
                revenue: $revenue,
                variableCost: $variable,
                fixedCost: $fixed,
                profit: $profit,
                cumulative: $cumulative,
            );
        }

        return $months;
    }

    public function customersAtEnd(): int
    {
        $last = $this->months[count($this->months) - 1];

        return $last->customers;
    }

    public function breakEvenMonth(): ?int
    {
        foreach ($this->months as $month) {
            if (!$month->profit->isNegative()) {
                return $month->month;
            }
        }

        return null;
    }

    /** The month the cumulative figure stops being negative, which is the one a founder raises money against. */
    public function paybackMonth(): ?int
    {
        foreach ($this->months as $month) {
            if (!$month->cumulative->isNegative()) {
                return $month->month;
            }
        }

        return null;
    }

    public function totalProfit(): Money
    {
        $sum = new Money(0);
        foreach ($this->months as $month) {
            $sum = $sum->plus($month->profit);
        }

        return $sum;
    }

    /**
     * The deepest cash hole the run opens, which is the amount a round has to
     * cover. A run that never goes below zero reports the lowest point it
     * reached, so the figure is never a made up zero.
     */
    public function worstCumulative(): Money
    {
        $worst = $this->months[0]->cumulative;
        foreach ($this->months as $month) {
            if ($month->cumulative->cents < $worst->cents) {
                $worst = $month->cumulative;
            }
        }

        return $worst;
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(static fn (Projection $p): array => $p->toArray(), $this->months);
    }
}
