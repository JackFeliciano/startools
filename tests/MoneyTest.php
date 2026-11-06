<?php

declare(strict_types=1);

use Startools\Model\Money;
use Startools\Tests\Assert;

return [
    ['cents survive a float that would round wrong', static function (): void {
        $money = Money::fromFloat(29.99);
        Assert::same(2999, $money->cents);
    }],
    ['multiplication rounds to the nearest cent', static function (): void {
        $money = Money::fromFloat(29.99)->times(3);
        Assert::same(8997, $money->cents);
    }],
    ['a formatting separates thousands', static function (): void {
        Assert::same('1,234.50', Money::fromFloat(1234.5)->format());
    }],
    ['a negative amount is visible as one', static function (): void {
        Assert::true(Money::fromFloat(-1)->isNegative());
        Assert::false(Money::fromFloat(1)->isNegative());
    }],
    ['minus can go below zero', static function (): void {
        Assert::same(-500, Money::fromFloat(5)->minus(Money::fromFloat(10))->cents);
    }],
];
