<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class ResolveCloudflareClientIp
{
    /**
     * Cloudflare's published proxy ranges.
     *
     * Keep this list synchronized with https://www.cloudflare.com/ips/
     * The CF-Connecting-IP header is trusted only when REMOTE_ADDR belongs
     * to one of these ranges, so direct clients cannot spoof their IP.
     *
     * @var array<int, string>
     */
    private const CLOUDFLARE_RANGES = [
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '108.162.192.0/18',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '162.158.0.0/15',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '188.114.96.0/20',
        '190.93.240.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $proxyIp = (string) $request->server->get('REMOTE_ADDR', '');
        $visitorIp = trim((string) $request->headers->get('CF-Connecting-IP', ''));

        if (
            $proxyIp !== ''
            && filter_var($visitorIp, FILTER_VALIDATE_IP) !== false
            && IpUtils::checkIp($proxyIp, self::CLOUDFLARE_RANGES)
        ) {
            // Symfony/Laravel resolve Request::ip() from the server bag.
            // This runs globally before any route-level throttle middleware.
            $request->server->set('REMOTE_ADDR', $visitorIp);
        }

        return $next($request);
    }
}
