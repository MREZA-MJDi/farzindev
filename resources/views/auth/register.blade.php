@extends('layouts.app')
@section('title', 'ایجاد حساب | فرزین')
@section('description', 'ساخت حساب کاربری فرزین برای ثبت و مدیریت سفارش‌ها.')
@section('body_class', 'fz-auth-body min-h-screen antialiased')
@section('content')
@php($brandImage = $brandProduct?->images?->first())
<section class="fz-auth" aria-labelledby="fz-register-heading"><div class="fz-auth__layout">
    <div class="fz-auth__visual">
        @if($brandImage?->url)<img class="fz-auth__product-image" src="{{ $brandImage->url }}" @if($brandImage->responsive_srcset) srcset="{{ $brandImage->responsive_srcset }}" sizes="(max-width: 900px) 100vw, 48vw" @endif alt="{{ $brandImage->alt ?: $brandProduct->name }}" width="1100" height="900" loading="eager" fetchpriority="high">@endif
        <div class="fz-auth__visual-shade"></div>
        <a class="fz-auth__wordmark" href="{{ route('home') }}" aria-label="فرزین، صفحه اصلی">فرزین <span>FARZIN / KITCHEN EQUIPMENT</span></a>
        <div class="fz-auth__visual-copy"><p>خرید خود را ساده‌تر مدیریت کنید</p><h2>یک حساب،<br>برای خریدهای شما.</h2><span>اطلاعات تماس و سفارش‌های خود را یک‌جا نگه دارید و هر زمان به حساب‌تان برگردید.</span></div>
        <span class="fz-auth__visual-foot">FARZIN · HOOD & SINK</span>
    </div>
    <div class="fz-auth__form-side"><div class="fz-auth__form-wrap fz-auth__form-wrap--register">
        <a href="{{ route('home') }}" class="fz-auth__mobile-brand">فرزین <span>تجهیزات مدرن آشپزخانه</span></a>
        <div class="fz-auth__eyebrow"><span></span> عضویت در فرزین</div>
        <h1 id="fz-register-heading">حساب خود را بسازید.</h1>
        <p class="fz-auth__intro">برای ثبت سفارش و دسترسی به سوابق خرید، اطلاعات زیر را وارد کنید.</p>
        @if($errors->any())<div class="fz-auth__notice fz-auth__notice--error" role="alert" aria-live="polite"><strong>لطفاً اطلاعات را بررسی کنید.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('register.store') }}" method="POST" class="fz-auth__form">
            @csrf
            <div class="fz-auth__field-grid">
                <div class="fz-auth__field"><label for="register-first-name">نام</label><input id="register-first-name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" placeholder="نام شما" required>@error('first_name')<small role="alert">{{ $message }}</small>@enderror</div>
                <div class="fz-auth__field"><label for="register-last-name">نام خانوادگی</label><input id="register-last-name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" placeholder="نام خانوادگی" required>@error('last_name')<small role="alert">{{ $message }}</small>@enderror</div>
            </div>
            <div class="fz-auth__field"><label for="register-phone">شماره موبایل</label><input id="register-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="09123456789" required>@error('phone')<small role="alert">{{ $message }}</small>@enderror</div>
            <div class="fz-auth__field"><label for="register-email">ایمیل <span>اختیاری</span></label><input id="register-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="name@example.com">@error('email')<small role="alert">{{ $message }}</small>@enderror</div>
            <div class="fz-auth__field-grid">
                <div class="fz-auth__field"><label for="register-password">رمز عبور</label><input id="register-password" type="password" name="password" autocomplete="new-password" placeholder="حداقل ۸ کاراکتر" required aria-describedby="fz-password-help"><small id="fz-password-help" class="fz-auth__field-hint">حرف بزرگ و کوچک انگلیسی و عدد داشته باشد.</small>@error('password')<small role="alert">{{ $message }}</small>@enderror</div>
                <div class="fz-auth__field"><label for="register-password-confirmation">تکرار رمز عبور</label><input id="register-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="تکرار رمز عبور" required>@error('password_confirmation')<small role="alert">{{ $message }}</small>@enderror</div>
            </div>
            <label class="fz-auth__terms"><input type="checkbox" name="terms" value="1" @checked(old('terms')) required><span>با <a href="{{ route('terms') }}" target="_blank" rel="noopener">قوانین و شرایط استفاده</a> موافقم.</span></label>
            @error('terms')<small class="fz-auth__terms-error" role="alert">{{ $message }}</small>@enderror
            <x-security.turnstile />
            <button class="fz-auth__submit" type="submit">ایجاد حساب <span aria-hidden="true">←</span></button>
        </form>
        <div class="fz-auth__switch"><span>قبلاً حساب ساخته‌اید؟</span><a href="{{ route('login', request()->only('intended')) }}">ورود به حساب</a></div>
        <a class="fz-auth__back" href="{{ route('shop.index') }}">بازگشت به فروشگاه</a>
    </div></div>
</div></section>
@endsection
