@extends('layouts.app')

@section(
    'title',
    'سفارش ' . $order->order_number . ' | فرزین'
)

@section(
    'description',
    'جزئیات سفارش ' . $order->order_number . ' در فرزین.'
)

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')

    @php
        $statusLabels = [
            'pending' => 'در انتظار بررسی',
            'processing' => 'در حال پردازش',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل شده',
            'cancelled' => 'لغو شده',
        ];

        $statusVariants = [
            'pending' => 'warning',
            'processing' => 'warning',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
        ];

        $paymentLabels = [
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'failed' => 'ناموفق',
            'refunded' => 'بازپرداخت شده',
        ];

        $paymentVariants = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
        ];

        $isInstallment =
            $order->payment_method === 'installment'
            || $order->installments->isNotEmpty();

        $paidInstallments = $order->installments->filter(
            fn ($installment) =>
                $installment->status === 'paid'
        );

        $pendingInstallments = $order->installments->filter(
            fn ($installment) =>
                in_array(
                    $installment->status,
                    ['pending', 'overdue']
                )
        );

        $installmentTotal =
            (float) $order->installments->sum('amount');

        $installmentPaid =
            (float) $paidInstallments->sum('amount');

        $installmentRemaining =
            max(
                0,
                $installmentTotal - $installmentPaid
            );

    @endphp

    <div class="bg-[var(--livora-cream)]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-10">

                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('account.index') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            حساب کاربری
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('account.orders.index') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            سفارش‌ها
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        {{ $order->order_number }}
                    </span>

                    </div>

                    <div class="mt-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                ORDER DETAILS
                            </p>

                            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                سفارش {{ $order->order_number }}
                            </h1>

                            <p class="mt-2 text-sm text-[var(--livora-stone)]">
                                {{ $order->created_at ? \App\Helpers\JalaliHelper::format($order->created_at, 'Y/m/d - H:i') : '—' }}
                            </p>

                        </div>

                        <div class="flex flex-wrap gap-2">

                            <x-ui.badge
                                :variant="$statusVariants[$order->status] ?? 'warning'"
                            >
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </x-ui.badge>

                            <x-ui.badge
                                :variant="$paymentVariants[$order->payment_status] ?? 'warning'"
                            >
                                {{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}
                            </x-ui.badge>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        <section>

            <x-layout.container>

                <div class="grid gap-6 py-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:py-10">

                    {{-- =================================================
                         MAIN
                    ================================================== --}}
                    <div class="space-y-6">

                        {{-- Progress --}}
                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                            <div class="mb-7">

                                <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                    ORDER STATUS
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    وضعیت سفارش
                                </h2>

                            </div>

                            @php
                                $steps = [
                                    'pending' => 'ثبت سفارش',
                                    'processing' => 'پردازش',
                                    'shipped' => 'ارسال',
                                    'delivered' => 'تحویل',
                                ];

                                $stepOrder = [
                                    'pending' => 1,
                                    'processing' => 2,
                                    'shipped' => 3,
                                    'delivered' => 4,
                                    'cancelled' => 0,
                                ];

                                $currentStep =
                                    $stepOrder[$order->status] ?? 1;
                            @endphp

                            <div class="grid grid-cols-4 gap-2">

                                @foreach($steps as $step => $label)

                                    @php
                                        $stepNumber = $stepOrder[$step];
                                        $done =
                                            $order->status !== 'cancelled'
                                            && $currentStep >= $stepNumber;
                                    @endphp

                                    <div class="text-center">

                                        <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full {{ $done ? 'bg-[var(--livora-ink)] text-white' : 'bg-[var(--livora-surface)] text-[var(--livora-stone)]' }} text-[10px] font-semibold">
                                            {{ $stepNumber }}
                                        </div>

                                        <p class="mt-2 text-[10px] leading-5 {{ $done ? 'font-medium text-[var(--livora-text)]' : 'text-[var(--livora-stone)]' }}">
                                            {{ $label }}
                                        </p>

                                    </div>

                                @endforeach

                            </div>

                            @if($order->status === 'cancelled')

                                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-xs leading-7 text-red-800">
                                    این سفارش لغو شده است.
                                </div>

                            @endif

                        </div>


                        {{-- Items --}}
                        <div class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">

                            <div class="border-b border-[var(--livora-border)] p-6 sm:p-7">

                                <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                    PRODUCTS
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    محصولات سفارش
                                </h2>

                            </div>

                            <div class="divide-y divide-[var(--livora-border)]">

                                @foreach($order->items as $item)

                                    <div class="flex gap-4 p-5 sm:p-7">

                                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-[var(--livora-surface)]">

                                            @if($item->product?->images?->first()?->url)

                                                <img
                                                    src="{{ $item->product->images->first()->url }}"
                                                    alt="{{ $item->product_name }}"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                >

                                            @else

                                                <div class="flex h-full w-full items-center justify-center text-[10px] tracking-wider text-[var(--livora-stone)]">
                                                    فرزین
                                                </div>

                                            @endif

                                        </div>

                                        <div class="min-w-0 flex-1">

                                            @php
                                                $variantOptions =
                                                    collect($item->variant_options ?? []);
                                            @endphp

                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                                                <div>

                                                    <p class="text-sm font-semibold">
                                                        {{ $item->product_name }}
                                                    </p>

                                                    @if($variantOptions->isNotEmpty())

                                                        <div class="mt-2 flex flex-wrap gap-1.5">

                                                            @foreach($variantOptions as $option)

                                                                <span class="rounded-full border border-[var(--livora-border)] bg-[var(--livora-surface)] px-2.5 py-1 text-[9px] text-[var(--livora-stone)]">

                                                                    {{ $option['name'] ?? $option['type'] ?? 'ویژگی' }}:
                                                                    <span class="font-medium text-[var(--livora-text)]">
                                                                        {{ $option['value'] ?? '—' }}
                                                                    </span>

                                                                </span>

                                                            @endforeach

                                                        </div>

                                                    @endif

                                                    <p class="mt-2 text-xs text-[var(--livora-stone)]">
                                                        تعداد:
                                                        {{ \App\Helpers\PersianNumberHelper::format($item->quantity) }}
                                                    </p>

                                                    @if($item->sku)

                                                        <p class="mt-1 text-[10px] text-[var(--livora-stone)]">
                                                            SKU:
                                                            {{ $item->sku }}
                                                        </p>

                                                    @endif

                                                </div>

                                                <p class="text-sm font-semibold">
                                                    {{ \App\Helpers\PersianNumberHelper::format((float) $item->total) }}
                                                    تومان
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>


                        {{-- Installments --}}
                        @if($isInstallment && $order->installments->isNotEmpty())

                            <div class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">

                                <div class="p-6 sm:p-7">

                                    <p class="text-[10px] uppercase tracking-[0.2em] text-white/45">
                                        INSTALLMENT PLAN
                                    </p>

                                    <h2 class="mt-2 text-xl font-semibold">
                                        برنامه اقساط
                                    </h2>

                                    <p class="mt-2 text-xs leading-7 text-white/50">
                                        وضعیت هر پرداخت و سررسید آن را از اینجا دنبال کن.
                                    </p>

                                </div>

                                <div class="grid grid-cols-1 gap-px bg-white/10 sm:grid-cols-3">

                                    <div class="bg-[var(--livora-ink)] p-5 sm:p-6">

                                        <p class="text-[10px] text-white/45">
                                            کل برنامه
                                        </p>

                                        <p class="mt-2 text-xl font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($installmentTotal) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-white/40">
                                            تومان
                                        </p>

                                    </div>

                                    <div class="bg-[var(--livora-ink)] p-5 sm:p-6">

                                        <p class="text-[10px] text-white/45">
                                            پرداخت‌شده
                                        </p>

                                        <p class="mt-2 text-xl font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($installmentPaid) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-white/40">
                                            تومان
                                        </p>

                                    </div>

                                    <div class="bg-[var(--livora-ink)] p-5 sm:p-6">

                                        <p class="text-[10px] text-white/45">
                                            باقی‌مانده
                                        </p>

                                        <p class="mt-2 text-xl font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($installmentRemaining) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-white/40">
                                            تومان
                                        </p>

                                    </div>

                                </div>


                                <div class="space-y-2 p-5 sm:p-7">

                                    @foreach($order->installments as $installment)

                                        @php
                                            $installmentStatus = match ($installment->status) {
                                                'paid' => 'پرداخت شده',
                                                'overdue' => 'معوق',
                                                'cancelled' => 'لغو شده',
                                                default => 'در انتظار پرداخت',
                                            };

                                            $installmentClass = match ($installment->status) {
                                                'paid' => 'border-emerald-300/20 bg-emerald-300/10',
                                                'overdue' => 'border-red-300/20 bg-red-300/10',
                                                'cancelled' => 'border-white/10 bg-white/5',
                                                default => 'border-white/10 bg-white/5',
                                            };
                                        @endphp

                                        <div class="rounded-2xl border {{ $installmentClass }} p-4">

                                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                                <div>

                                                    <div class="flex flex-wrap items-center gap-2">

                                                        <p class="text-sm font-semibold">

                                                            @if($installment->type === 'cash')
                                                                پیش‌پرداخت
                                                            @else
                                                                قسط / چک {{ \App\Helpers\PersianNumberHelper::format($installment->sequence - 1) }}
                                                            @endif

                                                        </p>

                                                        <span class="rounded-full border border-white/10 px-2.5 py-1 text-[10px]">
                                                        {{ $installmentStatus }}
                                                    </span>

                                                    </div>

                                                    <p class="mt-2 text-[11px] text-white/45">

                                                        @if($installment->due_date)
                                                            سررسید:
                                                            {{ \App\Helpers\JalaliHelper::format($installment->due_date, 'Y/m/d') }}
                                                        @endif

                                                    </p>

                                                    @if($installment->cheque_number)

                                                        <p class="mt-1 text-[10px] text-white/40">
                                                            شماره چک:
                                                            {{ $installment->cheque_number }}
                                                        </p>

                                                    @endif

                                                </div>

                                                <div class="text-left">

                                                    <p class="text-lg font-semibold">
                                                        {{ \App\Helpers\PersianNumberHelper::format((float) $installment->amount) }}
                                                    </p>

                                                    <p class="mt-1 text-[10px] text-white/40">
                                                        تومان
                                                    </p>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endif


                        {{-- Address --}}
                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                            <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                DELIVERY
                            </p>

                            <h2 class="mt-2 text-xl font-semibold">
                                اطلاعات ارسال
                            </h2>

                            <div class="mt-6 grid gap-5 sm:grid-cols-2">

                                <div>
                                    <p class="text-[10px] text-[var(--livora-stone)]">
                                        گیرنده
                                    </p>

                                    <p class="mt-2 text-sm font-medium">
                                        {{ $order->first_name }}
                                        {{ $order->last_name }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-[var(--livora-stone)]">
                                        تلفن
                                    </p>

                                    <p class="mt-2 text-sm font-medium">
                                        {{ $order->phone }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-[var(--livora-stone)]">
                                        استان / شهر
                                    </p>

                                    <p class="mt-2 text-sm font-medium">
                                        {{ $order->province }}
                                        /
                                        {{ $order->city }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-[var(--livora-stone)]">
                                        کد پستی
                                    </p>

                                    <p class="mt-2 text-sm font-medium">
                                        {{ $order->postal_code }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">

                                    <p class="text-[10px] text-[var(--livora-stone)]">
                                        آدرس
                                    </p>

                                    <p class="mt-2 text-sm leading-7">
                                        {{ $order->address }}

                                        @if($order->unit)
                                            ، واحد {{ $order->unit }}
                                        @endif
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         SIDEBAR
                    ================================================== --}}
                    <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">

                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                            <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                SUMMARY
                            </p>

                            <h2 class="mt-2 text-lg font-semibold">
                                خلاصه مالی
                            </h2>

                            <div class="mt-6 space-y-4">

                                <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-[var(--livora-stone)]">
                                    مبلغ محصولات
                                </span>

                                    <span class="text-sm font-medium">
                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->subtotal) }}
                                </span>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-[var(--livora-stone)]">
                                    ارسال
                                </span>

                                    <span class="text-sm font-medium">
                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->shipping_cost) }}
                                </span>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-[var(--livora-stone)]">
                                    تخفیف
                                </span>

                                    <span class="text-sm font-medium">
                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->discount) }}
                                </span>
                                </div>

                            </div>

                            <div class="my-6 h-px bg-[var(--livora-border)]"></div>

                            <div class="flex items-end justify-between">

                            <span class="text-sm text-[var(--livora-stone)]">
                                مبلغ نهایی
                            </span>

                                <div class="text-right">

                                    <p class="text-2xl font-semibold">
                                        {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-[var(--livora-stone)]">
                                        تومان
                                    </p>

                                </div>

                            </div>

                        </div>


                        @if($order->payment_provider || $order->payment_method)

                            <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5 sm:p-6">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    PAYMENT
                                </p>

                                <h3 class="mt-3 text-sm font-semibold">
                                    اطلاعات پرداخت
                                </h3>

                                <div class="mt-4 space-y-3 text-xs">

                                    <div class="flex justify-between gap-4">
                                    <span class="text-[var(--livora-stone)]">
                                        روش
                                    </span>

                                        <span class="font-medium">
                                        {{ $order->payment_method === 'installment' ? 'اقساطی' : 'آنلاین' }}
                                    </span>
                                    </div>

                                    @if($order->payment_provider)

                                        <div class="flex justify-between gap-4">
                                        <span class="text-[var(--livora-stone)]">
                                            سرویس
                                        </span>

                                            <span class="font-medium">
                                            {{ $order->payment_provider }}
                                        </span>
                                        </div>

                                    @endif

                                    <div class="flex justify-between gap-4">
                                    <span class="text-[var(--livora-stone)]">
                                        وضعیت
                                    </span>

                                        <span class="font-medium">
                                        {{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}
                                    </span>
                                    </div>

                                </div>

                            </div>

                        @endif


                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-6">

                            <a
                                href="{{ route('account.orders.index') }}"
                                class="flex w-full items-center justify-center rounded-2xl border border-[var(--livora-border)] px-5 py-3.5 text-sm font-medium transition hover:border-[var(--livora-ink)]"
                            >
                                بازگشت به سفارش‌ها
                            </a>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-3 flex w-full items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                ادامه خرید
                            </a>

                        </div>

                    </aside>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
