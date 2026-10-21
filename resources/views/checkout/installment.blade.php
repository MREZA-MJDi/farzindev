@extends('layouts.app')

@section(
    'title',
    'خرید اقساطی | ' . $order->order_number . ' | فرزین'
)

@section(
    'description',
    'تکمیل فرآیند خرید اقساطی سفارش در فرزین.'
)

@push('seo')

    <meta
        name="robots"
        content="noindex,nofollow"
    >

@endpush


@section('content')

    @php

        $cashInstallment = $order->installments
            ->firstWhere('type', 'cash');

        $chequeInstallments = $order->installments
            ->where('type', 'cheque')
            ->values();

        $cashAmount = (float) (
            $cashInstallment?->amount
            ?? $order->installment_cash_amount
            ?? 0
        );

        $deferredAmount = (float) (
            $order->installment_deferred_amount
            ?? 0
        );

        $cashPaid =
            $cashInstallment?->status === 'paid';

    @endphp


    <div
        x-data="{
        showCheques: false
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
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('checkout.payment', $order) }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            روش پرداخت
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        خرید اقساطی
                    </span>

                    </nav>


                    <div class="mt-8">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            فرزین INSTALLMENT
                        </p>

                        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">
                            تکمیل خرید اقساطی
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">

                            سفارش

                            <span class="font-semibold text-[var(--livora-text)]">
                            {{ $order->order_number }}
                        </span>

                            برای ادامه فرآیند خرید اقساطی آماده است.

                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             MAIN
        ========================================================== --}}

        <x-layout.container>

            <div class="grid gap-6 py-8 sm:py-10 lg:grid-cols-[minmax(0,1fr)_380px]">


                {{-- =================================================
                     LEFT
                ================================================== --}}

                <div class="space-y-6">


                    {{-- =================================================
                         STEP 01
                    ================================================== --}}

                    <section class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">

                        <div class="p-6 sm:p-8">

                            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                                <div>

                                    <p class="text-[10px] uppercase tracking-[0.2em] text-white/40">
                                        STEP 01
                                    </p>

                                    <h2 class="mt-3 text-2xl font-semibold">
                                        پیش‌پرداخت نقدی
                                    </h2>

                                    <p class="mt-3 max-w-xl text-xs leading-7 text-white/50">
                                        مبلغ پیش‌پرداخت ابتدا باید پرداخت و تأیید شود.
                                        پس از آن، اطلاعات چک‌های باقی‌مانده تکمیل خواهد شد.
                                    </p>

                                </div>


                                @if($cashPaid)

                                    <span class="w-fit rounded-full bg-emerald-500/15 px-3 py-1.5 text-[10px] text-emerald-300">
                                    پرداخت شده
                                </span>

                                @else

                                    <span class="w-fit rounded-full bg-white/10 px-3 py-1.5 text-[10px] text-white/50">
                                    در انتظار پرداخت
                                </span>

                                @endif

                            </div>


                            {{-- Cash Amount --}}
                            <div class="mt-7 rounded-3xl border border-white/10 bg-white/[0.06] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-white/35">
                                    CASH PAYMENT
                                </p>

                                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                                    <p class="text-3xl font-bold">

                                        {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}

                                        <span class="text-xs font-normal text-white/45">
                                        تومان
                                    </span>

                                    </p>

                                    <span class="text-xs text-white/45">

                                    {{ \App\Helpers\PersianNumberHelper::format(
                                        (int) $order->installment_cash_percent
                                    ) }}٪ پیش‌پرداخت

                                </span>

                                </div>

                            </div>


                            @if($cashPaid)

                                <div class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4">

                                    <div class="flex items-start gap-3">

                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-300">
                                        ✓
                                    </span>

                                        <div>

                                            <p class="text-xs font-semibold text-emerald-200">
                                                پیش‌پرداخت با موفقیت ثبت شده است.
                                            </p>

                                            <p class="mt-1 text-[10px] leading-6 text-emerald-300/70">
                                                حالا می‌توانید برنامه چک‌ها را بررسی کنید.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            @else

                                <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4">

                                    <p class="text-xs leading-7 text-white/45">
                                        مرحله اتصال مبلغ پیش‌پرداخت به درگاه پرداخت
                                        در Backend در حال تکمیل است.
                                    </p>

                                </div>

                                <a
                                    href="{{ route('checkout.payment', $order) }}"
                                    class="mt-4 flex w-full items-center justify-center rounded-2xl bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-text)] transition hover:bg-[var(--livora-cream)]"
                                >
                                    انتخاب درگاه پیش‌پرداخت
                                </a>

                            @endif

                        </div>

                    </section>


                    {{-- =================================================
                         STEP 02 — CHEQUES
                    ================================================== --}}

                    <section class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">

                        {{-- Toggle Header --}}
                        <button
                            type="button"
                            @click="showCheques = !showCheques"
                            class="group flex w-full items-center justify-between gap-4 border-b border-[var(--livora-border)] p-5 text-right transition-colors duration-200 hover:bg-[var(--livora-surface)] sm:p-7"
                        >

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    STEP 02
                                </p>

                                <h2 class="mt-2 text-xl font-semibold text-[var(--livora-text)]">
                                    اطلاعات چک‌ها
                                </h2>

                                <p class="mt-2 text-xs leading-6 text-[var(--livora-stone)]">
                                    مشاهده برنامه چک‌های باقی‌مانده سفارش
                                </p>

                            </div>


                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[var(--livora-border)] bg-[var(--livora-surface)] text-[var(--livora-text)] transition-transform duration-300 ease-out"
                                :class="showCheques ? 'rotate-180' : 'rotate-0'"
                            >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-4 w-4"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                />

                            </svg>

                        </span>

                        </button>


                        {{-- Animated Content --}}
                        <div
                            x-ref="chequeContent"
                            x-bind:style="
                            showCheques
                                ? 'max-height:' + $refs.chequeContent.scrollHeight + 'px; opacity:1;'
                                : 'max-height:0px; opacity:0;'
                        "
                            class="overflow-hidden transition-[max-height,opacity] duration-500 ease-[cubic-bezier(.22,1,.36,1)]"
                        >

                            <div class="p-5 sm:p-7">

                                @if($chequeInstallments->isNotEmpty())

                                    <div class="space-y-4">

                                        @foreach($chequeInstallments as $index => $installment)

                                            <article
                                                class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5"
                                            >

                                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                                    <div>

                                                        <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                                            CHEQUE {{ $index + 1 }}
                                                        </p>

                                                        <p class="mt-2 text-xl font-semibold text-[var(--livora-text)]">

                                                            {{ \App\Helpers\PersianNumberHelper::format(
                                                                (float) $installment->amount
                                                            ) }}

                                                            <span class="text-xs font-normal text-[var(--livora-stone)]">
                                                            تومان
                                                        </span>

                                                        </p>

                                                        <p class="mt-2 text-xs text-[var(--livora-stone)]">

                                                            سررسید:

                                                            {{ $installment->due_date ? \App\Helpers\JalaliHelper::format($installment->due_date, 'Y/m/d') : 'تعیین نشده' }}

                                                        </p>

                                                    </div>


                                                    <span class="w-fit rounded-full bg-[var(--livora-white)] px-3 py-1.5 text-[10px] text-[var(--livora-stone)]">
                                                    در انتظار ثبت
                                                </span>

                                                </div>


                                                {{-- Future cheque data --}}
                                                <div class="mt-6 grid gap-4 sm:grid-cols-3">

                                                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                                        <p class="text-[10px] text-[var(--livora-stone)]">
                                                            شماره چک
                                                        </p>

                                                        <p class="mt-2 text-xs font-medium text-[var(--livora-text)]">
                                                            بعداً ثبت می‌شود
                                                        </p>

                                                    </div>


                                                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                                        <p class="text-[10px] text-[var(--livora-stone)]">
                                                            بانک
                                                        </p>

                                                        <p class="mt-2 text-xs font-medium text-[var(--livora-text)]">
                                                            بعداً ثبت می‌شود
                                                        </p>

                                                    </div>


                                                    <div class="rounded-2xl border border-dashed border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                                        <p class="text-[10px] text-[var(--livora-stone)]">
                                                            تصویر چک
                                                        </p>

                                                        <p class="mt-2 text-xs font-medium text-[var(--livora-text)]">
                                                            بعداً فعال می‌شود
                                                        </p>

                                                    </div>

                                                </div>


                                                <div class="mt-4 rounded-2xl bg-white/60 p-4">

                                                    <p class="text-[10px] leading-6 text-[var(--livora-stone)]">
                                                        در نسخه فعلی هنوز آپلود و ذخیره اطلاعات چک فعال نشده است.
                                                        پس از تکمیل Backend، همین بخش به فرم واقعی ثبت شماره چک،
                                                        بانک، صاحب چک و تصویر متصل می‌شود.
                                                    </p>

                                                </div>

                                            </article>

                                        @endforeach

                                    </div>

                                @else

                                    <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-6 text-center">

                                        <p class="text-sm font-medium text-[var(--livora-text)]">
                                            قسط چکی برای این طرح وجود ندارد.
                                        </p>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </section>


                    {{-- =================================================
                         INFORMATION
                    ================================================== --}}

                    <section class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-[var(--livora-text)]">
                                i
                            </div>

                            <div>

                                <h2 class="text-sm font-semibold">
                                    روند خرید اقساطی
                                </h2>

                                <div class="mt-3 space-y-2 text-xs leading-7 text-[var(--livora-stone)]">

                                    <p>
                                        ۱. مبلغ پیش‌پرداخت پرداخت می‌شود.
                                    </p>

                                    <p>
                                        ۲. اطلاعات چک‌های باقی‌مانده ثبت می‌شود.
                                    </p>

                                    <p>
                                        ۳. مدارک توسط فروشگاه بررسی می‌شود.
                                    </p>

                                    <p>
                                        ۴. پس از تأیید، سفارش وارد مرحله نهایی پردازش می‌شود.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </section>

                </div>


                {{-- =================================================
                     SUMMARY
                ================================================== --}}

                <aside class="lg:sticky lg:top-24 lg:self-start">

                    <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            INSTALLMENT SUMMARY
                        </p>

                        <h2 class="mt-2 text-lg font-semibold">
                            خلاصه طرح
                        </h2>


                        <div class="mt-6 space-y-4">

                            {{-- Total --}}
                            <div class="rounded-2xl bg-[var(--livora-surface)] p-4">

                                <p class="text-[10px] text-[var(--livora-stone)]">
                                    مبلغ کل سفارش
                                </p>

                                <p class="mt-2 text-xl font-bold">

                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}

                                    <span class="text-[10px] font-normal">
                                    تومان
                                </span>

                                </p>

                            </div>


                            {{-- Cash --}}
                            <div class="flex items-center justify-between border-b border-[var(--livora-border)] pb-4">

                            <span class="text-xs text-[var(--livora-stone)]">
                                پیش‌پرداخت
                            </span>

                                <strong class="text-sm">

                                    {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}

                                    تومان

                                </strong>

                            </div>


                            {{-- Deferred --}}
                            <div class="flex items-center justify-between border-b border-[var(--livora-border)] pb-4">

                            <span class="text-xs text-[var(--livora-stone)]">
                                باقی‌مانده
                            </span>

                                <strong class="text-sm">

                                    {{ \App\Helpers\PersianNumberHelper::format($deferredAmount) }}

                                    تومان

                                </strong>

                            </div>


                            {{-- Cheques --}}
                            <div class="flex items-center justify-between">

                            <span class="text-xs text-[var(--livora-stone)]">
                                تعداد چک
                            </span>

                                <strong class="text-sm">
                                    {{ $chequeInstallments->count() }}
                                </strong>

                            </div>

                        </div>


                        {{-- Status --}}
                        <div class="mt-6 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

                            @if($cashPaid)

                                <p class="text-[10px] leading-6 text-emerald-700">
                                    پیش‌پرداخت تأیید شده است و سفارش آماده تکمیل مراحل بعدی است.
                                </p>

                            @else

                                <p class="text-[10px] leading-6 text-[var(--livora-stone)]">
                                    پیش‌پرداخت هنوز انجام نشده است.
                                </p>

                            @endif

                        </div>


                        <a
                            href="{{ route('checkout.payment', $order) }}"
                            class="mt-5 flex w-full items-center justify-center rounded-2xl border border-[var(--livora-border)] px-5 py-3.5 text-sm font-medium transition hover:border-[var(--livora-ink)]"
                        >
                            بازگشت به روش‌های پرداخت
                        </a>

                    </div>

                </aside>

            </div>

        </x-layout.container>

    </div>

@endsection
