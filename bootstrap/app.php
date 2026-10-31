<?php

use App\Console\Commands\OptimizeResponsiveImages;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\ApplyRobotsHeaders;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withCommands([
        OptimizeResponsiveImages::class,
    ])

    ->withMiddleware(function (
        Middleware $middleware
    ) {

        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'customer' => CustomerMiddleware::class,
        ]);

        // Give every request a safe identifier that can be matched to server logs.
        $middleware->prepend(RequestIdMiddleware::class);

        // Enforce the SEO pre-launch gate on every web response, including XML and files.
        $middleware->appendToGroup('web', [ApplyRobotsHeaders::class]);

        // Payment providers call this public endpoint from outside the browser
        // session, so exempt only the gateway callback route from CSRF tokens.
        $middleware->validateCsrfTokens(except: [
            'checkout/payment/callback/*',
        ]);
    })

    ->withExceptions(function (
        Exceptions $exceptions
    ) {

        $exceptions->dontReportDuplicates();

        // Attach operational context to exception logs without recording request
        // bodies, passwords, cookies, or arbitrary headers.
        $exceptions->context(fn (): array => [
            'request_id' => request()->attributes->get('request_id'),
            'request_method' => request()->method(),
            'request_route' => request()->route()?->getName()
                ?? request()->route()?->uri(),
            'user_id' => rescue(fn () => request()->user()?->getAuthIdentifier(), report: false),
        ]);

        /*
        |--------------------------------------------------------------------------
        | JSON / AJAX
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            if (
                ! $request->expectsJson()
                || ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500)
                || $e instanceof AuthorizationException
                || $e instanceof AuthenticationException
                || $e instanceof ValidationException
            ) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() >= 500) {
                Log::error('Unhandled HTTP server error', [
                    'exception' => $e,
                    'request_id' => $request->attributes->get('request_id'),
                    'request_method' => $request->method(),
                    'request_route' => $request->route()?->getName()
                        ?? $request->route()?->uri(),
                    'user_id' => rescue(fn () => $request->user()?->getAuthIdentifier(), report: false),
                ]);
            }

            $requestId = $request->attributes->get('request_id')
                ?? (string) \Illuminate\Support\Str::uuid();
            $request->attributes->set('request_id', $requestId);
            $message = config('app.debug') && app()->environment(['local', 'testing'])
                ? $e->getMessage()
                : 'در پردازش درخواست مشکلی پیش آمد. لطفاً دوباره تلاش کنید.';
            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            return response()->json([
                'success' => false,
                'message' => $message,
                'request_id' => $requestId,
            ], $status, ['X-Request-ID' => $requestId]);
        });


        /*
        |--------------------------------------------------------------------------
        | HTTP Error Pages
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (
            HttpExceptionInterface $e,
            Request $request
        ) {
            if ($request->expectsJson()) {
                return null;
            }

            $view = match ($e->getStatusCode()) {
                403 => 'errors.403',
                404 => 'errors.404',
                419 => 'errors.419',
                422 => 'errors.422',
                default => null,
            };

            if ($view === null) {
                return null;
            }

            return response()->view(
                $view,
                [],
                $e->getStatusCode()
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Production Fallback
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            // Let Laravel retain its native redirect/4xx behavior for expected
            // framework exceptions. The production fallback is for unexpected
            // server failures, not authentication, validation, or missing rows.
            if (
                $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof ValidationException
                || $e instanceof ModelNotFoundException
            ) {
                return null;
            }

            if (config('app.debug') && app()->environment(['local', 'testing'])) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($status < 500) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() >= 500) {
                Log::error('Unhandled HTTP server error', [
                    'exception' => $e,
                    'request_id' => $request->attributes->get('request_id'),
                    'request_method' => $request->method(),
                    'request_route' => $request->route()?->getName()
                        ?? $request->route()?->uri(),
                    'user_id' => $request->user()?->getAuthIdentifier(),
                ]);
            }

            $requestId = $request->attributes->get('request_id')
                ?? (string) \Illuminate\Support\Str::uuid();
            $request->attributes->set('request_id', $requestId);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'در پردازش درخواست مشکلی پیش آمد. لطفاً دوباره تلاش کنید.',
                    'request_id' => $requestId,
                ], $status, ['X-Request-ID' => $requestId]);
            }

            return response()->view(
                'errors.500',
                ['requestId' => $requestId, 'errorCode' => $status],
                $status,
                ['X-Request-ID' => $requestId]
            );
        });
    })
    ->create();
