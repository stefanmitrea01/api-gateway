<?php

// app/Services/LoggingService.php
namespace App\Http\Services;

use App\Models\RequestLogModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoggingService
{
    /**
     * @param  Request  $request
     * @param  array  $responseData
     * @param  float  $startTime
     * @return void
     */
    public function logRequest(Request $request, array $responseData, float $startTime): void
    {
        if (!config('gateway.logging.enabled')) {
            return;
        }

        $responseTime = (microtime(true) - $startTime) * 1000;

        DB::transaction(function () use (
            $request,
            $responseData,
            $responseTime
        ) {
             RequestLogModel::create([
                'method' => $request->method(),
                'path' => $request->path(),
                'request_headers' => $request->headers->all(),
                'request_body' => $request->all(),
                'status_code' => $responseData['status'],
                'response_headers' => $responseData['headers'],
                'response_body' => json_decode($responseData['body'], true),
                'latency_ms' => $responseTime,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        });

    }
}
