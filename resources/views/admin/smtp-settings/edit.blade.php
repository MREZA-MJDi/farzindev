@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6" dir="rtl">
    <header>
        <p class="text-xs font-semibold text-[var(--admin-muted)]">مدیریت زیرساخت و اعتبار کسب‌وکار</p>
        <h1 class="mt-2 text-xl font-bold text-[var(--admin-text)]">ایمیل سازمانی و نماد اعتماد</h1>
        <p class="mt-2 text-sm leading-7 text-[var(--admin-text-soft)]">تنظیم ارسال و دریافت ایمیل، مشاهده رمز صندوق، دریافت کد اینماد و بارگذاری تصویر نماد برای نمایش در فوتر.</p>
    </header>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.smtp-settings.update') }}" class="space-y-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-sm sm:p-7">
        @csrf
        @method('PUT')

        <section class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۱. تنظیم ارسال ایمیل (SMTP)</h2>
                    <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">برای ارسال پیام‌های سایت و ایمیل‌های سیستمی.</p>
                </div>
                <label class="flex items-center gap-2 rounded-xl bg-[var(--admin-surface)] px-4 py-3 text-sm font-semibold">
                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled)) class="h-4 w-4 rounded">
                    فعال‌سازی SMTP
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium">میزبان SMTP
                    <input name="host" value="{{ old('host', $settings->host) }}" placeholder="mail.farajihome.com" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" autocomplete="off">
                </label>
                <label class="block text-sm font-medium">پورت SMTP
                    <input type="number" min="1" max="65535" name="port" value="{{ old('port', $settings->port ?: 587) }}" required class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                </label>
                <label class="block text-sm font-medium">رمزنگاری SMTP
                    <select name="encryption" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                        <option value="tls" @selected(old('encryption', $settings->encryption) === 'tls')>STARTTLS / TLS</option>
                        <option value="ssl" @selected(old('encryption', $settings->encryption) === 'ssl')>SSL</option>
                        <option value="none" @selected(old('encryption', $settings->encryption) === 'none')>بدون رمزنگاری</option>
                    </select>
                </label>
                <label class="block text-sm font-medium">نام کاربری SMTP
                    <input name="username" value="{{ old('username', $settings->username ?: 'info@farajihome.com') }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" autocomplete="username">
                </label>
                <label class="block text-sm font-medium sm:col-span-2">رمز کامل صندوق ایمیل
                    <div class="mt-2 flex gap-2">
                        <input id="smtp-password" type="password" name="password" value="{{ $settings->password }}" autocomplete="new-password" placeholder="{{ $hasPassword ? 'رمز فعلی ذخیره شده است' : 'رمز صندوق ایمیل را وارد کنید' }}" class="min-w-0 flex-1 rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                        <button type="button" data-toggle-secret="smtp-password" class="rounded-xl border border-[var(--admin-border)] px-3 text-xs font-bold">نمایش رمز</button>
                        <button type="button" data-copy-target="smtp-password" class="rounded-xl border border-[var(--admin-border)] px-3 text-xs font-bold">کپی رمز</button>
                    </div>
                    <span class="mt-1 block text-xs text-[var(--admin-muted)]">رمز در پایگاه داده رمزنگاری می‌شود. برای حفظ رمز قبلی، آن را تغییر ندهید.</span>
                </label>
                <label class="block text-sm font-medium">آدرس فرستنده
                    <input type="email" name="from_address" value="{{ old('from_address', $settings->from_address ?: 'info@farajihome.com') }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                </label>
                <label class="block text-sm font-medium">نام نمایشی فرستنده
                    <input name="from_name" value="{{ old('from_name', $settings->from_name ?: 'فرجی‌هوم') }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                </label>
            </div>
        </section>

        <div class="border-t border-[var(--admin-border)]"></div>

        <section class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-[var(--admin-text)]">۲. دریافت ایمیل و کد اینماد (IMAP)</h2>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">اطلاعات IMAP معمولاً با همان نام کاربری و رمز صندوق ایمیل کار می‌کند. اگر سرور تنظیم جداگانه دارد، اینجا وارد کنید.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium">آدرس صندوق
                    <input type="email" name="mailbox_address" value="{{ old('mailbox_address', $settings->mailbox_address ?: 'info@farajihome.com') }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                </label>
                <label class="block text-sm font-medium">میزبان IMAP
                    <input name="imap_host" value="{{ old('imap_host', $settings->imap_host ?: $settings->host) }}" placeholder="mail.farajihome.com" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                </label>
                <label class="block text-sm font-medium">پورت IMAP
                    <input type="number" min="1" max="65535" name="imap_port" value="{{ old('imap_port', $settings->imap_port ?: 993) }}" required class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                </label>
                <label class="block text-sm font-medium">رمزنگاری IMAP
                    <select name="imap_encryption" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm">
                        <option value="ssl" @selected(old('imap_encryption', $settings->imap_encryption ?: 'ssl') === 'ssl')>SSL</option>
                        <option value="tls" @selected(old('imap_encryption', $settings->imap_encryption) === 'tls')>STARTTLS / TLS</option>
                        <option value="none" @selected(old('imap_encryption', $settings->imap_encryption) === 'none')>بدون رمزنگاری</option>
                    </select>
                </label>
                <label class="block text-sm font-medium">نام کاربری IMAP
                    <input name="imap_username" value="{{ old('imap_username', $settings->imap_username ?: $settings->username ?: 'info@farajihome.com') }}" class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                </label>
                <label class="block text-sm font-medium">رمز IMAP (در صورت تفاوت)
                    <div class="mt-2 flex gap-2">
                        <input id="imap-password" type="password" name="imap_password" value="{{ $mailboxPassword }}" autocomplete="new-password" placeholder="در صورت یکسان بودن، رمز SMTP استفاده می‌شود" class="min-w-0 flex-1 rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                        <button type="button" data-toggle-secret="imap-password" class="rounded-xl border border-[var(--admin-border)] px-3 text-xs font-bold">نمایش</button>
                        <button type="button" data-copy-target="imap-password" class="rounded-xl border border-[var(--admin-border)] px-3 text-xs font-bold">کپی</button>
                    </div>
                </label>
            </div>
            <div class="rounded-xl bg-[var(--admin-surface)] p-4 text-xs leading-7 text-[var(--admin-text-soft)]">
                <strong class="text-[var(--admin-text)]">نحوه دریافت:</strong>
                دکمه «خواندن صندوق و دریافت کد» ایمیل‌های اخیر مرتبط با اینماد را بررسی می‌کند و کد احتمالی را همین‌جا نشان می‌دهد. برای این قابلیت باید افزونه PHP IMAP روی VPS فعال باشد.
            </div>
        </section>

        <div class="border-t border-[var(--admin-border)]"></div>

        <section class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-[var(--admin-text)]">۳. تصویر نماد اعتماد برای فوتر</h2>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">تصویر رسمی نماد را بارگذاری کنید؛ پس از ذخیره، در بخش مناسب فوتر سایت نمایش داده می‌شود.</p>
            </div>
            @if($trustBadgeImagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($trustBadgeImagePath))
                <div class="flex flex-wrap items-center gap-4 rounded-xl border border-[var(--admin-border)] p-4">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($trustBadgeImagePath) }}" alt="پیش‌نمایش نماد اعتماد" class="h-24 w-24 rounded-lg bg-white object-contain p-2">
                    <div class="text-xs leading-6 text-[var(--admin-text-soft)]">تصویر فعلی نماد اعتماد؛ با بارگذاری تصویر جدید جایگزین می‌شود.</div>
                </div>
            @endif
            <label class="block text-sm font-medium">انتخاب تصویر نماد (JPG، PNG یا WEBP؛ حداکثر ۵ مگابایت)
                <input type="file" name="trust_badge_image" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm file:ml-3 file:rounded-lg file:border-0 file:bg-[var(--admin-surface)] file:px-3 file:py-2">
            </label>
        </section>

        <div class="flex justify-end border-t border-[var(--admin-border)] pt-5">
            <button type="submit" class="rounded-xl bg-[var(--admin-text)] px-6 py-3 text-sm font-bold text-white hover:opacity-90">ذخیره همه تنظیمات</button>
        </div>
    </form>

    <div class="grid gap-5 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.smtp-settings.test') }}" class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-sm">
            @csrf
            <h2 class="text-sm font-bold text-[var(--admin-text)]">تست ارسال ایمیل</h2>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">پس از ذخیره تنظیمات، پیام آزمایشی بفرستید.</p>
            <div class="mt-4 space-y-3">
                <input type="email" name="test_recipient" required placeholder="ایمیل دریافت‌کننده تست" class="w-full rounded-xl border border-[var(--admin-border)] bg-white px-3 py-3 text-sm" dir="ltr">
                <button type="submit" class="w-full rounded-xl border border-[var(--admin-border)] px-4 py-3 text-sm font-bold">ارسال پیام تست</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.smtp-settings.inbox.sync') }}" class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-sm">
            @csrf
            <h2 class="text-sm font-bold text-[var(--admin-text)]">صندوق ورودی اینماد</h2>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">ایمیل‌های مرتبط با اینماد را از صندوق {{ $settings->mailbox_address ?: 'ثبت‌شده' }} بخوان.</p>
            <button type="submit" class="mt-4 w-full rounded-xl bg-[var(--admin-accent)] px-4 py-3 text-sm font-bold text-white">خواندن صندوق و دریافت کد</button>
        </form>
    </div>

    <section id="enamad-inbox-results" class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-base font-bold text-[var(--admin-text)]">نتیجه بررسی ایمیل‌های اینماد</h2><span id="enamad-inbox-sync-time" class="text-xs text-[var(--admin-muted)]">{{ $inboxLastSync ? 'آخرین بررسی: '.\\Illuminate\\Support\\Carbon::parse($inboxLastSync)->format('Y/m/d H:i') : 'هنوز بررسی خودکاری انجام نشده است' }}</span></div>
            <p id="enamad-inbox-error" class="mt-2 text-xs text-rose-600">{{ $inboxLastError }}</p>
            @forelse($inboxMessages as $message)
                <article class="mt-4 rounded-xl border border-[var(--admin-border)] p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="break-all text-xs font-semibold text-[var(--admin-text)]">{{ $message['subject'] ?: 'بدون عنوان' }}</p>
                            <p class="mt-1 break-all text-[11px] text-[var(--admin-muted)]">{{ $message['from'] }} · {{ $message['date'] }}</p>
                        </div>
                        @if($message['code'])
                            <div class="flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2">
                                <strong class="font-mono text-lg tracking-widest text-emerald-800" dir="ltr">{{ $message['code'] }}</strong>
                                <button type="button" data-copy-value="{{ $message['code'] }}" class="text-xs font-bold text-emerald-800">کپی کد</button>
                            </div>
                        @else
                            <span class="text-xs text-[var(--admin-muted)]">کد مشخصی استخراج نشد</span>
                        @endif
                    </div>
                </article>
            @empty
                <p class="mt-3 text-sm text-[var(--admin-muted)]">پیام مرتبطی در ایمیل‌های اخیر پیدا نشد.</p>
            @endforelse
        </section>
</div>

<script>
document.addEventListener('click', async (event) => {
    const toggle = event.target.closest('[data-toggle-secret]');
    if (toggle) {
        const input = document.getElementById(toggle.dataset.toggleSecret);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            toggle.textContent = input.type === 'password' ? 'نمایش رمز' : 'پنهان کردن';
        }
    }

    const copyButton = event.target.closest('[data-copy-target], [data-copy-value]');
    if (copyButton) {
        const input = copyButton.dataset.copyTarget ? document.getElementById(copyButton.dataset.copyTarget) : null;
        const value = input ? input.value : copyButton.dataset.copyValue;
        if (!value) return;
        try {
            await navigator.clipboard.writeText(value);
            const oldText = copyButton.textContent;
            copyButton.textContent = 'کپی شد';
            setTimeout(() => copyButton.textContent = oldText, 1400);
        } catch (error) {
            if (input) {
                input.type = 'text';
                input.select();
                document.execCommand('copy');
            }
        }
    }
});

<script>
(() => {
    const endpoint = @json(route('admin.smtp-settings.inbox.status'));
    const container = document.querySelector('#enamad-inbox-results');
    const time = document.getElementById('enamad-inbox-sync-time');
    const error = document.getElementById('enamad-inbox-error');
    if (!container || !endpoint) return;
    const render = (messages) => {
        container.querySelectorAll('[data-inbox-message]').forEach(node => node.remove());
        const empty = container.querySelector('[data-inbox-empty]');
        if (empty) empty.remove();
        if (!messages.length) {
            const p = document.createElement('p'); p.dataset.inboxEmpty = '1'; p.className = 'mt-3 text-sm text-[var(--admin-muted)]'; p.textContent = 'پیام مرتبطی در ایمیل‌های اخیر پیدا نشد.'; container.appendChild(p); return;
        }
        messages.forEach(message => {
            const article = document.createElement('article'); article.dataset.inboxMessage = '1'; article.className = 'mt-4 rounded-xl border border-[var(--admin-border)] p-4';
            const top = document.createElement('div'); top.className = 'flex flex-wrap items-start justify-between gap-3';
            const meta = document.createElement('div'); meta.className = 'min-w-0';
            const subject = document.createElement('p'); subject.className = 'break-all text-xs font-semibold text-[var(--admin-text)]'; subject.textContent = message.subject || 'بدون عنوان';
            const from = document.createElement('p'); from.className = 'mt-1 break-all text-[11px] text-[var(--admin-muted)]'; from.textContent = (message.from || '') + ' · ' + (message.date || '');
            meta.append(subject, from); top.appendChild(meta);
            if (message.code) {
                const codeWrap = document.createElement('div'); codeWrap.className = 'flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2';
                const code = document.createElement('strong'); code.className = 'font-mono text-lg tracking-widest text-emerald-800'; code.dir = 'ltr'; code.textContent = message.code;
                const copy = document.createElement('button'); copy.type = 'button'; copy.dataset.copyValue = message.code; copy.className = 'text-xs font-bold text-emerald-800'; copy.textContent = 'کپی کد'; codeWrap.append(code, copy); top.appendChild(codeWrap);
            } else { const noCode = document.createElement('span'); noCode.className = 'text-xs text-[var(--admin-muted)]'; noCode.textContent = 'کد مشخصی استخراج نشد'; top.appendChild(noCode); }
            article.appendChild(top); container.appendChild(article);
        });
    };
    const refresh = async () => {
        try {
            const response = await fetch(endpoint, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) return;
            const data = await response.json(); render(data.messages || []);
            if (time && data.last_sync) time.textContent = 'آخرین بررسی: ' + new Date(data.last_sync).toLocaleString('fa-IR');
            if (error) error.textContent = data.error || '';
        } catch (_) { /* Keep the last successfully rendered inbox visible. */ }
    };
    window.setInterval(refresh, 60000);
})();
</script>
</script>
@endsection
