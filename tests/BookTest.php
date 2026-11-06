<?php

declare(strict_types=1);

use Startools\Model\Sample;
use Startools\Tests\Assert;

return [
    ['three scenarios come out of one set of assumptions', static function (): void {
        $book = Sample::book();
        Assert::count(3, $book->scenarios);
        Assert::same('slow', $book->scenarios[0]->name);
        Assert::same('fast', $book->scenarios[2]->name);
    }],
    ['the slow scenario ends with fewer customers than the fast one', static function (): void {
        $book = Sample::book();
        Assert::true($book->get('slow')->forecast->customersAtEnd()
            < $book->get('fast')->forecast->customersAtEnd());
    }],
    ['the plan scenario matches the base assumptions', static function (): void {
        $book = Sample::book();
        Assert::same(
            $book->base->customersByMonth[0]['new'],
            $book->get('plan')->forecast->months[0]->newCustomers,
        );
    }],
    ['asking for a scenario by an unknown name raises', static function (): void {
        try {
            Sample::book()->get('sideways');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['the brief states the stage and the scenarios', static function (): void {
        $text = Sample::brief()->text();
        Assert::contains('Stage: revenue', $text);
        Assert::contains('Plan scenario', $text);
        Assert::contains('Missing before an investor reads this', $text);
    }],
];
