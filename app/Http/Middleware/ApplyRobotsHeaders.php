<?php

namespace App\Http\Middleware;

use App\Services\Seo\SeoSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyRobotsHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $privateRoute = $request->is(
            'admin', 'admin/*',
            'account', 'account/*',
            'cart', 'cart/*',
            'checkout', 'checkout/*',
            'login', 'register', 'forgot-password', 'reset-password/*', 'logout'
        );

        // Private dashboards and customer-specific responses remain excluded
        // even after the public SEO release switch is intentionally turned off.
        if ($privateRoute) {
            $response->headers->set(
                'X-Robots-Tag',
                'noindex, nofollow, noarchive, nosnippet, noimageindex'
            );

            return $response;
        }

        // The service combines the global development lock with global robots
        // settings and filtered-shop rules. Apply it to HTML, XML, JSON and
        // controller-served files so non-HTML endpoints cannot bypass noindex.
        $robots = app(SeoSettings::class)->robotsMeta();

        if (str_contains($robots, 'noindex') || str_contains($robots, 'nofollow')) {
            $response->headers->set('X-Robots-Tag', $robots);
        } else {
            $response->headers->remove('X-Robots-Tag');
        }

        return $response;
    }
}
