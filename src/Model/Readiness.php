<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * How far a model is from the shape an investor expects to read. It names what
 * is missing rather than scoring the founder, because a score tells him nothing
 * he can act on.
 */
final class Readiness
{
    /**
     * @param list<string> $gaps
     */
    private function __construct(
        public readonly string $stage,
        public readonly array $gaps,
    ) {
    }

    public static function assess(Forecast $forecast, string $stage): self
    {
        $gaps = [];
        $last = $forecast->months[count($forecast->months) - 1];

        if ($stage === Stage::IDEA) {
            $gaps[] = 'no customer has used the product yet';

            return new self($stage, $gaps);
        }

        if ($forecast->customersAtEnd() === 0) {
            $gaps[] = 'the projection ends with no customers';
        }
        if ($last->revenue->cents <= 0) {
            $gaps[] = 'the projection shows no revenue';
        }
        if ($forecast->breakEvenMonth() === null) {
            $gaps[] = 'the run never reaches a profitable month';
        }
        if ($forecast->paybackMonth() === null) {
            $gaps[] = 'the cash never comes back to zero inside the run';
        }
        if ($forecast->assumptions->monthlyChurnBasisPoints >= 500) {
            $gaps[] = 'churn above five percent a month asks a question the model cannot answer';
        }

        return new self($stage, $gaps);
    }

    public function isInvestable(): bool
    {
        return $this->gaps === [];
    }
}
