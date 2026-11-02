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

        $adminPath = trim((string) config('farzin.admin_path', 'admin'), '/') ?: 'admin';

        $privateRoute = $request->is(
            $adminPath, $adminPath . '/*',
            'account', 'account/*',
            'cart', 'cart/*',
            'checkout', 'checkout/*',
            'login', 'register', 'forgot-password', 'reset-password/*', 'logout'
        );

        $csrfSensitiveRoute = $request->is(
            $adminPath, $adminPath . '/*',
            'account', 'account/*',
            'contact', 'product/*',
            'cart', 'cart/*',
            'checkout', 'checkout/*',
            'login', 'register', 'logout'
        );

        // Never let a browser, reverse proxy, or CDN reuse HTML containing a
        // session-bound CSRF token. Admin and storefront form pages stay private.
        $authenticatedResponse = $request->user('web') !== null
            || $request->user('admin') !== null;

        if ($csrfSensitiveRoute || $authenticatedResponse) {
            $response->headers->set('Cache-Control', 'private, no-store, no-cache, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

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
