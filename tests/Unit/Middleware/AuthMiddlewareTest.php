<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthMiddlewareTest extends TestCase
{
    private AuthMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AuthMiddleware();
    }

    public function testHandleWithAuthDisabled()
    {
        config(['gateway.auth.enabled' => false]);

        $request = Request::create('/test', 'GET');
        $next = function ($req) {
            return new Response('OK', 200);
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function testHandleWithMockAuthSuccess()
    {
        config([
            'gateway.auth.enabled' => true,
            'gateway.auth.mock' => true
        ]);
        putenv('MOCK_JWT_TOKEN=test-token');

        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer test-token'
        ]);

        $next = function ($req) {
            return new Response('OK', 200);
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function testHandleWithMockAuthMissingToken()
    {
        config([
            'gateway.auth.enabled' => true,
            'gateway.auth.mock' => true
        ]);

        $request = Request::create('/test', 'GET');
        $next = function ($req) {
            return new Response('OK', 200);
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized', $responseData['error']);
        $this->assertEquals('Invalid or missing mock JWT token', $responseData['message']);
    }

    public function testHandleWithMockAuthInvalidToken()
    {
        config([
            'gateway.auth.enabled' => true,
            'gateway.auth.mock' => true
        ]);
        putenv('MOCK_JWT_TOKEN=correct-token');

        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer wrong-token'
        ]);

        $next = function ($req) {
            return new Response('OK', 200);
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized', $responseData['error']);
        $this->assertEquals('Invalid or missing mock JWT token', $responseData['message']);
    }
}
