@extends('layouts.app')
@section('title', 'ورود به حساب | ' . $storefrontBrandName)
@section('description', 'ورود به حساب کاربری ' . $storefrontBrandName . ' و مشاهده سفارش‌ها.')
@section('body_class', 'fz-auth-body min-h-screen antialiased')
@section('content')
@php($brandImage = $brandProduct?->images?->first())
<section class="fz-auth" aria-labelledby="fz-login-heading"><div class="fz-auth__layout">
    <div class="fz-auth__visual">
        @if($brandImage?->url)<img class="fz-auth__product-image" src="{{ $brandImage->url }}" @if($brandImage->responsive_srcset) srcset="{{ $brandImage->responsive_srcset }}" sizes="(max-width: 900px) 100vw, 48vw" @endif alt="{{ $brandImage->alt ?: $brandProduct->name }}" width="1100" height="900" loading="eager" fetchpriority="high">@endif
        <div class="fz-auth__visual-shade"></div>
        <a class="fz-auth__wordmark" href="{{ route('home') }}" aria-label="{{ $storefrontBrandName }}، صفحه اصلی">{{ $storefrontBrandName }} <span>تجهیزات مدرن آشپزخانه</span></a>
        <div class="fz-auth__visual-copy"><p>انتخاب آگاهانه برای آشپزخانه</p><h2>جزئیات را ببینید.<br>با اطمینان انتخاب کنید.</h2><span>محصول‌ها، قیمت‌ها و مشخصات را بررسی کنید و سفارش‌های خود را در حساب کاربری مدیریت کنید.</span></div>
        @if($brandProduct)
            <a class="fz-auth__featured" href="{{ route('product.show', $brandProduct->slug) }}"><span>محصول منتخب</span><strong>{{ $brandProduct->name }}</strong><b>{{ \App\Helpers\PersianNumberHelper::format((float) $brandProduct->price) }} تومان <i aria-hidden="true">←</i></b></a>
        @else
            <span class="fz-auth__visual-foot">{{ $storefrontBrandName }} · هود و سینک آشپزخانه</span>
        @endif
    </div>
    <div class="fz-auth__form-side"><div class="fz-auth__form-wrap">
        <a href="{{ route('home') }}" class="fz-auth__mobile-brand">{{ $storefrontBrandName }} <span>تجهیزات مدرن آشپزخانه</span></a>
        <div class="fz-auth__eyebrow"><span></span> حساب کاربری {{ $storefrontBrandName }}</div>
        <h1 id="fz-login-heading">خوش برگشتید.</h1>
        <p class="fz-auth__intro">برای ادامه خرید و مشاهده سفارش‌ها وارد حساب خود شوید.</p>
        @if(session('warning'))<div class="fz-auth__notice fz-auth__notice--warning" role="status">{{ session('warning') }}</div>@endif
        @if(session('success'))<div class="fz-auth__notice fz-auth__notice--success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="fz-auth__notice fz-auth__notice--error" role="alert"><strong>ورود انجام نشد.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('login.store') }}" method="POST" class="fz-auth__form">
            @csrf
            @if(request()->query('intended') === '/checkout' || session()->has('url.intended'))<div class="fz-auth__checkout-note">برای ادامه خرید وارد شوید؛ پس از ورود به مرحله تسویه برمی‌گردید.</div>@endif
            <div class="fz-auth__field"><label for="login-identifier">شماره موبایل یا ایمیل</label><input id="login-identifier" type="text" name="phone" value="{{ old('phone') }}" placeholder="09123456789" autocomplete="username" inputmode="text" required autofocus>@error('phone')<small role="alert">{{ $message }}</small>@enderror</div>
            <div class="fz-auth__field"><div class="fz-auth__field-heading"><label for="login-password">رمز عبور</label><a href="{{ route('contact') }}">برای بازیابی رمز با پشتیبانی تماس بگیرید</a></div><input id="login-password" type="password" name="password" placeholder="رمز عبور" autocomplete="current-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">@error('password')<small role="alert">{{ $message }}</small>@enderror</div>
            <label class="fz-auth__remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>مرا به خاطر بسپار</span></label>
            <x-security.turnstile />
            <button class="fz-auth__submit" type="submit">ورود به حساب <span aria-hidden="true">←</span></button>
        </form>
        <div class="fz-auth__switch"><span>هنوز حساب ندارید؟</span><a href="{{ route('register', request()->only('intended')) }}">ایجاد حساب جدید</a></div>
        <a class="fz-auth__back" href="{{ route('shop.index') }}">بازگشت به فروشگاه</a>
    </div></div>
</div></section>
@endsection
