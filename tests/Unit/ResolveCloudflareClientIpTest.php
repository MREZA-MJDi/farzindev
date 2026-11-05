<?php

namespace Tests\Unit;

use App\Http\Middleware\ResolveCloudflareClientIp;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ResolveCloudflareClientIpTest extends TestCase
{
    public function test_it_uses_cf_connecting_ip_when_request_comes_from_cloudflare_ipv4(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '173.245.48.10',
            'HTTP_CF_CONNECTING_IP' => '198.51.100.23',
        ]);

        (new ResolveCloudflareClientIp())->handle(
            $request,
            static fn (Request $request): Response => new Response()
        );

        self::assertSame('198.51.100.23', $request->ip());
    }

    public function test_it_uses_cf_connecting_ip_when_request_comes_from_cloudflare_ipv6(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '2606:4700::1',
            'HTTP_CF_CONNECTING_IP' => '2001:db8::1234',
        ]);

        (new ResolveCloudflareClientIp())->handle(
            $request,
            static fn (Request $request): Response => new Response()
        );

        self::assertSame('2001:db8::1234', $request->ip());
    }

    public function test_it_does_not_trust_cf_connecting_ip_from_an_untrusted_peer(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '198.51.100.10',
            'HTTP_CF_CONNECTING_IP' => '203.0.113.50',
        ]);

        (new ResolveCloudflareClientIp())->handle(
            $request,
            static fn (Request $request): Response => new Response()
        );

        self::assertSame('198.51.100.10', $request->ip());
    }

    public function test_it_ignores_an_invalid_cf_connecting_ip_header(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '173.245.48.10',
            'HTTP_CF_CONNECTING_IP' => 'not-an-ip',
        ]);

        (new ResolveCloudflareClientIp())->handle(
            $request,
            static fn (Request $request): Response => new Response()
        );

        self::assertSame('173.245.48.10', $request->ip());
    }
}
