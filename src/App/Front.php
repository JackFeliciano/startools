<?php

declare(strict_types=1);

namespace Startools\App;

use Startools\Model\Sample;

/**
 * The front controller. Routes, then renders. The health route answers with the
 * month count so a probe can tell a working process from a live one.
 */
final class Front
{
    public function __construct(private readonly string $base)
    {
    }

    public function run(): void
    {
        $router = new Router($this->base);
        $views = new Views($this->base);
        $brief = Sample::brief();

        $router->get('/', static fn (): array => [200, [
            'html' => $views->front('Sample subscription business', $brief->book, $brief->round),
        ]]);
        $router->get('/brief', static fn (): array => [200, ['html' => $views->brief($brief->text())]]);
        $router->get('/health', static fn (): array => [200, ['json' => [
            'ok' => true,
            'months' => $brief->book->base->months,
        ]]]);

        [$status, $body] = $router->handle(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
        );

        http_response_code($status);
        if (isset($body['json'])) {
            header('Content-Type: application/json');
            echo json_encode($body['json'], JSON_UNESCAPED_SLASHES), "\n";

            return;
        }
        if (isset($body['html'])) {
            header('Content-Type: text/html; charset=utf-8');
            echo $body['html'];

            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $views->notFound((string) ($body['path'] ?? '/'));
    }
}
