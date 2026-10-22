<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! $request->user()?->isAdmin()) {
            Log::warning('Blocked access to an admin-only route', [
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $request->user()?->getAuthIdentifier(),
                'request_method' => $request->method(),
                'request_route' => $request->route()?->getName()
                    ?? $request->route()?->uri(),
            ]);

            abort(403);
        }

        return $next($request);
    }
}
