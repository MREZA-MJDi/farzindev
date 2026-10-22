<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! $request->user()?->isAdmin()) {
            // Log failures must never change the authorization decision.
            rescue(fn () => Log::warning('Blocked access to an admin-only route', [
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => rescue(fn () => $request->user()?->getAuthIdentifier(), report: false),
                'request_method' => $request->method(),
                'request_route' => $request->route()?->getName()
                    ?? $request->route()?->uri(),
            ]), report: false);

            abort(403);
        }

        return $next($request);
    }
}
