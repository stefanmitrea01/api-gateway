<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayService
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function relayRequest(Request $request): array
    {
        $destination = $this->getDestination($request->path());

        if (!$destination) {
            return [
                'status' => 404,
                'body' => ['error' => 'Route not found'],
                'headers' => [],
                'success' => false
            ];
        }

        try {
            $response =  Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
                ->withBody($request->getContent(),$request->header('Content-Type', 'application/json'))
                ->{$request->method()}($destination);

            return [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => 502,
                'body' => ['error' => 'Bad Gateway', 'message' => $e->getMessage()],
                'headers' => [],
                'success' => false
            ];
        }
    }

    /**
     * @param  string  $path
     * @return string|null
     */
    public function getDestination(string $path): string|null
    {
        foreach (config('gateway.routes') as $pattern => $destination) {
            if (fnmatch($pattern, $path)) {
                return $destination;
            }
        }

        return null;
    }
}
