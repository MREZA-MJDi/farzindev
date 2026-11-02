<?php

namespace App\Support;

use Dotenv\Dotenv;
use RuntimeException;
use Throwable;

final class AdminEnvironment
{
    /**
     * Read the administrator provisioning values from the current process
     * environment or .env file, even when Laravel's config cache is enabled.
     *
     * The real password is returned only to the calling command and must never
     * be written to output or logs.
     *
     * @return array{name: string, email: string, password: string}
     */
    public static function credentials(): array
    {
        if (app()->environment('testing')) {
            return [
                'name' => trim((string) config('farzin.admin.name', 'Farzin Admin')),
                'email' => mb_strtolower(trim((string) config('farzin.admin.email', ''))),
                'password' => (string) config('farzin.admin.password', ''),
            ];
        }

        $parsed = [];
        $path = base_path('.env');

        if (! app()->environment('testing') && is_file($path)) {
            if (! is_readable($path)) {
                throw new RuntimeException('The application environment file cannot be read.');
            }

            try {
                $contents = file_get_contents($path);

                if ($contents === false) {
                    throw new RuntimeException('The application environment file cannot be read.');
                }

                $parsed = Dotenv::parse($contents);
            } catch (Throwable) {
                throw new RuntimeException('The application environment file could not be parsed. Check its dotenv syntax.');
            }
        }

        return [
            'name' => trim((string) self::value('ADMIN_NAME', $parsed, config('farzin.admin.name', 'Farzin Admin'))),
            'email' => mb_strtolower(trim((string) self::value('ADMIN_EMAIL', $parsed, config('farzin.admin.email', '')))),
            'password' => (string) self::value('ADMIN_PASSWORD', $parsed, config('farzin.admin.password', '')),
        ];
    }

    private static function value(string $key, array $parsed, mixed $fallback): mixed
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $processValue = getenv($key);

        if ($processValue !== false) {
            return $processValue;
        }

        return $parsed[$key] ?? $fallback;
    }
}
