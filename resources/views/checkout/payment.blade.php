@extends('layouts.app')

@section(
    'title',
    'انتخاب روش پرداخت | ' . $order->order_number . ' | فرزین'
)

@section(
    'description',
    'انتخاب روش پرداخت سفارش در فرزین؛ پرداخت آنلاین یا خرید اقساطی با شرایط مشخص.'
)

@section(
    'canonical',
    route('checkout.payment', $order)
)

@push('seo')

    <meta
        name="robots"
        content="noindex,nofollow"
    >

@endpush


@section('content')

    @php

        /*
        |--------------------------------------------------------------------------
        | Gateway Configuration
        |--------------------------------------------------------------------------
        */

        $gateways = [
            [
                'key' => 'digipay',
                'name' => 'DigiPay',
                'fa_name' => 'دیجی‌پی',
                'description' => 'پرداخت از طریق درگاه دیجی‌پی',
                'enabled' => (bool) config('payment.digipay.enabled', false),
                'badge' => 'DIGIPAY',
            ],

            [
                'key' => 'snappay',
                'name' => 'SnappPay',
                'fa_name' => 'اسنپ‌پی',
                'description' => 'پرداخت از طریق درگاه اسنپ‌پی',
                'enabled' => (bool) config('payment.snappay.enabled', false),
                'badge' => 'SNAPPAY',
            ],

            [
                'key' => 'torobpay',
                'name' => 'TorobPay',
                'fa_name' => 'ترب‌پی',
                'description' => 'پرداخت از طریق درگاه ترب‌پی',
                'enabled' => (bool) config('payment.torobpay.enabled', false),
                'badge' => 'TOROBPAY',
            ],
        ];

        $enabledGateways = collect($gateways)
            ->where('enabled', true)
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Order Totals
        |--------------------------------------------------------------------------
        */

        $orderTotal = (float) $order->total;


        /*
        |--------------------------------------------------------------------------
        | Existing Installment Plan
        |--------------------------------------------------------------------------
        */

        $hasInstallmentPlan =
            (bool) $order->installment_enabled
            && $order->installments
                ->isNotEmpty();

        $cashInstallment =
            $order->installments
                ->firstWhere('type', 'cash');

        $chequeInstallments =
            $order->installments
                ->where('type', 'cheque')
                ->values();

        $cashPercent =
            (int) (
                $order->installment_cash_percent
                ?? 0
            );

        $cashAmount =
            $cashInstallment
                ? (float) $cashInstallment->amount
                : null;

        $deferredAmount =
            (float) (
                $order->installment_deferred_amount
                ?? 0
            );

        $chequeCount =
            $chequeInstallments->count();

        $intervalMonths =
            (int) (
                $order->installment_interval_months
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | Payment Status
        |--------------------------------------------------------------------------
        */

        $isPaid =
            $order->payment_status === 'paid';


        /*
        |--------------------------------------------------------------------------
        | Current Payment
        |--------------------------------------------------------------------------
        */

        $latestPayment =
            $order->latestPayment;


    @endphp


    <div
        x-data="{
        selected: null,
        installmentOpen: {{ $hasInstallmentPlan ? 'true' : 'false' }},

        selectOnline(gateway) {
            this.selected = gateway;
            this.installmentOpen = false;
        },

        selectInstallment() {
            this.selected = 'livora-installment';
            this.installmentOpen = true;
        }
    }"
        class="overflow-hidden bg-[var(--livora-cream)]"
    >


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
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('cart.index') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            سبد خرید
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        پرداخت
                    </span>

                    </nav>


                    <div class="mt-8 max-w-3xl">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            PAYMENT
                        </p>

                        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">
                            روش پرداخت را انتخاب کنید.
                        </h1>

                        <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                            سفارش
                            <span class="font-medium text-[var(--livora-ink)]">
                            {{ $order->order_number }}
                        </span>
                            برای پرداخت آماده است.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             MAIN
        ========================================================== --}}

        <x-layout.container>

            <div class="grid gap-6 py-8 sm:py-10 lg:grid-cols-[1fr_380px] lg:py-14">


                {{-- =================================================
                     PAYMENT METHODS
                ================================================== --}}

                <div class="space-y-5">


                    {{-- Success --}}
                    @if(session('success'))

                        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">

                            <div class="flex items-start gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                ✓
                            </span>

                                <div>

                                    <p class="text-sm font-semibold text-emerald-800">
                                        {{ session('success') }}
                                    </p>

                                    <p class="mt-1 text-xs leading-6 text-emerald-700">
                                        می‌توانید روش پرداخت مناسب سفارش خود را انتخاب کنید.
                                    </p>

                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- Error --}}
                    @if(session('error'))

                        <div class="rounded-3xl border border-red-200 bg-red-50 p-5">

                            <div class="flex items-start gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white">
                                !
                            </span>

                                <div>

                                    <p class="text-sm font-semibold text-red-800">
                                        {{ session('error') }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- =================================================
                         ONLINE PAYMENT
                    ================================================== --}}

                    <section class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    ONLINE PAYMENT
                                </p>

                                <h2 class="mt-3 text-2xl font-semibold tracking-tight">
                                    پرداخت آنلاین
                                </h2>

                                <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                    یکی از درگاه‌های فعال را انتخاب کنید.
                                </p>

                            </div>

                            <span class="w-fit rounded-full border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-1.5 text-[10px] text-[var(--livora-stone)]">
                            مبلغ کامل سفارش
                        </span>

                        </div>


                        <div class="mt-7 grid gap-3 md:grid-cols-3">

                            @foreach($gateways as $gateway)

                                <div
                                    x-data
                                    @click="{{ $gateway['enabled'] ? "selectOnline('{$gateway['key']}')" : '' }}"
                                    @class([
                                        'relative rounded-3xl border p-5 transition duration-300',
                                        'cursor-pointer' => $gateway['enabled'],
                                        'cursor-not-allowed opacity-50' => ! $gateway['enabled'],
                                        'border-[var(--livora-ink)] bg-[var(--livora-surface)] shadow-sm'
                                            => $gateway['enabled'],
                                        'border-[var(--livora-border)] bg-[var(--livora-white)]'
                                            => ! $gateway['enabled'],
                                    ])
                                    :class="
                                    selected === '{{ $gateway['key'] }}'
                                        ? 'ring-2 ring-[var(--livora-accent)] ring-offset-2'
                                        : ''
                                "
                                >

                                    <div class="flex items-start justify-between gap-3">

                                        {{-- Logo --}}
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--livora-ink)] text-[9px] font-bold tracking-[0.08em] text-white"
                                        >
                                            {{ $gateway['badge'] }}
                                        </div>


                                        {{-- Radio --}}
                                        <div
                                            class="flex h-5 w-5 items-center justify-center rounded-full border"
                                            :class="
                                            selected === '{{ $gateway['key'] }}'
                                                ? 'border-[var(--livora-ink)] bg-[var(--livora-ink)]'
                                                : 'border-[var(--livora-border)]'
                                        "
                                        >
                                        <span
                                            x-show="selected === '{{ $gateway['key'] }}'"
                                            class="h-2 w-2 rounded-full bg-white"
                                        ></span>
                                        </div>

                                    </div>


                                    <p class="mt-6 text-sm font-semibold">
                                        {{ $gateway['fa_name'] }}
                                    </p>

                                    <p class="mt-2 text-[11px] leading-6 text-[var(--livora-stone)]">
                                        {{ $gateway['description'] }}
                                    </p>


                                    @if($gateway['enabled'])

                                        <span class="mt-4 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-medium text-emerald-700">
                                        فعال
                                    </span>

                                    @else

                                        <span class="mt-4 inline-flex rounded-full bg-[var(--livora-surface)] px-2.5 py-1 text-[9px] text-[var(--livora-stone)]">
                                        به‌زودی
                                    </span>

                                    @endif

                                </div>

                            @endforeach

                        </div>


                        @if($enabledGateways->isNotEmpty())

                            <div class="mt-6">

                                <form
                                    method="POST"
                                    action="{{ route('checkout.payment.installment', $order) }}"
                                    x-ref="onlineForm"
                                >

                                    @csrf

                                    <input
                                        type="hidden"
                                        name="gateway"
                                        :value="selected"
                                    >


                                    <button
                                        type="submit"
                                        @click="
                                        if (
                                            !['digipay', 'snappay', 'torobpay'].includes(selected)
                                        ) {
                                            $event.preventDefault();
                                        }
                                    "
                                        :disabled="!['digipay', 'snappay', 'torobpay'].includes(selected)"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)] disabled:cursor-not-allowed disabled:opacity-40"
                                    >

                                    <span>
                                        ادامه به درگاه
                                    </span>

                                        <span class="text-white/60">
                                        ←
                                    </span>

                                    </button>

                                </form>

                            </div>

                        @else

                            <div class="mt-6 rounded-2xl bg-[var(--livora-surface)] p-4">

                                <p class="text-xs leading-7 text-[var(--livora-stone)]">
                                    در حال حاضر هیچ درگاه آنلاینی فعال نیست.
                                </p>

                            </div>

                        @endif

                    </section>


                    {{-- =================================================
                         فرزین INSTALLMENT
                    ================================================== --}}

                    <section
                        class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-ink)] text-white"
                    >

                        <div class="p-5 sm:p-7">

                            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                                <div>

                                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-white/40">
                                        فرزین INSTALLMENT
                                    </p>

                                    <h2 class="mt-3 text-2xl font-semibold">
                                        خرید اقساطی با چک
                                    </h2>

                                    <p class="mt-3 max-w-xl text-xs leading-7 text-white/50">
                                        بخشی از مبلغ را نقد پرداخت کنید و باقی‌مانده را طبق برنامه چک تسویه کنید.
                                        شرایط این طرح از تنظیمات محصولات سفارش محاسبه می‌شود.
                                    </p>

                                </div>


                                @if($hasInstallmentPlan)

                                    <span class="w-fit rounded-full bg-white/10 px-3 py-1.5 text-[10px] text-white/60">
                                    طرح آماده است
                                </span>

                                @else

                                    <button
                                        type="button"
                                        @click="selectInstallment()"
                                        class="w-fit rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] text-white/70 transition hover:bg-white/10"
                                    >
                                        انتخاب خرید اقساطی
                                    </button>

                                @endif

                            </div>


                            {{-- Plan --}}
                            @if($hasInstallmentPlan)

                                <div class="mt-8 grid gap-3 sm:grid-cols-3">

                                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">

                                        <p class="text-[10px] uppercase tracking-[0.15em] text-white/35">
                                            CASH
                                        </p>

                                        <p class="mt-3 text-2xl font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-white/45">
                                            تومان · {{ $cashPercent }}٪ پیش‌پرداخت
                                        </p>

                                    </div>


                                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">

                                        <p class="text-[10px] uppercase tracking-[0.15em] text-white/35">
                                            CHEQUES
                                        </p>

                                        <p class="mt-3 text-2xl font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($chequeCount) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-white/45">
                                            فقره چک
                                        </p>

                                    </div>


                                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">

                                        <p class="text-[10px] uppercase tracking-[0.15em] text-white/35">
                                            INTERVAL
                                        </p>

                                        <p class="mt-3 text-2xl font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($intervalMonths) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-white/45">
                                            ماه فاصله سررسید
                                        </p>

                                    </div>

                                </div>


                                {{-- Cheques --}}
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">

                                    @foreach($chequeInstallments as $installment)

                                        <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">

                                            <div class="flex items-center justify-between gap-3">

                                            <span class="text-[10px] text-white/40">
                                                چک {{ $installment->sequence - 1 }}
                                            </span>

                                                <span class="text-[10px] text-white/40">
                                                سررسید
                                            </span>

                                            </div>

                                            <div class="mt-3 flex items-center justify-between gap-3">

                                                <strong class="text-sm">
                                                    {{ \App\Helpers\PersianNumberHelper::format((float) $installment->amount) }}
                                                    تومان
                                                </strong>

                                                <span class="rounded-full bg-white/10 px-2.5 py-1 text-[9px] text-white/60">
                                                {{ $installment->due_date
                                                    ? \App\Helpers\JalaliHelper::format($installment->due_date, 'Y/m/d')
                                                    : 'بدون تاریخ'
                                                }}
                                            </span>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>


                                {{-- Continue installment --}}
                                <div class="mt-6">

                                    <form
                                        action="{{ route('checkout.payment.livora-installment', $order) }}"
                                        method="POST"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]"
                                        >
                                        <span>
                                            ادامه فرآیند خرید اقساطی
                                        </span>

                                            <span>
                                            ←
                                        </span>
                                        </button>

                                    </form>

                                </div>

                            @else

                                <div class="mt-7 rounded-3xl border border-white/10 bg-white/[0.05] p-5">

                                    <div class="flex items-start gap-4">

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                                            ✓
                                        </div>

                                        <div>

                                            <p class="text-sm font-semibold">
                                                شرایط اقساطی را قبل از ثبت نهایی ببینید.
                                            </p>

                                            <p class="mt-2 text-xs leading-7 text-white/45">
                                                پس از انتخاب خرید اقساطی، سیستم شرایط محصولات سفارش را بررسی
                                                و درصد پیش‌پرداخت، مبلغ چک‌ها و سررسیدها را ایجاد می‌کند.
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                <form
                                    action="{{ route('checkout.payment.livora-installment', $order) }}"
                                    method="POST"
                                    class="mt-5"
                                >

                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-6 py-4 text-sm font-medium text-white transition hover:bg-white/15"
                                    >
                                        محاسبه و ایجاد طرح اقساطی
                                    </button>

                                </form>

                            @endif

                        </div>

                    </section>

                </div>


                {{-- =================================================
                     ORDER SUMMARY
                ================================================== --}}

                <aside class="lg:sticky lg:top-24 lg:self-start">

                    <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                        <div class="flex items-center justify-between gap-4">

                            <div>

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    ORDER SUMMARY
                                </p>

                                <h2 class="mt-2 text-lg font-semibold">
                                    خلاصه سفارش
                                </h2>

                            </div>

                            <span class="rounded-full bg-[var(--livora-surface)] px-3 py-1.5 text-[10px] text-[var(--livora-stone)]">
                            {{ $order->order_number }}
                        </span>

                        </div>


                        {{-- Items --}}
                        <div class="mt-7 space-y-3">

                            @foreach($order->items as $item)

                                @php
                                    $itemImage =
                                        $item->product?->images?->first()?->url;

                                    $variantOptions =
                                        collect($item->variant_options ?? []);
                                @endphp

                                <div class="flex gap-3 rounded-2xl bg-[var(--livora-surface)] p-3">

                                    <div class="h-16 w-14 shrink-0 overflow-hidden rounded-xl bg-[var(--livora-white)]">

                                        @if($itemImage)

                                            <img
                                                src="{{ $itemImage }}"
                                                alt="{{ $item->product_name }}"
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
                                            {{ $item->product_name }}
                                        </p>

                                        @if($variantOptions->isNotEmpty())

                                            <div class="mt-1 flex flex-wrap gap-1.5">

                                                @foreach($variantOptions as $option)

                                                    <span class="rounded-full border border-[var(--livora-border)] px-2 py-1 text-[9px] text-[var(--livora-stone)]">

                                                        {{ $option['name'] ?? $option['type'] ?? 'ویژگی' }}:
                                                        <span class="font-medium text-[var(--livora-ink)]">
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
                                            {{ \App\Helpers\PersianNumberHelper::format((float) $item->total) }}
                                            تومان
                                        </p>

                                    </div>

                                </div>

                            @endforeach

                        </div>


                        {{-- Total --}}
                        <div class="mt-6 space-y-3 border-t border-[var(--livora-border)] pt-5">

                            <div class="flex items-center justify-between gap-4 text-xs">

                            <span class="text-[var(--livora-stone)]">
                                مبلغ سفارش
                            </span>

                                <span class="font-medium">
                                {{ \App\Helpers\PersianNumberHelper::format((float) $order->subtotal) }}
                                تومان
                            </span>

                            </div>


                            <div class="flex items-center justify-between gap-4 text-xs">

                            <span class="text-[var(--livora-stone)]">
                                ارسال
                            </span>

                                <span class="font-medium">
                                {{ \App\Helpers\PersianNumberHelper::format((float) $order->shipping_cost) }}
                                تومان
                            </span>

                            </div>


                            @if((float) $order->discount > 0)

                                <div class="flex items-center justify-between gap-4 text-xs">

                                <span class="text-[var(--livora-stone)]">
                                    تخفیف
                                </span>

                                    <span class="font-medium text-emerald-600">
                                    -
                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->discount) }}
                                    تومان
                                </span>

                                </div>

                            @endif


                            <div class="flex items-end justify-between gap-4 border-t border-[var(--livora-border)] pt-4">

                            <span class="text-sm font-semibold">
                                مبلغ نهایی
                            </span>

                                <div class="text-left">

                                    <p class="text-2xl font-bold tracking-tight">
                                        {{ \App\Helpers\PersianNumberHelper::format($orderTotal) }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-[var(--livora-stone)]">
                                        تومان
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Installment Summary --}}
                        @if($hasInstallmentPlan)

                            <div class="mt-5 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-accent)]">
                                    INSTALLMENT SUMMARY
                                </p>

                                <div class="mt-4 flex items-center justify-between gap-3">

                                <span class="text-xs text-[var(--livora-stone)]">
                                    امروز
                                </span>

                                    <strong class="text-sm">
                                        {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}
                                        تومان
                                    </strong>

                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3">

                                <span class="text-xs text-[var(--livora-stone)]">
                                    باقی‌مانده
                                </span>

                                    <strong class="text-sm">
                                        {{ \App\Helpers\PersianNumberHelper::format($deferredAmount) }}
                                        تومان
                                    </strong>

                                </div>

                            </div>

                        @endif


                        {{-- Security --}}
                        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-[10px]">
                            ✓
                        </span>

                            <p class="text-[10px] leading-6 text-[var(--livora-stone)]">
                                مبلغ پرداخت از اطلاعات ثبت‌شده سفارش محاسبه می‌شود و
                                مبلغ ارسالی از سمت مرورگر مبنای پرداخت نیست.
                            </p>

                        </div>

                    </div>

                </aside>

            </div>

        </x-layout.container>

    </div>

@endsection
