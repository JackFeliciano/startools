<?php

declare(strict_types=1);

namespace Startools\App;

use Startools\Model\Book;
use Startools\Model\Brief;
use Startools\Model\Scenario;
use Startools\Model\Stage;

/**
 * The HTML. Every string that reaches the page goes through h(), so a company
 * name with an ampersand in it cannot break the markup.
 */
final class Views
{
    public function __construct(private readonly string $base)
    {
    }

    private function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function header(string $title): string
    {
        $base = $this->h($this->base);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$this->h($title)}</title>
<base href="{$base}">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<main>
HTML;
    }

    public function footer(): string
    {
        return "</main>\n</body>\n</html>\n";
    }

    public function front(string $company, Book $book, ?\Startools\Model\Round $round): string
    {
        $out = $this->header('Startup model');
        $out .= '<h1>' . $this->h($company) . '</h1>' . "\n";
        $out .= '<p class="stage">Stage: ' . $this->h($book->stage) . '</p>' . "\n";

        $out .= '<h2>Three scenarios</h2>' . "\n" . $this->scenarioTable($book);
        $out .= '<h2>The plan, month by month</h2>' . "\n" . $this->monthTable($book->get('plan'));

        if ($round !== null) {
            $out .= '<h2>The round</h2>' . "\n" . $this->roundTable($round);
        }

        $out .= '<h2>What an investor asks at this stage</h2>' . "\n" . $this->questions($book->stage);
        $out .= '<h2>Missing before an investor reads this</h2>' . "\n" . $this->gaps($book->get('plan'));

        return $out . $this->footer();
    }

    private function scenarioTable(Book $book): string
    {
        $out = '<table><thead><tr><th>Scenario</th><th>Customers</th><th>Total profit</th>'
            . '<th>Worst cash</th><th>Break even</th><th>Payback</th></tr></thead><tbody>' . "\n";
        foreach ($book->scenarios as $scenario) {
            $f = $scenario->forecast;
            $out .= '<tr><td>' . $this->h(ucfirst($scenario->name)) . '</td>'
                . '<td class="n">' . $f->customersAtEnd() . '</td>'
                . '<td class="n">' . $f->totalProfit()->format() . '</td>'
                . '<td class="n">' . $f->worstCumulative()->format() . '</td>'
                . '<td class="n">' . ($f->breakEvenMonth() ?? 'none') . '</td>'
                . '<td class="n">' . ($f->paybackMonth() ?? 'none') . '</td></tr>' . "\n";
        }

        return $out . '</tbody></table>' . "\n";
    }

    private function monthTable(Scenario $scenario): string
    {
        $out = '<table><thead><tr><th>Month</th><th>New</th><th>Churned</th><th>Customers</th>'
            . '<th>Revenue</th><th>Variable</th><th>Fixed</th><th>Profit</th><th>Cumulative</th>'
            . '</tr></thead><tbody>' . "\n";
        foreach ($scenario->forecast->months as $month) {
            $out .= '<tr><td class="n">' . $month->month . '</td>'
                . '<td class="n">' . $month->newCustomers . '</td>'
                . '<td class="n">' . $month->churnedCustomers . '</td>'
                . '<td class="n">' . $month->customers . '</td>'
                . '<td class="n">' . $month->revenue->format() . '</td>'
                . '<td class="n">' . $month->variableCost->format() . '</td>'
                . '<td class="n">' . $month->fixedCost->format() . '</td>'
                . '<td class="n">' . $month->profit->format() . '</td>'
                . '<td class="n">' . $month->cumulative->format() . '</td></tr>' . "\n";
        }

        return $out . '</tbody></table>' . "\n";
    }

    private function roundTable(\Startools\Model\Round $round): string
    {
        $rows = $round->toArray();
        $out = '<table><tbody>' . "\n";
        $out .= '<tr><th>Raising</th><td class="n">' . $rows['raising'] . '</td></tr>' . "\n";
        $out .= '<tr><th>Pre money</th><td class="n">' . $rows['pre_money'] . '</td></tr>' . "\n";
        $out .= '<tr><th>Post money</th><td class="n">' . $rows['post_money'] . '</td></tr>' . "\n";
        $out .= '<tr><th>Investor holds</th><td class="n">' . $rows['dilution'] . '%</td></tr>' . "\n";

        return $out . '</tbody></table>' . "\n";
    }

    /** @param list<string> $questions */
    private function questions(string $stage): string
    {
        $out = '<ul>' . "\n";
        foreach (Stage::questions($stage) as $question) {
            $out .= '<li>' . $this->h($question) . '</li>' . "\n";
        }

        return $out . '</ul>' . "\n";
    }

    private function gaps(Scenario $scenario): string
    {
        if ($scenario->readiness->gaps === []) {
            return '<p class="ok">The model answers every question this stage raises.</p>' . "\n";
        }
        $out = '<ul class="gaps">' . "\n";
        foreach ($scenario->readiness->gaps as $gap) {
            $out .= '<li>' . $this->h($gap) . '</li>' . "\n";
        }

        return $out . '</ul>' . "\n";
    }

    public function brief(string $text): string
    {
        return $this->header('Brief') . '<pre>' . $this->h($text) . '</pre>' . $this->footer();
    }

    public function notFound(string $path): string
    {
        return $this->header('Not found') . '<h1>Not found</h1><p>' . $this->h($path) . '</p>' . $this->footer();
    }
}
