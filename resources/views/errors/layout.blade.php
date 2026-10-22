<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <title>@yield('title', 'خطا | فرزین')</title>
    <style>
        :root { color-scheme: light; font-family: Tahoma, Arial, sans-serif; background: #f5f4f0; color: #243140; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        main { width: min(100%, 680px); background: #fff; border: 1px solid #e3e1dc; border-radius: 28px; padding: clamp(28px, 6vw, 56px); text-align: center; box-shadow: 0 18px 55px rgb(35 49 64 / 7%); }
        .brand { font-size: 12px; letter-spacing: .18em; font-weight: 700; color: #168b91; }
        .code { margin: 26px 0 8px; font-size: clamp(54px, 12vw, 88px); line-height: 1; font-weight: 800; color: #168b91; }
        h1 { margin: 16px 0 0; font-size: clamp(21px, 4vw, 30px); }
        p { margin: 14px auto 0; max-width: 460px; font-size: 14px; line-height: 2; color: #66717d; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 30px; }
        a { display: inline-flex; justify-content: center; align-items: center; min-height: 44px; padding: 10px 18px; border-radius: 14px; border: 1px solid #d8e0e2; color: #243140; text-decoration: none; font-size: 13px; font-weight: 600; }
        a.primary { background: #168b91; border-color: #168b91; color: #fff; }
        a:focus-visible { outline: 3px solid #8ed4d3; outline-offset: 3px; }
    </style>
</head>
<body>
    <main>
        <div class="brand">FARZIN / فرزین</div>
        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        @if (!empty($requestId))
            <p class="request-reference">
                کد پیگیری: <code>{{ $requestId }}</code>
            </p>
        @endif
        <div class="actions">
            <a class="primary" href="{{ route('home') }}">بازگشت به فروشگاه</a>
            <a href="{{ route('shop.index') }}">مشاهده محصولات</a>
        </div>
    </main>
</body>
</html>
