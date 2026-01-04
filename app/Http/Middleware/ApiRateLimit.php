<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    public function __construct(
        private RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Only apply rate limiting to API routes
        if (!$request->is('api/*')) {
            return $next($request);
        }

        // Get user if authenticated
        $user = $request->user('sanctum');
        
        if (!$user) {
            return $next($request);
        }

        // Rate limits per endpoint
        $limits = [
            'api/exams/enter' => 5, // 5 attempts per minute
            'api/auth/login' => 10, // 10 login attempts per minute
            'api/sync' => 20, // 20 syncs per minute
            'default' => 100, // 100 requests per minute
        ];

        $limit = $this->getLimit($request->path(), $limits);
        $key = $this->getKey($request, $user->id);

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            return response()->json([
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, 60); // 60 second window

        return $next($request);
    }

    private function getKey(Request $request, int $userId): string
    {
        $path = str_replace(['/', '.'], '_', $request->path());
        return "rate_limit:{$path}:{$userId}:{$request->ip()}";
    }

    private function getLimit(string $path, array $limits): int
    {
        foreach ($limits as $route => $limit) {
            if ($route !== 'default' && str_starts_with($path, $route)) {
                return $limit;
            }
        }
        return $limits['default'];
    }
}
