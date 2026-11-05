<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifyTurnstile
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function handle(Request $request, Closure $next): Response
    {
        $siteKey = config('services.turnstile.site_key');
        $secret = config('services.turnstile.secret');

        if (! filled($siteKey) || ! filled($secret)) {
            if (app()->environment(['local', 'testing'])) {
                return $next($request);
            }

            Log::critical('Cloudflare Turnstile is not configured; protected request blocked.', [
                'route' => $request->route()?->getName(),
            ]);

            return $this->reject(
                $request,
                'سامانه ضدربات هنوز پیکربندی نشده است. لطفاً با پشتیبانی تماس بگیرید.',
                503
            );
        }

        $expectedAction = match ($request->route()?->getName()) {
            'login.store' => 'login',
            'register.store' => 'signup',
            'admin.login.store' => 'admin-login',
            'checkout.place' => 'checkout',
            'contact.store' => 'contact',
            default => null,
        };

        if ($expectedAction === null) {
            Log::error('Turnstile middleware attached to unsupported route.', [
                'route' => $request->route()?->getName(),
            ]);

            return $this->reject($request, 'درخواست قابل تأیید نیست. صفحه را تازه‌سازی کنید.');
        }

        $token = $request->input('cf-turnstile-response');

        if (! is_string($token) || trim($token) === '' || strlen($token) > 2048) {
            return $this->reject(
                $request,
                'لطفاً تأیید کنید که ربات نیستید و دوباره تلاش کنید.'
            );
        }

        $hostnames = config('services.turnstile.hostnames', []);

        if (! is_array($hostnames) || $hostnames === []) {
            if (app()->environment(['local', 'testing'])) {
                $hostnames = [$request->getHost()];
            } else {
                Log::critical('Turnstile hostname allowlist is empty.', [
                    'route' => $request->route()?->getName(),
                ]);

                return $this->reject(
                    $request,
                    'تنظیم دامنه‌های مجاز سامانه ضدربات کامل نیست.',
                    503
                );
            }
        }

        try {
            $verification = Http::asForm()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(self::VERIFY_URL, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (Throwable $exception) {
            Log::warning('Cloudflare Turnstile verification request failed.', [
                'exception' => $exception::class,
                'route' => $request->route()?->getName(),
            ]);

            return $this->reject(
                $request,
                'ارتباط با سامانه ضدربات برقرار نشد. لطفاً دوباره تلاش کنید.'
            );
        }

        if (! $verification->successful() || $verification->json('success') !== true) {
            Log::notice('Cloudflare Turnstile rejected submitted token.', [
                'route' => $request->route()?->getName(),
                'error_codes' => $verification->json('error-codes', []),
            ]);

            return $this->reject(
                $request,
                'تأیید ضدربات ناموفق بود. لطفاً کپچا را دوباره تکمیل کنید.'
            );
        }

        $hostname = strtolower((string) $verification->json('hostname', ''));
        $action = (string) $verification->json('action', '');
        $normalizedHostnames = array_map(
            static fn ($host): string => strtolower(trim((string) $host)),
            $hostnames
        );

        if (
            $hostname === ''
            || ! in_array($hostname, $normalizedHostnames, true)
            || ! hash_equals($expectedAction, $action)
        ) {
            Log::notice('Turnstile hostname or action mismatch.', [
                'route' => $request->route()?->getName(),
                'hostname' => $hostname,
                'action' => $action,
            ]);

            return $this->reject(
                $request,
                'تأیید ضدربات معتبر نبود. صفحه را تازه‌سازی و دوباره تلاش کنید.'
            );
        }

        return $next($request);
    }

    protected function reject(
        Request $request,
        string $message,
        int $status = 422
    ): Response {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['captcha' => [$message]],
            ], $status);
        }

        return redirect()
            ->back()
            ->withInput($request->except('cf-turnstile-response'))
            ->withErrors(['captcha' => $message]);
    }
}
