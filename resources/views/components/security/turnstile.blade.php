@props(['action' => 'form'])

@php
    // The site key is public. Rendering must not depend on the server-only secret.
    $siteKey = config('services.turnstile.site_key');
@endphp

@if(filled($siteKey))
    <div class="space-y-2">
        <div
            class="cf-turnstile"
            data-sitekey="{{ $siteKey }}"
            data-action="{{ $action }}"
            data-theme="light"
            data-error-callback="farzinTurnstileError"
        ></div>

        <p
            class="hidden text-xs leading-6 text-red-600"
            data-turnstile-error
            role="alert"
        ></p>

        @error('captcha')
            <p class="text-xs leading-6 text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @once
        <script>
            window.farzinTurnstileError = function (code) {
                const messages = {
                    '110200': 'دامنه farajihome.com در فهرست دامنه‌های مجاز Widget قرار ندارد. تنظیم Hostname ویجت Cloudflare را بررسی کنید.',
                    '200500': 'اتصال مرورگر به challenges.cloudflare.com برقرار نشد. شبکه، فیلترشکن یا افزونه‌های مسدودکننده را بررسی کنید.'
                };

                document.querySelectorAll('[data-turnstile-error]').forEach(function (element) {
                    element.textContent = messages[String(code)]
                        || 'خطای Cloudflare Turnstile با کد ' + String(code) + ' رخ داد. صفحه را تازه کنید و دوباره تلاش کنید.';
                    element.classList.remove('hidden');
                });

                console.warn('Cloudflare Turnstile error:', code);
                return true;
            };
        </script>
        <script
            src="https://challenges.cloudflare.com/turnstile/v0/api.js?hl=fa"
            async
            defer
        ></script>
    @endonce
@elseif(app()->isProduction())
    <p class="text-xs leading-6 text-red-600" role="alert">
        Site Key سامانه ضدربات تنظیم نشده است؛ امکان ارسال این فرم وجود ندارد.
    </p>
@endif
