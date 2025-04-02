<?php

namespace Tests\Unit\Services;

use App\Http\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;

class LoggingServiceTest extends TestCase
{
    private LoggingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoggingService();
    }

    public function testLogRequestWhenLoggingDisabled()
    {
        config(['gateway.logging.enabled' => false]);

        $request = Request::create('/test', 'GET');
        $responseData = ['status' => 200, 'body' => '{}', 'headers' => []];

        DB::shouldReceive('transaction')->never();

        $this->service->logRequest($request, $responseData, microtime(true));
    }

    public function testLogRequestSuccess()
    {
        config(['gateway.logging.enabled' => true]);

        $request = Request::create('/test', 'POST', ['param' => 'value'], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_USER_AGENT' => 'TestAgent'
        ]);

        $responseData = [
            'status' => 200,
            'body' => '{"response":"data"}',
            'headers' => ['Content-Type' => 'application/json']
        ];

        $mockModel = Mockery::mock('overload:App\Models\RequestLogModel');
        $mockModel->shouldReceive('create')
            ->once()
            ->with([
                'method' => 'POST',
                'path' => '/test',
                'request_headers' => ['accept' => ['application/json'], 'user-agent' => ['TestAgent']],
                'request_body' => ['param' => 'value'],
                'status_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
                'response_body' => ['response' => 'data'],
                'latency_ms' => Mockery::type('float'),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'TestAgent'
            ]);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'));

        $this->service->logRequest($request, $responseData, microtime(true));
    }
}
