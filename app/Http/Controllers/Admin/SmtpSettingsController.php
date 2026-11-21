<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use App\Models\StorefrontSetting;
use App\Services\Mail\ImapInboxReader;
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
            'trust_badge_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $settings = SmtpSetting::current();
        unset($data['password'], $data['imap_password'], $data['trust_badge_image']);

        if ($request->filled('password')) {
            $settings->password = $request->input('password');
        }
        if ($request->filled('imap_password')) {
            $settings->imap_password = $request->input('imap_password');
        }

        $settings->fill($data);
        $settings->enabled = $request->boolean('enabled');
        $settings->save();

        if ($request->hasFile('trust_badge_image')) {
            $newPath = $request->file('trust_badge_image')->store('trust-badges', 'public');
            if (! $newPath) {
                return back()->withErrors(['trust_badge_image' => 'ذخیره تصویر نماد ناموفق بود. تنظیمات ایمیل ذخیره شد؛ تصویر قبلی حفظ شد.']);
            }
            $oldPath = $this->saveTrustBadgeImagePath($newPath);
            if ($oldPath && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

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


    public function inboxStatus(): \\Illuminate\\Http\\JsonResponse
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
