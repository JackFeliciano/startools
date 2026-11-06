<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * What a round costs the founder. The dilution comes from the pre-money and the
 * amount raised, and the model shows the post-money so nobody multiplies in
 * his head and arrives somewhere else.
 */
final class Round
{
    public function __construct(
        public readonly Money $raising,
        public readonly Money $preMoneyValuation,
    ) {
        if ($raising->cents <= 0) {
            throw new \InvalidArgumentException('a round raises a positive amount');
        }
        if ($preMoneyValuation->cents <= 0) {
            throw new \InvalidArgumentException('a round needs a pre-money valuation');
        }
    }

    public function postMoney(): Money
    {
        return $this->preMoneyValuation->plus($this->raising);
    }

    /** The share the new investor holds, as a percentage of the post-money. */
    public function dilutionPercent(): float
    {
        return round($this->raising->cents / $this->postMoney()->cents * 100, 2);
    }

    public function founderPercent(): float
    {
        return round(100 - $this->dilutionPercent(), 2);
    }

    /** How many months this round funds, at the average burn the forecast shows. */
    public function runwayMonths(Forecast $forecast): ?int
    {
        $burn = 0;
        $count = 0;
        foreach ($forecast->months as $month) {
            if ($month->profit->isNegative()) {
                $burn += -$month->profit->cents;
                $count++;
            }
        }
        if ($count === 0) {
            return null;
        }

        return (int) floor($this->raising->cents / ($burn / $count));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'raising' => $this->raising->format(),
            'pre_money' => $this->preMoneyValuation->format(),
            'post_money' => $this->postMoney()->format(),
            'dilution' => $this->dilutionPercent(),
            'founder' => $this->founderPercent(),
        ];
    }
}
