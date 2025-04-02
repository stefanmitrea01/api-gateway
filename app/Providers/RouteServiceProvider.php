<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $userOrIp = $request->user()?->id ?: $request->ip();

            // Apply gateway-specific rate limiting if enabled
            if (config('gateway.throttle.enabled')) {
                $key = 'gateway:' . $userOrIp;
                $limit = config('gateway.throttle.limit', 60); // Default to 60 if not set
                $period = config('gateway.throttle.period', 60); // Default to 60 sec

                if (RateLimiter::tooManyAttempts($key, $limit)) {
                    abort(429, json_encode(['error' => 'Too Many Requests']));
                }
                RateLimiter::hit($key, $period);
            }

            return Limit::perMinute(60)->by($userOrIp);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
