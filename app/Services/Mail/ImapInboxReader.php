<?php

namespace App\Services\Mail;

use App\Models\SmtpSetting;
use RuntimeException;
use Throwable;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\IMAP;

class ImapInboxReader
{
    /**
     * Read a small recent window without marking messages as read or storing bodies.
     *
     * @return array<int, array{from:string,subject:string,date:string,code:?string}>
     */
    public function recentVerificationMessages(SmtpSetting $settings, int $limit = 20): array
    {
        $host = trim((string) ($settings->imap_host ?: $settings->host));
        $username = trim((string) ($settings->imap_username ?: $settings->username));
        $password = $settings->imap_password ?: $settings->password;

        if ($host === '' || $username === '' || blank($password)) {
            throw new RuntimeException('اطلاعات اتصال IMAP کامل نیست؛ میزبان، نام کاربری و رمز صندوق را وارد کنید.');
        }

        $encryption = match ($settings->imap_encryption) {
            'tls' => 'tls',
            'none' => '',
            default => 'ssl',
        };

        $client = null;

        try {
            $client = (new ClientManager([
                'default' => 'default',
                'accounts' => [
                    'default' => [
                        'host' => $host,
                        'port' => (int) ($settings->imap_port ?: 993),
                        'protocol' => 'imap',
                        'encryption' => $encryption,
                        'validate_cert' => true,
                        'username' => $username,
                        'password' => (string) $password,
                        'timeout' => 15,
                    ],
                ],
                'options' => [
                    'fetch' => IMAP::FT_PEEK,
                    'sequence' => IMAP::ST_MSGN,
                    'fetch_body' => true,
                    'fetch_flags' => false,
                    'fetch_order' => 'desc',
                    'soft_fail' => false,
                ],
            ]))->account();

            $client->connect();
            $folder = $client->getFolder('INBOX');

            if (! $folder) {
                throw new RuntimeException('پوشه INBOX در صندوق ایمیل پیدا نشد.');
            }

            $query = $folder->query()
                ->whereAll()
                ->setFetchOrderDesc()
                ->limit(max(1, min($limit, 30)));

            $messages = [];

            foreach ($query->get() as $message) {
                $from = (string) $message->getFrom();
                $subject = (string) $message->getSubject();
                $rawBody = $message->getTextBody();

                if ($rawBody === '') {
                    $rawBody = $message->getHTMLBody();
                }

                $body = trim(preg_replace(
                    '/\s+/u',
                    ' ',
                    strip_tags(html_entity_decode($rawBody, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
                ) ?? '');

                $code = $this->extractVerificationCode($from, $subject, $body);
                $isEnamad = preg_match('/enamad|اینماد|نماد اعتماد/i', $from.' '.$subject.' '.$body) === 1;

                if (! $isEnamad) {
                    continue;
                }

                $date = $message->getDate();
                $messages[] = [
                    'from' => $from,
                    'subject' => $subject,
                    'date' => (string) $date,
                    'code' => $code,
                ];
            }

            return $messages;
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'اتصال یا خواندن صندوق IMAP ناموفق بود. میزبان، رمز و گواهی TLS را بررسی کنید.',
                0,
                $exception
            );
        } finally {
            if ($client !== null) {
                try {
                    $client->disconnect();
                } catch (Throwable) {
                    // A failed disconnect must not hide the original IMAP result.
                }
            }
        }
    }

    /**
     * Extract an eNamad verification code from a message without retaining its body.
     */
    public function extractVerificationCode(string $from, string $subject, string $body): ?string
    {
        $content = $this->normalizeDigits($from.' '.$subject.' '.$body);

        if (preg_match('/enamad|اینماد|نماد اعتماد/i', $content) !== 1) {
            return null;
        }

        if (preg_match('/(?:کد|code|verification|verify|otp|تایید|تأیید)[^0-9]{0,45}([0-9]{4,8})/iu', $content, $match)) {
            return $match[1];
        }

        if (preg_match('/\b([0-9]{4,8})\b/u', $content, $match)) {
            return $match[1];
        }

        return null;
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
