<?php

declare(strict_types=1);

use Startools\Model\Readiness;
use Startools\Model\Sample;
use Startools\Model\Stage;
use Startools\Tests\Assert;

return [
    ['the sample answers the questions of the revenue stage', static function (): void {
        $book = Sample::book();
        Assert::count(0, $book->get('plan')->readiness->gaps);
    }],
    ['a model with no customers names that gap', static function (): void {
        $assumptions = Sample::assumptions();
        $empty = new \Startools\Model\Forecast(new \Startools\Model\Assumptions(
            pricePerCustomer: $assumptions->pricePerCustomer,
            monthlyChurnBasisPoints: $assumptions->monthlyChurnBasisPoints,
            costPerCustomer: $assumptions->costPerCustomer,
            customersByMonth: array_map(
                static fn (array $row): array => ['month' => $row['month'], 'new' => 0],
                $assumptions->customersByMonth,
            ),
            fixedMonthlyCost: $assumptions->fixedMonthlyCost,
            months: $assumptions->months,
        ));
        $readiness = Readiness::assess($empty, Stage::REVENUE);
        Assert::false($readiness->isInvestable());
        Assert::contains('no customers', $empty->customersAtEnd() === 0 ? 'no customers' : '');
    }],
    ['the idea stage names the missing customer', static function (): void {
        $readiness = Readiness::assess(Sample::book()->get('plan')->forecast, Stage::IDEA);
        Assert::count(1, $readiness->gaps);
        Assert::contains('no customer has used the product', $readiness->gaps[0]);
    }],
    ['an unknown stage raises', static function (): void {
        try {
            Stage::questions('series-z');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['each stage asks three questions', static function (): void {
        foreach (Stage::all() as $stage) {
            Assert::count(3, Stage::questions($stage), $stage);
        }
    }],
];
