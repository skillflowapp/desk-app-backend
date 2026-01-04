<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class LogApiActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log API requests
        if ($request->is('api/*')) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    private function logActivity(Request $request, Response $response): void
    {
        try {
            // Don't log auth endpoints to avoid noise
            if ($request->is('api/auth/*')) {
                return;
            }

            $status = $response->getStatusCode();
            $isError = $status >= 400;

            AuditLog::create([
                'user_id' => auth('sanctum')->id(),
                'action' => $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $isError ? 'failed' : 'success',
                'meta_data' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'status_code' => $status,
                    'query_params' => $request->query(),
                ],
            ]);
        } catch (\Exception $e) {
            // Don't break the request if logging fails
            \Illuminate\Support\Facades\Log::error('Audit log failed: ' . $e->getMessage());
        }
    }
}
