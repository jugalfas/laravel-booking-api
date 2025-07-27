<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

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
        $this->authenticate($request, $guards);

        return $next($request);
    }
}
