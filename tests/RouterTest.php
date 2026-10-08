<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Http\HttpRequest;
use Nexo\Http\HttpResponse;
use Nexo\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testRouteWithParams(): void
    {
        $router = new Router();
        $router->get('/v1/content/:id', fn (HttpRequest $req) => HttpResponse::json(['id' => $req->params['id']]));

        $response = $router->dispatch(new HttpRequest('GET', '/v1/content/abc'));
        self::assertSame(200, $response->status);
        self::assertSame(['id' => 'abc'], $response->data);
    }

    public function testMethodMismatchReturns404(): void
    {
        $router = new Router();
        $router->post('/v1/content', fn () => ['ok' => true]);

        $response = $router->dispatch(new HttpRequest('GET', '/v1/content'));
        self::assertSame(404, $response->status);
        self::assertSame('not_found', $response->data['error']['code']);
    }

    public function testHandlerExceptionReturns500(): void
    {
        $router = new Router();
        $router->get('/boom', fn () => throw new \RuntimeException('kaput'));

        $response = $router->dispatch(new HttpRequest('GET', '/boom'));
        self::assertSame(500, $response->status);
        self::assertSame('internal_error', $response->data['error']['code']);
    }
}
