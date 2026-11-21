<?php

namespace App\\Services\\Mail;

use Illuminate\\Support\\Facades\\Http;
use RuntimeException;

class MailProviderClient
{
    /**
     * Provision a mailbox through the VPS-level mail provider API.
     *
     * The API token must be scoped server-side to this application's domain.
     *
     * @return array{email:string,id:string|null}
     */
    public function createMailbox(string $localPart, string $password, int $quotaMb): array
    {
        $domain = strtolower(trim((string) config('mail-provider.domain')));
        $baseUrl = rtrim(trim((string) config('mail-provider.api_url')), '/');
        $token = trim((string) config('mail-provider.api_token'));

        if ($domain === '' || $baseUrl === '' || $token === '') {
            throw new RuntimeException('اتصال سرویس مرکزی ایمیل هنوز روی سرور تنظیم نشده است.');
        }

        $scheme = strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME));
        $providerHost = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        $isLoopback = in_array($providerHost, ['127.0.0.1', 'localhost', '::1'], true);

        if ($scheme !== 'https' && ! $isLoopback) {
            throw new RuntimeException('نشانی سرویس مرکزی ایمیل باید در محیط عملیاتی از HTTPS استفاده کند.');
        }

        if (! preg_match('/^[a-z0-9][a-z0-9._+-]{0,63}$/', $localPart)) {
            throw new RuntimeException('نام صندوق ایمیل معتبر نیست.');
        }

        $email = strtolower($localPart).'@'.$domain;
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('آدرس ایمیل انتخاب‌شده معتبر نیست.');
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->withHeaders([
                'Idempotency-Key' => hash('sha256', $domain.'|'.$localPart),
            ])
            ->connectTimeout(5)
            ->timeout(20)
            ->withOptions(['allow_redirects' => false])
            ->post($baseUrl.'/api/v1/mailboxes', [
                'email' => $email,
                'password' => $password,
                'quota_mb' => $quotaMb,
            ]);

        if ($response->status() === 409) {
            throw new RuntimeException('این آدرس ایمیل از قبل در سرویس مرکزی وجود دارد.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('سرویس مرکزی ایمیل نتوانست صندوق را ایجاد کند (HTTP '.$response->status().').');
        }

        $payload = $response->json();
        $createdEmail = strtolower((string) data_get($payload, 'data.email', data_get($payload, 'email', $email)));

        if ($createdEmail !== $email) {
            throw new RuntimeException('پاسخ سرویس ایمیل با دامنه مجاز این فروشگاه مطابقت ندارد.');
        }

        return [
            'email' => $email,
            'id' => data_get($payload, 'data.id', data_get($payload, 'id')),
        ];
    }
}
