<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (! $request->user('admin')?->isAdmin()) {
                // Logging must never change the authorization decision.
                rescue(fn () => Log::warning('Blocked access to an admin-only route', [
                    'request_id' => $request->attributes->get('request_id'),
                    'user_id' => rescue(fn () => $request->user('admin')?->getAuthIdentifier(), report: false),
                    'request_method' => $request->method(),
                    'request_route' => $request->route()?->getName()
                        ?? $request->route()?->uri(),
                ]), report: false);

                abort(403);
            }

            return $next($request);
        } finally {
            // Laravel's auth:admin middleware selects the admin guard for this
            // request. Restore the customer guard before the worker handles
            // another request, preventing cross-request guard leakage.
            Auth::shouldUse('web');
        }
    }
}
