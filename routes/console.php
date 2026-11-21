<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('orders:expire-unpaid --minutes=1440')
    ->hourly()
    ->withoutOverlapping();


Artisan::command('enamad:inbox-sync', function (\App\Services\Mail\ImapInboxReader $reader) {
    $settings = \App\Models\SmtpSetting::current();
    if (! $settings->enabled || blank($settings->imap_host ?: $settings->host) || blank($settings->imap_username ?: $settings->username) || blank($settings->imap_password ?: $settings->password)) {
        $this->comment('صندوق IMAP هنوز تنظیم نشده است؛ دریافت خودکار فعلاً رد شد.');
        return 0;
    }

    try {
        $messages = $reader->recentVerificationMessages($settings, 20);
        cache()->put('enamad.inbox.latest', $messages, now()->addDays(7));
        cache()->put('enamad.inbox.last_sync', now()->toIso8601String(), now()->addDays(7));
        cache()->forget('enamad.inbox.last_error');
        $this->info('صندوق اینماد بررسی شد؛ '.count($messages).' پیام مرتبط یافت شد.');
        return 0;
    } catch (\Throwable $exception) {
        report($exception);
        cache()->put('enamad.inbox.last_error', 'خواندن صندوق ناموفق بود؛ تنظیمات IMAP و پیکربندی اتصال IMAP و گواهی TLS را بررسی کنید.', now()->addHours(6));
        $this->error('خواندن صندوق اینماد ناموفق بود.');
        return 1;
    }
})->purpose('Read recent Enamad verification emails into the admin dashboard');

Schedule::command('enamad:inbox-sync')
    ->everyFiveMinutes()
    ->withoutOverlapping();
