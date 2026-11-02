<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>ورود مدیریت | {{ $storefrontBrandName }}</title>
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--admin-bg,#f4f5f3)] text-[var(--admin-text,#18211f)] antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-5 py-10">
        <section class="w-full rounded-3xl border border-[var(--admin-border,#e2e7e4)] bg-white p-7 shadow-xl sm:p-9">
            <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2 text-lg font-black" aria-label="بازگشت به فروشگاه">{{ $storefrontBrandName }}</a>
            <p class="mb-2 text-sm font-semibold text-[var(--admin-accent,#147b69)]">دسترسی امن</p>
            <h1 class="text-2xl font-black">ورود به مدیریت</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">این بخش فقط برای کاربران مجاز مدیریت است.</p>

            @if(session('success'))
                <div role="status" class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div role="status" class="mt-5 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">{{ session('warning') }}</div>
            @endif
            @if($errors->any())
                <div role="alert" class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="admin-email" class="mb-2 block text-sm font-bold">ایمیل مدیریت</label>
                    <input id="admin-email" name="email" type="email" value="{{ old('email') }}" dir="ltr" autocomplete="username" required autofocus class="w-full rounded-xl border border-slate-300 px-4 py-3 text-left outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="admin-password" class="mb-2 block text-sm font-bold">رمز عبور</label>
                    <input id="admin-password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                    مرا به خاطر بسپار
                </label>
                <button type="submit" class="w-full rounded-xl bg-slate-900 px-5 py-3.5 font-bold text-white transition hover:bg-emerald-800">ورود به پنل مدیریت</button>
            </form>
            <a href="{{ route('home') }}" class="mt-6 block text-center text-sm text-slate-500 hover:text-slate-900">بازگشت به فروشگاه</a>
        </section>
    </main>
</body>
</html>
