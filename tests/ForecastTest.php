<?php

declare(strict_types=1);

use Startools\Model\Assumptions;
use Startools\Model\Forecast;
use Startools\Model\Money;
use Startools\Tests\Assert;

/**
 * One month at a hundred a customer with no churn and no cost, so the expected
 * figures can be read off the assumptions directly.
 */
function simple(int $churn = 0, int $new = 10): Forecast
{
    $rows = [];
    for ($i = 1; $i <= 3; $i++) {
        $rows[] = ['month' => $i, 'new' => $new];
    }

    return new Forecast(new Assumptions(
        pricePerCustomer: new Money(10000),
        monthlyChurnBasisPoints: $churn,
        costPerCustomer: new Money(0),
        customersByMonth: $rows,
        fixedMonthlyCost: new Money(0),
        months: 3,
    ));
}

function simpleWithFixed(Money $fixed): Forecast
{
    $rows = [];
    for ($i = 1; $i <= 3; $i++) {
        $rows[] = ['month' => $i, 'new' => 10];
    }

    return new Forecast(new Assumptions(
        pricePerCustomer: new Money(10000),
        monthlyChurnBasisPoints: 0,
        costPerCustomer: new Money(0),
        customersByMonth: $rows,
        fixedMonthlyCost: $fixed,
        months: 3,
    ));
}

return [
    ['customers accumulate when nothing churns', static function (): void {
        $forecast = simple();
        Assert::same(30, $forecast->customersAtEnd());
        Assert::same(10, $forecast->months[0]->customers);
        Assert::same(20, $forecast->months[1]->customers);
    }],
    ['revenue is the customer count times the price', static function (): void {
        $forecast = simple();
        Assert::same(300000, $forecast->months[2]->revenue->cents);
    }],
    ['churn applies to the customers who were already there', static function (): void {
        $forecast = simple(1000);
        Assert::same(0, $forecast->months[0]->churnedCustomers);
        Assert::same(1, $forecast->months[1]->churnedCustomers);
        Assert::same(19, $forecast->months[1]->customers);
    }],
    ['a new customer is never counted as churned in his own month', static function (): void {
        $forecast = simple(5000, 10);
        Assert::same(0, $forecast->months[0]->churnedCustomers);
        Assert::same(10, $forecast->months[0]->customers);
    }],
    ['profit subtracts variable and fixed cost', static function (): void {
        $rows = [['month' => 1, 'new' => 10]];
        $forecast = new Forecast(new Assumptions(
            pricePerCustomer: new Money(10000),
            monthlyChurnBasisPoints: 0,
            costPerCustomer: new Money(4000),
            customersByMonth: $rows,
            fixedMonthlyCost: new Money(5000),
            months: 1,
        ));
        Assert::same(55000, $forecast->months[0]->profit->cents);
    }],
    ['the cumulative figure adds the months', static function (): void {
        $forecast = simple();
        Assert::same(100000, $forecast->months[0]->cumulative->cents);
        Assert::same(300000, $forecast->months[1]->cumulative->cents);
        Assert::same(600000, $forecast->months[2]->cumulative->cents);
    }],
    ['break even names the first month the fixed cost is covered', static function (): void {
        $rows = [];
        foreach ([1, 2, 3] as $i) {
            $rows[] = ['month' => $i, 'new' => 10];
        }
        $forecast = new Forecast(new Assumptions(
            pricePerCustomer: new Money(10000),
            monthlyChurnBasisPoints: 0,
            costPerCustomer: new Money(0),
            customersByMonth: $rows,
            fixedMonthlyCost: new Money(15000),
            months: 3,
        ));
        Assert::same(1, $forecast->breakEvenMonth());
        Assert::same(1, $forecast->paybackMonth());
    }],
    ['payback names the month the cash comes back to zero', static function (): void {
        $rows = [];
        foreach ([1, 2, 3] as $i) {
            $rows[] = ['month' => $i, 'new' => 10];
        }
        $forecast = new Forecast(new Assumptions(
            pricePerCustomer: new Money(10000),
            monthlyChurnBasisPoints: 0,
            costPerCustomer: new Money(0),
            customersByMonth: $rows,
            fixedMonthlyCost: new Money(5000),
            months: 3,
        ));
        Assert::same(1, $forecast->paybackMonth());
    }],
    ['the worst cash position is the deepest hole', static function (): void {
        $forecast = simpleWithFixed(new Money(115000));
        Assert::same(-15000, $forecast->months[0]->profit->cents);
        Assert::same(-15000, $forecast->months[0]->cumulative->cents);
        Assert::same(70000, $forecast->months[1]->cumulative->cents);
        Assert::same(-15000, $forecast->worstCumulative()->cents);
    }],
    ['a run that never loses money reports its lowest point', static function (): void {
        $forecast = simple();
        Assert::same(100000, $forecast->worstCumulative()->cents);
    }],
    ['a model rejects a month count that disagrees with the rows', static function (): void {
        try {
            new Assumptions(
                pricePerCustomer: new Money(100),
                monthlyChurnBasisPoints: 0,
                costPerCustomer: new Money(0),
                customersByMonth: [['month' => 1, 'new' => 1]],
                fixedMonthlyCost: new Money(0),
                months: 2,
            );
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['churn above the whole base is rejected', static function (): void {
        try {
            new Assumptions(
                pricePerCustomer: new Money(100),
                monthlyChurnBasisPoints: 10001,
                costPerCustomer: new Money(0),
                customersByMonth: [['month' => 1, 'new' => 1]],
                fixedMonthlyCost: new Money(0),
                months: 1,
            );
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
];
