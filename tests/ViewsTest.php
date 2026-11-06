<?php

declare(strict_types=1);

use Startools\App\Views;
use Startools\Model\Sample;
use Startools\Tests\Assert;

return [
    ['the front page keeps the line breaks literal', static function (): void {
        $html = (new Views('/'))->front('Sample', Sample::book(), null);
        Assert::same(0, substr_count($html, '\\n'));
        Assert::true(substr_count($html, "\n") > 10);
    }],
];
