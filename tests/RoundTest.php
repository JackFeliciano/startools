<?php

declare(strict_types=1);

use Startools\Model\Money;
use Startools\Model\Round;
use Startools\Model\Sample;
use Startools\Tests\Assert;

return [
    ['the post money is the pre money plus the raise', static function (): void {
        $round = new Round(new Money(200000), new Money(800000));
        Assert::same(1000000, $round->postMoney()->cents);
    }],
    ['dilution is the raise over the post money', static function (): void {
        $round = new Round(new Money(250000), new Money(750000));
        Assert::same(25.0, $round->dilutionPercent());
        Assert::same(75.0, $round->founderPercent());
    }],
    ['a round refuses a zero raise', static function (): void {
        try {
            new Round(new Money(0), new Money(100));
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['a round refuses a zero valuation', static function (): void {
        try {
            new Round(new Money(100), new Money(0));
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['runway divides the raise by the average burn', static function (): void {
        $round = new Round(Money::fromFloat(120000), Money::fromFloat(480000));
        $months = $round->runwayMonths(Sample::book()->get('plan')->forecast);
        Assert::true($months !== null && $months > 0, 'expected a positive runway');
    }],
];
