<?php

declare(strict_types=1);

namespace Startools\Model;

/**
 * The stages a founder moves through, and what an investor reads at each one.
 * The questions are the ones a partner asks before he opens the deck, so a
 * founder sees the whole ladder before he fills in a single number.
 */
final class Stage
{
    public const IDEA = 'idea';
    public const PROTOTYPE = 'prototype';
    public const REVENUE = 'revenue';
    public const GROWTH = 'growth';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::IDEA, self::PROTOTYPE, self::REVENUE, self::GROWTH];
    }

    /** @return list<string> */
    public static function questions(string $stage): array
    {
        return match ($stage) {
            self::IDEA => [
                'Who has this problem today, and what do they pay to solve it?',
                'Why does this team see the problem before anyone else?',
                'What did you build or sell before this?',
            ],
            self::PROTOTYPE => [
                'Who used the prototype, and what did they do with it?',
                'What did you learn that changed the design?',
                'What does the next version cost to reach a paying customer?',
            ],
            self::REVENUE => [
                'Who pays, how much, and how often?',
                'What does it cost to win one customer, and what does he return?',
                'Which customers renew, and which ones leave?',
            ],
            self::GROWTH => [
                'What breaks first if the volume doubles?',
                'Which channel carries the growth, and what does it cost per customer?',
                'What does the money buy, in months and in customers?',
            ],
            default => throw new \InvalidArgumentException('unknown stage ' . $stage),
        };
    }

    /** The figures an investor expects to read with the projection. */
    /** @return list<string> */
    public static function measures(): array
    {
        return [
            'customers',
            'revenue',
            'gross margin',
            'customer acquisition cost',
            'lifetime value',
            'churn',
            'runway',
            'month of payback',
        ];
    }
}
