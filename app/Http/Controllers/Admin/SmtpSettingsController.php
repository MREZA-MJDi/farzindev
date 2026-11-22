<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use App\Models\StorefrontSetting;
use App\Services\Mail\ImapInboxReader;
use App\Services\Mail\MailProviderClient;
use App\Services\StorefrontPageContent;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SmtpSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = SmtpSetting::current();

        return view('admin.smtp-settings.edit', [
            'settings' => $settings,
            'hasPassword' => filled($settings->password),
            'mailboxPassword' => $settings->imap_password ?: $settings->password,
            'trustBadgeImagePath' => $this->trustBadgeImagePath(),
            'inboxMessages' => cache()->get('enamad.inbox.latest', []),
            'inboxLastSync' => cache()->get('enamad.inbox.last_sync'),
            'inboxLastError' => cache()->get('enamad.inbox.last_error'),
            'inforFileExists' => is_file(public_path('infor')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', 'in:tls,ssl,none'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1024'],
            'from_address' => ['nullable', 'email:rfc', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'enabled' => ['nullable', 'boolean'],
            'mailbox_address' => ['nullable', 'email:rfc', 'max:255'],
            'imap_host' => ['nullable', 'string', 'max:255'],
            'imap_port' => ['required', 'integer', 'between:1,65535'],
            'imap_encryption' => ['required', 'in:ssl,tls,none'],
            'imap_username' => ['nullable', 'string', 'max:255'],
            'imap_password' => ['nullable', 'string', 'max:1024'],
            'enamad_embed_code' => [
                'nullable',
                'string',
                'max:3000',
                static function (string $attribute, mixed $value, \\Closure $fail): void {
                    $value = trim((string) $value);
                    if ($value === '') {
                        return;
                    }

                    $hasAnchor = preg_match('/<a\\b[^>]*href\\s*=\\s*([\"\\\'])(.*?)\\1/is', $value, $anchor) === 1;
                    $hasImage = preg_match('/<img\\b[^>]*src\\s*=\\s*([\"\\\'])(.*?)\\1/is', $value, $image) === 1;
                    $href = $hasAnchor ? html_entity_decode(trim($anchor[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
                    $src = $hasImage ? html_entity_decode(trim($image[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
                    $hrefParts = parse_url($href);
                    $srcParts = parse_url($src);
                    $hrefQuery = [];
                    $srcQuery = [];

                    if (is_array($hrefParts)) parse_str((string) ($hrefParts['query'] ?? ''), $hrefQuery);
                    if (is_array($srcParts)) parse_str((string) ($srcParts['query'] ?? ''), $srcQuery);

                    $id = trim((string) ($hrefQuery['id'] ?? ''));
                    $code = trim((string) ($hrefQuery['Code'] ?? ''));
                    $validHref = is_array($hrefParts)
                        && strtolower((string) ($hrefParts['scheme'] ?? '')) === 'https'
                        && strtolower((string) ($hrefParts['host'] ?? '')) === 'trustseal.enamad.ir'
                        && in_array((string) ($hrefParts['path'] ?? ''), ['', '/'], true)
                        && ! isset($hrefParts['port']) && ! isset($hrefParts['user'])
                        && preg_match('/^\\d{1,12}$/', $id) === 1
                        && preg_match('/^[A-Za-z0-9]{8,128}$/', $code) === 1;
                    $validImage = is_array($srcParts)
                        && strtolower((string) ($srcParts['scheme'] ?? '')) === 'https'
                        && strtolower((string) ($srcParts['host'] ?? '')) === 'trustseal.enamad.ir'
                        && ($srcParts['path'] ?? '') === '/logo.aspx'
                        && ! isset($srcParts['port']) && ! isset($srcParts['user'])
                        && (string) ($srcQuery['id'] ?? '') === $id
                        && (string) ($srcQuery['Code'] ?? '') === $code;

                    if (! $validHref || ! $validImage) {
                        $fail('کد باید شامل لینک و تصویر رسمی اینماد از دامنه trustseal.enamad.ir و شناسه یکسان باشد.');
                    }
                },
            ],
        ]);

        $settings = SmtpSetting::current();
        $enamadEmbedCode = trim((string) ($data['enamad_embed_code'] ?? ''));
        unset($data['password'], $data['imap_password'], $data['enamad_embed_code']);

        if ($request->filled('password')) {
            $settings->password = $request->input('password');
        }
        if ($request->filled('imap_password')) {
            $settings->imap_password = $request->input('imap_password');
        }

        $settings->fill($data);
        $settings->enabled = $request->boolean('enabled');
        $settings->save();

        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'page_home_enamad_embed_code'],
            ['value' => $enamadEmbedCode]
        );

        $enamadHref = '';
        if ($enamadEmbedCode !== '' && preg_match('/<a\\b[^>]*href\\s*=\\s*([\"\\\'])(.*?)\\1/is', $enamadEmbedCode, $anchor) === 1) {
            $enamadHref = html_entity_decode(trim($anchor[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'page_home_enamad_url'],
            ['value' => $enamadHref]
        );
        app(StorefrontPageContent::class)->clearCache();
        cache()->forget('storefront.enamad-badge-image.v1');

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'تنظیمات ایمیل و نماد اعتماد ذخیره شد.');
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_recipient' => ['required', 'email:rfc', 'max:255'],
        ]);

        $settings = SmtpSetting::current();
        if (! $settings->enabled || blank($settings->host) || blank($settings->from_address)) {
            return back()->withErrors(['smtp' => 'ابتدا SMTP را فعال و میزبان و ایمیل فرستنده را کامل کنید.']);
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port,
            'scheme' => $settings->encryption === 'ssl' ? 'smtps' : 'smtp',
            'auto_tls' => $settings->encryption !== 'none',
            'username' => $settings->username,
            'password' => $settings->password,
            'timeout' => 15,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name ?: config('app.name'));
        Mail::purge('smtp');

        try {
            Mail::mailer('smtp')->raw(
                'این پیام برای بررسی اتصال SMTP سایت ارسال شده است.',
                fn ($message) => $message->to($data['test_recipient'])->subject('تست اتصال ایمیل سایت')
            );
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['smtp' => 'ارسال پیام آزمایشی ناموفق بود. میزبان، پورت، رمزنگاری و اطلاعات ورود را بررسی کنید.']);
        } finally {
            Mail::purge('smtp');
        }

        return back()->with('success', 'پیام آزمایشی با موفقیت ارسال شد.');
    }

    public function syncInbox(ImapInboxReader $reader): RedirectResponse
    {
        try {
            $messages = $reader->recentVerificationMessages(SmtpSetting::current());
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['imap' => $exception->getMessage()]);
        }

        cache()->put('enamad.inbox.latest', $messages, now()->addDays(7));
        cache()->put('enamad.inbox.last_sync', now()->toIso8601String(), now()->addDays(7));
        cache()->forget('enamad.inbox.last_error');

        return back()
            ->with('success', count($messages) ? 'ایمیل‌های مرتبط با اینماد دریافت شدند.' : 'ایمیل مرتبط با اینماد در پیام‌های اخیر پیدا نشد.');
    }



    public function createMailbox(Request $request, MailProviderClient $provider): RedirectResponse
    {
        $data = $request->validate([
            'local_part' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9._+-]{0,63}$/', 'not_regex:/\.\./', 'not_regex:/\.$/'],
            'quota_mb' => ['nullable', 'integer', 'between:64,4096'],
        ]);

        $settings = SmtpSetting::current();
        if (filled($settings->mailbox_address)) {
            return back()->withErrors(['mailbox' => 'برای این پروژه یک صندوق اصلی ثبت شده است: '.$settings->mailbox_address]);
        }

        $password = rtrim(strtr(base64_encode(random_bytes(32)), '+/', 'AZ'), '=');

        try {
            $mailbox = $provider->createMailbox(
                $data['local_part'],
                $password,
                (int) ($data['quota_mb'] ?? config('mail-provider.quota_mb', 512))
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'mailbox' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'ایجاد صندوق ایمیل ناموفق بود. اتصال سرویس مرکزی را بررسی کنید.',
            ]);
        }

        $address = $mailbox['email'];
        $settings->forceFill([
            'mailbox_address' => $address,
            'username' => $address,
            'password' => $password,
            'imap_username' => $address,
            'imap_password' => $password,
            'host' => config('mail-provider.smtp_host'),
            'port' => config('mail-provider.smtp_port', 587),
            'encryption' => config('mail-provider.smtp_encryption', 'tls'),
            'imap_host' => config('mail-provider.imap_host'),
            'imap_port' => config('mail-provider.imap_port', 993),
            'imap_encryption' => config('mail-provider.imap_encryption', 'ssl'),
            'from_address' => $address,
            'from_name' => $settings->from_name ?: config('app.name'),
            'enabled' => true,
        ])->save();

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'صندوق '.$address.' ساخته شد. رمز آن در همین صفحه قابل نمایش و کپی است.');
    }

    public function uploadVerificationFile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'verification_file' => ['required', 'file', 'max:256'],
        ]);

        $file = $data['verification_file'];
        $originalName = strtolower($file->getClientOriginalName());
        $mime = $file->getMimeType();

        if (! in_array($originalName, ['infor', 'infor.txt'], true)
            || ! in_array($mime, ['text/plain', 'application/octet-stream'], true)) {
            return back()->withErrors([
                'verification_file' => 'فقط فایل متنی infor یا infor.txt قابل بارگذاری است.',
            ]);
        }

        try {
            $file->move(public_path(), 'infor');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['verification_file' => 'ذخیره فایل تأیید در ریشه سایت ناموفق بود.']);
        }

        return back()->with('success', 'فایل تأیید اینماد در ریشه سایت قرار گرفت: '.url('/infor'));
    }

    public function inboxStatus(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'messages' => cache()->get('enamad.inbox.latest', []),
            'last_sync' => cache()->get('enamad.inbox.last_sync'),
            'error' => cache()->get('enamad.inbox.last_error'),
        ]);
    }

    private function trustBadgeImagePath(): ?string
    {
        return StorefrontSetting::query()->where('key', 'page_home_enamad_image_path')->value('value');
    }

    private function saveTrustBadgeImagePath(string $path): ?string
    {
        $oldPath = StorefrontSetting::query()->where('key', 'page_home_enamad_image_path')->value('value');
        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'page_home_enamad_image_path'],
            ['value' => $path]
        );
        cache()->forget('storefront.enamad-badge-image.v1');
        return $oldPath;
    }
}
