<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param $request
     * @param  Throwable  $exception
     * @return JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => method_exists($exception, 'errors') ? $exception->errors() : null,
            ], $this->getStatusCode($exception));
        }

        return parent::render($request, $exception);
    }

    protected function getStatusCode($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return $exception instanceof ValidationException
            ? Response::HTTP_UNPROCESSABLE_ENTITY
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
