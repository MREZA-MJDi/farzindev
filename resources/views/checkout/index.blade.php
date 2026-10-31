@extends('layouts.app')

@section('title', 'تسویه حساب | فرزین')

@section(
    'description',
    'تکمیل اطلاعات سفارش و ادامه فرآیند پرداخت در فرزین.'
)

@section('content')

    <div class="overflow-hidden bg-[var(--livora-cream)]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >
                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('cart.index') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            سبد خرید
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        تسویه حساب
                    </span>
                    </nav>

                    <div class="mt-8">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            CHECKOUT
                        </p>

                        <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                            تکمیل سفارش
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                            اطلاعات تحویل را وارد کنید تا سفارش شما ثبت شود و
                            به صفحه انتخاب روش پرداخت منتقل شوید.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        <x-layout.container>

            <div class="py-8 sm:py-10 lg:py-14">

                {{-- =================================================
                     ERRORS
                ================================================== --}}

                @if($errors->any())

                    <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white">
                                !
                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-red-800">
                                    اطلاعات سفارش کامل نیست.
                                </p>

                                <p class="mt-1 text-xs leading-6 text-red-700">
                                    موارد زیر را بررسی کنید:
                                </p>

                                <ul class="mt-3 space-y-2 text-xs leading-6 text-red-700">

                                    @foreach($errors->all() as $error)

                                        <li class="flex items-start gap-2">
                                            <span>•</span>
                                            <span>{{ $error }}</span>
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- =================================================
                     SESSION ERROR
                ================================================== --}}

                @if(session('error'))

                    <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white">
                                !
                            </div>

                            <div>

                                <p class="text-sm font-semibold text-red-800">
                                    {{ session('error') }}
                                </p>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- =================================================
                     FORM
                ================================================== --}}

                <form
                    action="{{ route('checkout.place') }}"
                    method="POST"
                    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px]"
                >

                    @csrf


                    {{-- =================================================
                         LEFT
                    ================================================== --}}

                    <div class="space-y-6">

                        {{-- Contact --}}
                        <section class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    01 · CONTACT
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    اطلاعات تماس
                                </h2>

                                <p class="mt-2 text-xs leading-6 text-[var(--livora-stone)]">
                                    اطلاعاتی که برای ثبت و پیگیری سفارش استفاده می‌شود.
                                </p>

                            </div>


                            <div class="mt-7 grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    name="first_name"
                                    label="نام"
                                    value="{{ old('first_name') }}"
                                    error="{{ $errors->first('first_name') }}"
                                    required
                                />

                                <x-ui.input
                                    name="last_name"
                                    label="نام خانوادگی"
                                    value="{{ old('last_name') }}"
                                    error="{{ $errors->first('last_name') }}"
                                    required
                                />

                                <x-ui.input
                                    name="phone"
                                    label="شماره موبایل"
                                    value="{{ old('phone') }}"
                                    error="{{ $errors->first('phone') }}"
                                    inputmode="tel"
                                    required
                                />

                                <x-ui.input
                                    type="email"
                                    name="email"
                                    label="ایمیل"
                                    value="{{ old('email', auth()->user()->email) }}"
                                    error="{{ $errors->first('email') }}"
                                    required
                                />

                            </div>

                        </section>


                        {{-- Address --}}
                        <section class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    02 · DELIVERY
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    آدرس تحویل
                                </h2>

                                <p class="mt-2 text-xs leading-6 text-[var(--livora-stone)]">
                                    سفارش به این آدرس ارسال خواهد شد.
                                </p>

                            </div>


                            <div class="mt-7 grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    name="province"
                                    label="استان"
                                    value="{{ old('province') }}"
                                    error="{{ $errors->first('province') }}"
                                    required
                                />

                                <x-ui.input
                                    name="city"
                                    label="شهر"
                                    value="{{ old('city') }}"
                                    error="{{ $errors->first('city') }}"
                                    required
                                />


                                <div class="sm:col-span-2">

                                    <x-ui.input
                                        name="address"
                                        label="آدرس کامل"
                                        value="{{ old('address') }}"
                                        error="{{ $errors->first('address') }}"
                                        required
                                    />

                                </div>


                                <x-ui.input
                                    name="postal_code"
                                    label="کد پستی"
                                    value="{{ old('postal_code') }}"
                                    error="{{ $errors->first('postal_code') }}"
                                    inputmode="numeric"
                                    required
                                />

                                <x-ui.input
                                    name="unit"
                                    label="واحد"
                                    value="{{ old('unit') }}"
                                    error="{{ $errors->first('unit') }}"
                                />


                                <div class="sm:col-span-2">

                                    <label
                                        for="notes"
                                        class="mb-2 block text-xs font-medium text-[var(--livora-text)]"
                                    >
                                        توضیحات سفارش
                                    </label>

                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="4"
                                        placeholder="توضیحات مربوط به ارسال، ساختمان، هماهنگی و..."
                                        class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3.5 text-sm text-[var(--livora-text)] outline-none transition placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-ink)]"
                                    >{{ old('notes') }}</textarea>

                                    @error('notes')

                                    <p class="mt-2 text-[11px] text-red-600">
                                        {{ $message }}
                                    </p>

                                    @enderror

                                </div>

                            </div>

                        </section>

                    </div>


                    {{-- =================================================
                         SUMMARY
                    ================================================== --}}

                    <aside>

                        <div class="sticky top-24 rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                            <div class="flex items-center justify-between gap-4">

                                <div>

                                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                        ORDER SUMMARY
                                    </p>

                                    <h2 class="mt-2 text-lg font-semibold">
                                        خلاصه سفارش
                                    </h2>

                                </div>

                                <span class="rounded-full bg-[var(--livora-surface)] px-3 py-1.5 text-[10px] text-[var(--livora-stone)]">
                                {{ $cart->items->count() }}
                                آیتم
                            </span>

                            </div>


                            <div class="mt-6 space-y-3">

                                @foreach($cart->items as $item)

                                    @php
                                        $image =
                                            $item->product?->images?->first()?->url;

                                        $variantOptions =
                                            collect($item->variant_options ?? []);
                                    @endphp

                                    <div class="flex gap-3 rounded-2xl bg-[var(--livora-surface)] p-3">

                                        <div class="h-16 w-14 shrink-0 overflow-hidden rounded-xl bg-[var(--livora-white)]">

                                            @if($image)

                                                <img
                                                    src="{{ $image }}"
                                                    alt="{{ $item->product->name }}"
                                                    class="h-full w-full object-cover"
                                                >

                                            @else

                                                <div class="flex h-full w-full items-center justify-center text-[8px] tracking-widest text-[var(--livora-stone)]">
                                                    LV
                                                </div>

                                            @endif

                                        </div>


                                        <div class="min-w-0 flex-1">

                                            <p class="truncate text-xs font-semibold">
                                                {{ $item->product->name }}
                                            </p>

                                            @if($variantOptions->isNotEmpty())

                                                <div class="mt-1 flex flex-wrap gap-1.5">

                                                    @foreach($variantOptions as $option)

                                                        <span class="rounded-full border border-[var(--livora-border)] px-2 py-1 text-[9px] text-[var(--livora-stone)]">

                                                            {{ $option['name'] ?? $option['type'] ?? 'ویژگی' }}:
                                                            <span class="font-medium text-[var(--livora-text)]">
                                                                {{ $option['value'] ?? '—' }}
                                                            </span>

                                                        </span>

                                                    @endforeach

                                                </div>

                                            @endif

                                            <p class="mt-2 text-[10px] text-[var(--livora-stone)]">
                                                تعداد:
                                                {{ \App\Helpers\PersianNumberHelper::format($item->quantity) }}
                                            </p>

                                            <p class="mt-2 text-[11px] font-semibold">
                                                {{ \App\Helpers\PersianNumberHelper::format(
                                                    (float) $item->unit_price * $item->quantity
                                                ) }}
                                                تومان
                                            </p>

                                        </div>

                                    </div>

                                @endforeach

                            </div>


                            {{-- Totals --}}
                            <div class="mt-6 border-t border-[var(--livora-border)] pt-5">

                                <div class="flex items-center justify-between text-xs">

                                <span class="text-[var(--livora-stone)]">
                                    مجموع
                                </span>

                                    <span class="font-semibold">
                                    {{ \App\Helpers\PersianNumberHelper::format($cart->subtotal()) }}
                                    تومان
                                </span>

                                </div>

                                <div class="mt-3 flex items-center justify-between text-xs">

                                <span class="text-[var(--livora-stone)]">
                                    ارسال
                                </span>

                                    <span class="font-semibold">
                                    رایگان
                                </span>

                                </div>


                                <div class="mt-5 border-t border-[var(--livora-border)] pt-5">

                                    <div class="flex items-end justify-between gap-4">

                                        <div>

                                            <p class="text-sm font-semibold">
                                                مبلغ نهایی
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--livora-stone)]">
                                                قبل از انتخاب روش پرداخت
                                            </p>

                                        </div>

                                        <div class="text-left">

                                            <p class="text-2xl font-bold tracking-tight">
                                                {{ \App\Helpers\PersianNumberHelper::format($cart->subtotal()) }}
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--livora-stone)]">
                                                تومان
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Submit --}}
                            <button
                                type="submit"
                                class="mt-6 flex w-full items-center justify-center gap-3 rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >

                            <span>
                                ثبت سفارش و ادامه پرداخت
                            </span>

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2.3" /><path d="M2.5 10h19m-15 5h4" /></svg>

                            </button>


                            <div class="mt-4 flex items-start gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-white)]">
                                ✓
                            </span>

                                <p class="text-[10px] leading-6 text-[var(--livora-stone)]">
                                    پس از ثبت سفارش، به صفحه انتخاب روش پرداخت
                                    منتقل می‌شوید.
                                </p>

                            </div>

                        </div>

                    </aside>

                </form>

            </div>

        </x-layout.container>

    </div>

@endsection
