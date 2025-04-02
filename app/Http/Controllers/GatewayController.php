<?php

namespace App\Http\Controllers;

use App\Http\Services\GatewayService;
use App\Http\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class GatewayController extends BaseController
{
    protected GatewayService $gatewayService;
    protected LoggingService $loggingService;

    /**
     * @param  GatewayService  $gatewayService
     * @param  LoggingService  $loggingService
     */
    public function __construct(GatewayService $gatewayService, LoggingService $loggingService)
    {
        $this->gatewayService = $gatewayService;
        $this->loggingService = $loggingService;
    }

    /**
     * @param  Request  $request
     * @return Response
     */
    public function handle(Request $request): Response
    {

        $startTime = microtime(true);
        $responseData = $this->gatewayService->relayRequest($request);

        $this->loggingService->logRequest($request, $responseData, $startTime);

        return response(
            is_array($responseData['body']) ? json_encode($responseData['body']) : $responseData['body'],
            $responseData['status']
        )->withHeaders($responseData['headers']);
    }
}
