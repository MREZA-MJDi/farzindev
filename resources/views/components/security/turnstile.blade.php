@php
    $siteKey = config('services.turnstile.site_key');
    $secret = config('services.turnstile.secret');
    $action = match (request()->route()?->getName()) {
        'login.store' => 'login',
        'register.store' => 'signup',
        'admin.login.store' => 'admin-login',
        'checkout.place' => 'checkout',
        'contact.store' => 'contact',
        default => 'form',
    };
@endphp

@if(filled($siteKey) && filled($secret))
    <div class="space-y-2">
        <div
            class="cf-turnstile"
            data-sitekey="{{ $siteKey }}"
            data-action="{{ $action }}"
            data-theme="light"
        ></div>

        @error('captcha')
            <p class="text-xs leading-6 text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @once
        <script
            src="https://challenges.cloudflare.com/turnstile/v0/api.js?hl=fa"
            async
            defer
        ></script>
    @endonce
@elseif(app()->isProduction())
    <p class="text-xs leading-6 text-red-600" role="alert">
        سامانه ضدربات در حال حاضر پیکربندی نشده است؛ امکان ارسال این فرم وجود ندارد.
    </p>
@endif
