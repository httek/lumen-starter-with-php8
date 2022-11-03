<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RequestIdMiddleware
{
    const RequestIdKey = 'X-Request-ID';

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $uuid = $request->headers->get(self::RequestIdKey)) {
            $uuid = Str::uuid()->toString();
            $request->headers->set(self::RequestIdKey, $uuid);
        }

        Log::shareContext([self::RequestIdKey => $uuid]);

        /** @var Response $response */
        $response = $next($request);

        $_SERVER['HTTP_X_REQUEST_ID'] = $uuid;
        $response->headers->set(self::RequestIdKey, $uuid);

        return $response;
    }
}
