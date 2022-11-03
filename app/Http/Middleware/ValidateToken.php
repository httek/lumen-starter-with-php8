<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\TokenService;
use Illuminate\Http\Request;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->bearerToken() ?: $request->input('token')) {
            $validated = TokenService::validate($token);
            if ($validated->fails()) {
                return $validated->isExpired()
                    ? error($validated->getMessage(), 4011)
                    : error('Unauthorized', 4010);
            }
        }

        return $next($request);
    }
}
