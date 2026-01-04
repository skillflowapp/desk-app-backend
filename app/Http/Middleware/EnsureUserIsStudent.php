<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isStudent()) {
            return response()->json([
                'message' => 'Only students can access this resource',
            ], 403);
        }

        return $next($request);
    }
}
