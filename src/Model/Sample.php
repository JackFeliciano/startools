<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * A worked example a founder can load and then change one number at a time. It
 * is a subscription business with a small fixed cost, so the arithmetic is easy
 * to follow on the first read.
 */
final class Sample
{
    public static function assumptions(): Assumptions
    {
        $rows = [];
        for ($month = 1; $month <= 24; $month++) {
            $rows[] = ['month' => $month, 'new' => 6 + $month];
        }

        return new Assumptions(
            pricePerCustomer: Money::fromFloat(29.0),
            monthlyChurnBasisPoints: 300,
            costPerCustomer: Money::fromFloat(4.5),
            customersByMonth: $rows,
            fixedMonthlyCost: Money::fromFloat(1800.0),
            months: 24,
        );
    }

    public static function book(): Book
    {
        return new Book(self::assumptions(), Stage::REVENUE);
    }

    public static function round(): Round
    {
        return new Round(Money::fromFloat(750000.0), Money::fromFloat(3000000.0));
    }

    public static function brief(): Brief
    {
        return new Brief('Sample subscription business', self::book(), self::round());
    }
}
