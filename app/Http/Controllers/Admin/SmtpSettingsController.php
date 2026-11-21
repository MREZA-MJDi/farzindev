<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\SmtpSetting;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Config;
use Illuminate\\Support\\Facades\\Mail;
use Illuminate\\View\\View;
use Throwable;

class SmtpSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = SmtpSetting::current();

        return view('admin.smtp-settings.edit', [
            'settings' => $settings,
            'hasPassword' => filled($settings->password),
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
        ]);

        $settings = SmtpSetting::current();
        unset($data['password']);

        if ($request->filled('password')) {
            $settings->password = $request->input('password');
        }

        $settings->fill($data);
        $settings->enabled = $request->boolean('enabled');
        $settings->save();

        return redirect()->route('admin.smtp-settings.edit')
            ->with('success', 'تنظیمات SMTP با موفقیت ذخیره شد.');
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
            'encryption' => $settings->encryption === 'none' ? null : $settings->encryption,
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

            return back()->withErrors([
                'smtp' => 'ارسال پیام آزمایشی ناموفق بود. میزبان، پورت، رمزنگاری و اطلاعات ورود را بررسی کنید.',
            ]);
        } finally {
            Mail::purge('smtp');
        }

        return back()->with('success', 'پیام آزمایشی با موفقیت ارسال شد.');
    }
}
