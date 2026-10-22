<?php

namespace App\\Http\\Middleware;

use Closure;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Str;
use Symfony\\Component\\HttpFoundation\\Response;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate the ID server-side; never trust a caller-supplied header as a log key.
        $requestId = (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
