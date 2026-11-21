@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6" dir="rtl">
    <div>
        <p class="text-xs font-semibold text-[var(--admin-muted)]">مدیریت سایت و زیرساخت</p>
        <h1 class="mt-2 text-xl font-bold text-[var(--admin-text)]">تنظیمات ایمیل سازمانی (SMTP)</h1>
        <p class="mt-2 text-sm leading-7 text-[var(--admin-text-soft)]">اتصال ایمیل‌های سیستمی سایت را از این بخش مدیریت کنید. رمز عبور به‌صورت رمزنگاری‌شده در پایگاه داده نگهداری می‌شود و هرگز در صفحه نمایش داده نمی‌شود.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.smtp-settings.update') }}" class="space-y-5 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-sm sm:p-7">
        @csrf
        @method('PUT')
        <div class="flex items-center justify-between gap-4 rounded-xl bg-[var(--admin-surface)] p-4">
            <div>
                <p class="text-sm font-bold text-[var(--admin-text)]">فعال‌سازی ارسال ایمیل</p>
                <p class="mt-1 text-xs text-[var(--admin-muted)]">پس از تکمیل مشخصات سرور ایمیل، این گزینه را فعال کنید.</p>
            </div>
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled)) class="h-4 w-4 rounded">
                فعال
            </label>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-medium">آدرس SMTP
                <input name="host" value="{{ old('host', $settings->host) }}" placeholder="mail.example.com" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" autocomplete="off">
            </label>
            <label class="block text-sm font-medium">پورت
                <input type="number" min="1" max="65535" name="port" value="{{ old('port', $settings->port ?: 587) }}" required class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
            </label>
            <label class="block text-sm font-medium">نوع رمزنگاری
                <select name="encryption" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                    <option value="tls" @selected(old('encryption', $settings->encryption) === 'tls')>STARTTLS / TLS</option>
                    <option value="ssl" @selected(old('encryption', $settings->encryption) === 'ssl')>SSL</option>
                    <option value="none" @selected(old('encryption', $settings->encryption) === 'none')>بدون رمزنگاری</option>
                </select>
            </label>
            <label class="block text-sm font-medium">نام کاربری
                <input name="username" value="{{ old('username', $settings->username) }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" autocomplete="username">
            </label>
            <label class="block text-sm font-medium sm:col-span-2">رمز عبور SMTP
                <input type="password" name="password" autocomplete="new-password" placeholder="{{ $hasPassword ? 'برای حفظ رمز فعلی خالی بگذارید' : 'رمز عبور ایمیل را وارد کنید' }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                @if($hasPassword)<span class="mt-1 block text-xs text-emerald-700">رمز عبور قبلی ثبت شده است؛ برای تغییر آن رمز جدید وارد کنید.</span>@endif
            </label>
            <label class="block text-sm font-medium">ایمیل فرستنده
                <input type="email" name="from_address" value="{{ old('from_address', $settings->from_address) }}" placeholder="no-reply@example.com" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
            </label>
            <label class="block text-sm font-medium">نام نمایشی فرستنده
                <input name="from_name" value="{{ old('from_name', $settings->from_name ?: config('app.name')) }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
            </label>
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-[var(--admin-border)] pt-5">
            <button type="submit" class="rounded-xl bg-[var(--admin-text)] px-5 py-3 text-sm font-bold text-white hover:opacity-90">ذخیره تنظیمات</button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.smtp-settings.test') }}" class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-sm sm:p-7">
        @csrf
        <h2 class="text-base font-bold text-[var(--admin-text)]">تست اتصال ایمیل</h2>
        <p class="mt-2 text-sm leading-7 text-[var(--admin-text-soft)]">پس از ذخیره تنظیمات، یک پیام آزمایشی به آدرس دلخواه ارسال کنید تا اتصال واقعی بررسی شود.</p>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <input type="email" name="test_recipient" required placeholder="آدرس دریافت‌کننده تست" class="min-w-0 flex-1 rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
            <button type="submit" class="rounded-xl border border-[var(--admin-border-dark)] px-5 py-3 text-sm font-bold text-[var(--admin-text)] hover:bg-[var(--admin-surface)]">ارسال پیام تست</button>
        </div>
    </form>
</div>
@endsection
