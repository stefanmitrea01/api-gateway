<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\GatewayController;
use App\Http\Services\GatewayService;
use App\Http\Services\LoggingService;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;

class GatewayControllerTest extends TestCase
{
    private GatewayService $gatewayService;
    private LoggingService $loggingService;
    private GatewayController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatewayService = Mockery::mock(GatewayService::class);
        $this->loggingService = Mockery::mock(LoggingService::class);
        $this->controller = new GatewayController($this->gatewayService, $this->loggingService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testHandleSuccess()
    {
        $request = Request::create('/api/v1/test', 'GET');
        $responseData = [
            'status' => 200,
            'body' => ['test' => 'data'],
            'headers' => ['Content-Type' => 'application/json'],
            'success' => true
        ];

        $this->gatewayService->shouldReceive('relayRequest')
            ->with($request)
            ->andReturn($responseData);

        $this->loggingService->shouldReceive('logRequest')
            ->with($request, $responseData, Mockery::type('float'));

        $response = $this->controller->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"test":"data"}', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testHandleWithNonArrayBody()
    {
        $request = Request::create('/api/v1/test', 'GET');
        $responseData = [
            'status' => 200,
            'body' => 'plain text response',
            'headers' => ['Content-Type' => 'text/plain'],
            'success' => true
        ];

        $this->gatewayService->shouldReceive('relayRequest')
            ->with($request)
            ->andReturn($responseData);

        $this->loggingService->shouldReceive('logRequest')
            ->with($request, $responseData, Mockery::type('float'));

        $response = $this->controller->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('plain text response', $response->getContent());
        $this->assertEquals('text/plain', $response->headers->get('Content-Type'));
    }
}
