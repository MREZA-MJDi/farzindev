<?php

namespace App\Services\Mail;

use App\Models\SmtpSetting;
use RuntimeException;

class ImapInboxReader
{
    /**
     * Read a small, recent window only. Message bodies are never persisted.
     *
     * @return array<int, array{from:string,subject:string,date:string,code:?string}>
     */
    public function recentVerificationMessages(SmtpSetting $settings, int $limit = 20): array
    {
        if (! function_exists('imap_open')) {
            throw new RuntimeException('افزونه PHP IMAP روی سرور نصب یا فعال نیست.');
        }

        $host = trim((string) ($settings->imap_host ?: $settings->host));
        $username = trim((string) ($settings->imap_username ?: $settings->username));
        $password = $settings->imap_password ?: $settings->password;

        if ($host === '' || $username === '' || blank($password)) {
            throw new RuntimeException('اطلاعات اتصال IMAP کامل نیست؛ میزبان، نام کاربری و رمز صندوق را وارد کنید.');
        }

        $port = (int) ($settings->imap_port ?: 993);
        $mode = match ($settings->imap_encryption) {
            'tls' => '/imap/tls',
            'none' => '/imap',
            default => '/imap/ssl',
        };
        $mailbox = sprintf('{%s:%d%s}INBOX', $host, $port, $mode);
        $connection = @imap_open($mailbox, $username, (string) $password, OP_READONLY, 1);

        if (! $connection) {
            throw new RuntimeException('اتصال به صندوق ورودی ناموفق بود. تنظیمات IMAP، پورت و گواهی TLS را بررسی کنید.');
        }

        try {
            $messageNumbers = imap_search($connection, 'ALL') ?: [];
            rsort($messageNumbers);
            $messages = [];

            foreach (array_slice($messageNumbers, 0, max(1, min($limit, 30))) as $number) {
                $overview = imap_fetch_overview($connection, (string) $number, 0)[0] ?? null;
                if (! $overview) {
                    continue;
                }

                $from = $this->decodeHeader((string) ($overview->from ?? ''));
                $subject = $this->decodeHeader((string) ($overview->subject ?? ''));
                $rawBody = (string) @imap_body($connection, (int) $number, FT_PEEK);
                $body = trim(preg_replace('/\s+/u', ' ', strip_tags($rawBody)) ?? '');

                $isEnamad = preg_match('/enamad|اینماد|نماد اعتماد/i', $from.' '.$subject.' '.$body) === 1;
                if (! $isEnamad) {
                    continue;
                }

                $code = null;
                if (preg_match('/(?:کد|code|verification|verify|otp|تایید|تأیید)[^0-9]{0,45}([0-9]{4,8})/iu', $body.' '.$subject, $match)) {
                    $code = $match[1];
                } elseif (preg_match('/\b([0-9]{4,8})\b/u', $body, $match)) {
                    $code = $match[1];
                }

                $messages[] = [
                    'from' => $from,
                    'subject' => $subject,
                    'date' => (string) ($overview->date ?? ''),
                    'code' => $code,
                ];
            }

            return $messages;
        } finally {
            imap_close($connection);
        }
    }

    private function fetchTextBody($connection, int $messageNumber, object $structure, string $partNumber = ''): string
    {
        if (! empty($structure->parts)) {
            $chunks = [];
            foreach ($structure->parts as $index => $part) {
                $number = $partNumber === '' ? (string) ($index + 1) : $partNumber.'.'.($index + 1);
                if ((int) ($part->type ?? -1) === 0) {
                    $content = (string) @imap_fetchbody($connection, $messageNumber, $number, FT_PEEK);
                    $encoding = (int) ($part->encoding ?? 0);
                    if ($encoding === 3) {
                        $content = base64_decode($content, true) ?: '';
                    } elseif ($encoding === 4) {
                        $content = quoted_printable_decode($content);
                    }
                    $chunks[] = $content;
                } elseif (! empty($part->parts)) {
                    $chunks[] = $this->fetchTextBody($connection, $messageNumber, $part, $number);
                }
            }
            return implode("\\n", $chunks);
        }

        $content = $partNumber === ''
            ? (string) @imap_body($connection, $messageNumber, FT_PEEK)
            : (string) @imap_fetchbody($connection, $messageNumber, $partNumber, FT_PEEK);
        $encoding = (int) ($structure->encoding ?? 0);
        if ($encoding === 3) {
            return base64_decode($content, true) ?: '';
        }
        if ($encoding === 4) {
            return quoted_printable_decode($content);
        }
        return $content;
    }

    private function decodeHeader(string $value): string
    {
        if (! function_exists('imap_mime_header_decode')) {
            return $value;
        }

        $parts = imap_mime_header_decode($value);
        return collect($parts)->map(fn ($part) => $part->text ?? '')->implode('');
    }
}
