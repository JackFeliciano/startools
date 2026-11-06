<?php

declare(strict_types=1);

use Startools\App\Router;
use Startools\Tests\Assert;

return [
    ['a route answers with its handler', static function (): void {
        $router = new Router('/startools/');
        $router->get('/', static fn (): array => [200, ['html' => 'front']]);
        [$status, $body] = $router->handle('GET', '/startools/');
        Assert::same(200, $status);
        Assert::same('front', $body['html']);
    }],
    ['the prefix is stripped before matching', static function (): void {
        $router = new Router('/startools/');
        $router->get('/brief', static fn (): array => [200, ['html' => 'brief']]);
        [$status, $body] = $router->handle('GET', '/startools/brief?x=1');
        Assert::same(200, $status);
        Assert::same('brief', $body['html']);
    }],
    ['an unknown path is a 404 that names it', static function (): void {
        $router = new Router('/');
        [$status, $body] = $router->handle('GET', '/nowhere');
        Assert::same(404, $status);
        Assert::same('/nowhere', $body['path']);
    }],
    ['a trailing slash and none match the same route', static function (): void {
        $router = new Router('/');
        $router->get('/brief', static fn (): array => [200, ['html' => 'ok']]);
        Assert::same(200, $router->handle('GET', '/brief')[0]);
        Assert::same(200, $router->handle('GET', '/brief/')[0]);
    }],
];
