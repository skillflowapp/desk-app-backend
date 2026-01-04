<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isTeacher()) {
            return response()->json([
                'message' => 'Only teachers can access this resource',
            ], 403);
        }

        return $next($request);
    }
}
