<?php

namespace Auth;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RedirectIfAuthenticatedMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
    }

    public function testItCanGenerateDefinitionViaStaticMethod()
    {
        $signature = RedirectIfAuthenticated::using('foo');
        $this->assertSame('Illuminate\Auth\Middleware\RedirectIfAuthenticated:foo', $signature);

        $signature = RedirectIfAuthenticated::using('foo', 'bar');
        $this->assertSame('Illuminate\Auth\Middleware\RedirectIfAuthenticated:foo,bar', $signature);

        $signature = RedirectIfAuthenticated::using('foo', 'bar', 'baz');
        $this->assertSame('Illuminate\Auth\Middleware\RedirectIfAuthenticated:foo,bar,baz', $signature);
    }

    public function testJsonRequestThrows403WhenAuthenticated()
    {
        $guard = $this->createMock(Guard::class);
        $guard->method('check')->willReturn(true);

        $auth = $this->createMock(AuthFactory::class);
        $auth->method('guard')->willReturn($guard);

        Auth::swap($auth);

        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $middleware = new RedirectIfAuthenticated;

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Already authenticated.');

        $middleware->handle($request, function () {
        });
    }

    public function testUnauthenticatedRequestPassesThrough()
    {
        $guard = $this->createMock(Guard::class);
        $guard->method('check')->willReturn(false);

        $auth = $this->createMock(AuthFactory::class);
        $auth->method('guard')->willReturn($guard);

        Auth::swap($auth);

        $request = Request::create('/test', 'GET');

        $middleware = new RedirectIfAuthenticated;

        $response = $middleware->handle($request, function ($req) {
            return new \Symfony\Component\HttpFoundation\Response('passed');
        });

        $this->assertSame('passed', $response->getContent());
    }
}
