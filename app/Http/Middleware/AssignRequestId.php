<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = config('examflow.request_id_header', 'X-Request-ID');
        $requestId = $request->header($header) ?: (string) Str::ulid();

        $request->attributes->set('request_id', $requestId);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set($header, $requestId);

        return $response;
    }
}
