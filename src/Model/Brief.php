<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * The page a founder hands to an investor. It states the stage, the three
 * scenarios and the gaps, in plain text, so it survives being pasted anywhere.
 */
final class Brief
{
    public function __construct(
        public readonly string $company,
        public readonly Book $book,
        public readonly ?Round $round,
    ) {
    }

    public function text(): string
    {
        $lines = [];
        $lines[] = $this->company;
        $lines[] = 'Stage: ' . $this->book->stage;
        $lines[] = '';

        foreach ($this->book->scenarios as $scenario) {
            $forecast = $scenario->forecast;
            $lines[] = ucfirst($scenario->name) . ' scenario';
            $lines[] = '  customers at the end: ' . $forecast->customersAtEnd();
            $lines[] = '  total profit: ' . $forecast->totalProfit()->format();
            $lines[] = '  worst cash position: ' . $forecast->worstCumulative()->format();
            $lines[] = '  break even: ' . ($forecast->breakEvenMonth() ?? 'not inside the run');
            $lines[] = '  payback: ' . ($forecast->paybackMonth() ?? 'not inside the run');
            $lines[] = '';
        }

        if ($this->round !== null) {
            $lines[] = 'Round';
            $lines[] = '  raising: ' . $this->round->raising->format();
            $lines[] = '  pre money: ' . $this->round->preMoneyValuation->format();
            $lines[] = '  post money: ' . $this->round->postMoney()->format();
            $lines[] = '  investor holds: ' . $this->round->dilutionPercent() . '%';
            $lines[] = '';
        }

        $plan = $this->book->get('plan');
        $lines[] = 'Missing before an investor reads this';
        if ($plan->readiness->gaps === []) {
            $lines[] = '  nothing the model can name';
        }
        foreach ($plan->readiness->gaps as $gap) {
            $lines[] = '  ' . $gap;
        }

        return implode("\n", $lines) . "\n";
    }
}
