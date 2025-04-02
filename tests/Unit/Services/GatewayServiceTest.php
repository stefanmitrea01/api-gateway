<?php

namespace Tests\Unit\Services;

use App\Http\Services\GatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayServiceTest extends TestCase
{
    private GatewayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GatewayService();

        // Mock the HTTP client
        Http::fake([
            'jsonplaceholder.typicode.com/posts' => Http::response(['test' => 'data'], 200, ['Content-Type' => 'application/json']),
            'nonexistent-service.com/*' => Http::response([], 502),
        ]);

        // Configure test routes
        config(['gateway.routes' => [
            'api/v1/*' => 'https://jsonplaceholder.typicode.com/posts',
            'api/v2/*' => 'https://nonexistent-service.com/api'
        ]]);
    }

    public function testRelayRequestSuccess()
    {
        $request = Request::create('/api/v1/test', 'GET');

        $response = $this->service->relayRequest($request);

        $this->assertTrue($response['success']);
        $this->assertEquals(200, $response['status']);
        $this->assertEquals(['test' => 'data'], json_decode($response['body'], true));
        $this->assertEquals(['Content-Type' => ['application/json']], $response['headers']);
    }

    public function testRelayRequestRouteNotFound()
    {
        $request = Request::create('/unknown/route', 'GET');

        $response = $this->service->relayRequest($request);

        $this->assertFalse($response['success']);
        $this->assertEquals(404, $response['status']);
        $this->assertEquals(['error' => 'Route not found'], $response['body']);
    }

    public function testGetDestinationFound()
    {
        $result = $this->service->getDestination('api/v1/test');
        $this->assertEquals('https://jsonplaceholder.typicode.com/posts', $result);
    }

    public function testGetDestinationNotFound()
    {
        $result = $this->service->getDestination('unknown/route');
        $this->assertNull($result);
    }
}
