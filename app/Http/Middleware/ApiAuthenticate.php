<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'message' => 'Unauthenticated.',
        ], 401));
    }

    public function handle($request, Closure $next, ...$guards): Response
    {
        $user = Auth::guard('sanctum')->user();
        if (isset($guards[0]) && (!$user || !$user->hasRole($guards[0]))) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }
        
        return $next($request);
    }
}
