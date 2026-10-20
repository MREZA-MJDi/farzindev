@extends('admin.layouts.app')

@section('title', 'داشبورد')

@section('page_title', 'داشبورد')

@section(
    'meta_description',
    'داشبورد مدیریت فروشگاه فرزین'
)

@section('content')

    @php

        /*
        |--------------------------------------------------------------------------
        | Dashboard Helpers
        |--------------------------------------------------------------------------
        */

        $statusLabels = [
            'pending' => 'در انتظار',
            'processing' => 'در پردازش',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل شده',
            'cancelled' => 'لغو شده',
        ];

        $statusColors = [
            'pending' => [
                'text' => 'text-amber-600',
                'bg' => 'bg-amber-50',
            ],

            'processing' => [
                'text' => 'text-sky-600',
                'bg' => 'bg-sky-50',
            ],

            'shipped' => [
                'text' => 'text-indigo-600',
                'bg' => 'bg-indigo-50',
            ],

            'delivered' => [
                'text' => 'text-emerald-600',
                'bg' => 'bg-emerald-50',
            ],

            'cancelled' => [
                'text' => 'text-red-600',
                'bg' => 'bg-red-50',
            ],
        ];

        $statusTotal =
            array_sum($orderStatus);

        $deliveredOrders =
            $orderStatus['delivered'] ?? 0;

        $deliveredPercent =
            $statusTotal > 0
                ? round(
                    ($deliveredOrders / $statusTotal) * 100
                )
                : 0;

        $paidOrderPercent =
            $totalOrders > 0
                ? round(
                    ($paidOrders / $totalOrders) * 100
                )
                : 0;

        $installmentPaidPercent =
            $installmentOrders > 0
                ? round(
                    ($installmentPaidOrders / $installmentOrders) * 100
                )
                : 0;

        $maxMonthlyRevenue =
            max(
                1,
                $monthlyRevenue->max('revenue')
            );

        $maxProductQuantity =
            max(
                1,
                $topProducts->max('quantity')
            );

    @endphp


    <div class="space-y-8">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">

            <div>

                <div class="mb-2 text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--admin-accent)]">
                    فرزین / ADMIN
                </div>

                <h1 class="admin-title">
                    داشبورد مدیریت
                </h1>

                <p class="admin-subtitle mt-2">
                    نمای زنده فروشگاه، سفارش‌ها، درآمد و عملکرد محصولات.
                </p>

            </div>

            <div class="flex flex-wrap items-center gap-3">

            <span class="rounded-full border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-2 text-xs text-[var(--admin-muted)]">
                {{ \App\Helpers\JalaliHelper::format(now(), 'l، d F Y') }}
            </span>

                <a
                    href="{{ route('admin.products.create') }}"
                    class="admin-btn admin-btn-primary"
                >
                    + افزودن محصول
                </a>

            </div>

        </div>

        @if(($inventoryIssueOrders ?? 0) > 0)
            <a
                href="{{ route('admin.orders.index', ['inventory_issue' => 1]) }}"
                class="flex min-w-0 flex-col gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-950 transition hover:border-amber-400 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-lg" aria-hidden="true">!</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold">سفارش پرداخت‌شده نیازمند بررسی موجودی است</p>
                        <p class="mt-1 text-xs leading-6 text-amber-900">پرداخت ثبت شده، اما موجودی کافی برای رزرو خودکار وجود نداشته است. سفارش را بررسی کنید تا تأمین کالا یا بازپرداخت مشخص شود.</p>
                    </div>
                </div>
                <span class="inline-flex shrink-0 items-center gap-2 text-xs font-semibold">
                    {{ \App\Helpers\PersianNumberHelper::format($inventoryIssueOrders) }} سفارش
                    <span aria-hidden="true">←</span>
                </span>
            </a>
        @endif

        {{-- =========================================================
             KPI CARDS
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Products --}}
            <div class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <p class="admin-stat-label">
                            کل محصولات
                        </p>

                        <p class="mt-2 text-3xl font-bold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($totalProducts) }}
                        </p>

                        <p class="mt-2 text-xs text-[var(--admin-muted)]">
                            {{ \App\Helpers\PersianNumberHelper::format($activeProducts) }}
                            فعال
                        </p>

                    </div>

                    <div class="admin-stat-icon">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 8.25-9-5.25-9 5.25m18 0-9 5.25m9-5.25V15l-9 5.25M3 8.25l9 5.25m-9-5.25V15l9 5.25m0-6.75V20.25"
                            />
                        </svg>

                    </div>

                </div>

                <div class="mt-4 flex items-center gap-2 text-[11px]">

                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                    {{ \App\Helpers\PersianNumberHelper::format($installmentProductsCount) }}
                    اقساطی
                </span>

                    @if($lowStockProducts > 0)

                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">
                        {{ \App\Helpers\PersianNumberHelper::format($lowStockProducts) }}
                        کم‌موجودی
                    </span>

                    @endif

                </div>

            </div>


            {{-- Customers --}}
            <div class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <p class="admin-stat-label">
                            مشتریان
                        </p>

                        <p class="mt-2 text-3xl font-bold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($totalCustomers) }}
                        </p>

                        <p class="mt-2 text-xs text-[var(--admin-muted)]">
                            ثبت‌نام‌شده
                        </p>

                    </div>

                    <div class="admin-stat-icon">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0ZM19.5 20.25a4.5 4.5 0 0 0-3.75-4.44"
                            />
                        </svg>

                    </div>

                </div>

                <div class="mt-4 text-xs text-[var(--admin-muted)]">
                    {{ $recentCustomers->count() }}
                    مشتری جدید در لیست اخیر
                </div>

            </div>


            {{-- Orders --}}
            <div class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <p class="admin-stat-label">
                            سفارش‌ها
                        </p>

                        <p class="mt-2 text-3xl font-bold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($totalOrders) }}
                        </p>

                        <p class="mt-2 text-xs text-[var(--admin-muted)]">
                            {{ \App\Helpers\PersianNumberHelper::format($pendingOrders) }}
                            در انتظار / پردازش
                        </p>

                    </div>

                    <div class="admin-stat-icon">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"
                            />
                        </svg>

                    </div>

                </div>

                <div class="mt-4 flex items-center gap-2 text-[11px]">

                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                    {{ \App\Helpers\PersianNumberHelper::format($paidOrderPercent) }}٪ پرداخت‌شده
                </span>

                </div>

            </div>


            {{-- Revenue --}}
            <div class="admin-stat admin-card-hover overflow-hidden p-5">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="admin-stat-label">
                            درآمد کل
                        </p>

                        <p class="mt-2 truncate text-2xl font-bold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($totalRevenue) }}
                        </p>

                        <p class="mt-1 text-[11px] text-[var(--admin-muted)]">
                            تومان
                        </p>

                    </div>

                    <div class="admin-stat-icon">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v12m4.5-9.75c0-1.243-1.007-2.25-2.25-2.25h-4.5A2.25 2.25 0 0 0 7.5 8.25v.75a2.25 2.25 0 0 0 2.25 2.25h4.5A2.25 2.25 0 0 1 16.5 13.5v.75a2.25 2.25 0 0 1-2.25 2.25h-4.5a2.25 2.25 0 0 1-2.25-2.25"
                            />
                        </svg>

                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between gap-3">

                <span class="text-[11px] text-[var(--admin-muted)]">
                    این ماه:
                    {{ \App\Helpers\PersianNumberHelper::format($currentMonthRevenue) }}
                    تومان
                </span>

                    <span
                        class="{{ $revenueGrowthPercent >= 0 ? 'text-emerald-600' : 'text-red-600' }} text-[11px] font-semibold"
                    >
                    {{ $revenueGrowthPercent > 0 ? '+' : '' }}
                        {{ \App\Helpers\PersianNumberHelper::format($revenueGrowthPercent, 1) }}٪
                </span>

                </div>

            </div>

        </div>


        {{-- =========================================================
             MAIN ANALYTICS
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- Revenue Chart --}}
            <section class="admin-card xl:col-span-2">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                                REVENUE ANALYTICS
                            </p>

                            <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                                درآمد ۶ ماه اخیر
                            </h2>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                فقط سفارش‌هایی که پرداخت موفق داشته‌اند.
                            </p>

                        </div>

                        <div class="text-right">

                            <p class="text-[10px] text-[var(--admin-muted)]">
                                میانگین هر سفارش
                            </p>

                            <p class="mt-1 text-sm font-bold text-[var(--admin-text)]">
                                {{ \App\Helpers\PersianNumberHelper::format($averageOrderValue) }}
                                تومان
                            </p>

                        </div>

                    </div>

                </div>

                <div class="p-6">

                    <div class="relative h-[300px]">

                        {{-- Grid --}}
                        <div class="pointer-events-none absolute inset-0 flex flex-col justify-between">

                            @for($i = 0; $i < 5; $i++)

                                <div class="border-t border-dashed border-[var(--admin-border)]"></div>

                            @endfor

                        </div>


                        {{-- Bars --}}
                        <div class="absolute inset-0 flex items-end gap-3 sm:gap-5">

                            @foreach($monthlyRevenue as $month)

                                @php
                                    $height =
                                        $month['revenue'] > 0
                                            ? max(
                                                4,
                                                ($month['revenue'] / $maxMonthlyRevenue) * 100
                                            )
                                            : 2;
                                @endphp

                                <div class="group relative flex h-full flex-1 flex-col justify-end">

                                    {{-- Tooltip --}}
                                    <div class="pointer-events-none absolute bottom-[calc({{ $height }}%+12px)] left-1/2 z-20 hidden -translate-x-1/2 whitespace-nowrap rounded-xl bg-[var(--admin-text)] px-3 py-2 text-[10px] text-white shadow-xl group-hover:block">

                                        {{ \App\Helpers\PersianNumberHelper::format($month['revenue']) }}
                                        تومان

                                    </div>

                                    {{-- Bar --}}
                                    <div
                                        class="mx-auto w-full max-w-14 rounded-t-2xl bg-gradient-to-t from-[var(--admin-accent)] to-[#d5b28f] transition duration-500 hover:from-[var(--admin-text)] hover:to-[var(--admin-accent)]"
                                        style="height: {{ $height }}%;"
                                    ></div>

                                    <div class="mt-3 text-center">

                                        <p class="text-[10px] font-medium text-[var(--admin-text-soft)]">
                                            {{ $month['label'] }}
                                        </p>

                                        <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                            {{ \App\Helpers\PersianNumberHelper::format($month['orders']) }}
                                            سفارش
                                        </p>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </section>


            {{-- Order Status Donut --}}
            <section class="admin-card">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        ORDER HEALTH
                    </p>

                    <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                        وضعیت سفارش‌ها
                    </h2>

                    <p class="mt-1 text-xs text-[var(--admin-muted)]">
                        توزیع وضعیت سفارش‌های ثبت‌شده.
                    </p>

                </div>

                <div class="p-6">

                    <div class="relative mx-auto h-52 w-52">

                        @php
                            $statusSegments = [
                                [
                                    'value' => $orderStatus['delivered'] ?? 0,
                                    'color' => '#15803d',
                                ],
                                [
                                    'value' => $orderStatus['processing'] ?? 0,
                                    'color' => '#0284c7',
                                ],
                                [
                                    'value' => $orderStatus['shipped'] ?? 0,
                                    'color' => '#4f46e5',
                                ],
                                [
                                    'value' => $orderStatus['pending'] ?? 0,
                                    'color' => '#d97706',
                                ],
                                [
                                    'value' => $orderStatus['cancelled'] ?? 0,
                                    'color' => '#dc2626',
                                ],
                            ];

                            $circumference = 502.65;

                            $offset = 0;
                        @endphp

                        <svg
                            viewBox="0 0 200 200"
                            class="h-full w-full -rotate-90"
                        >

                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="22"
                                class="text-[var(--admin-surface)]"
                            />

                            @if($statusTotal > 0)

                                @foreach($statusSegments as $segment)

                                    @php
                                        if ($segment['value'] <= 0) {
                                            continue;
                                        }

                                        $segmentLength =
                                            ($segment['value'] / $statusTotal)
                                            * $circumference;
                                    @endphp

                                    <circle
                                        cx="100"
                                        cy="100"
                                        r="80"
                                        fill="none"
                                        stroke="{{ $segment['color'] }}"
                                        stroke-width="22"
                                        stroke-dasharray="{{ $segmentLength }} {{ $circumference - $segmentLength }}"
                                        stroke-dashoffset="-{{ $offset }}"
                                    />

                                    @php
                                        $offset += $segmentLength;
                                    @endphp

                                @endforeach

                            @endif

                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center">

                            <p class="text-3xl font-bold text-[var(--admin-text)]">
                                {{ \App\Helpers\PersianNumberHelper::format($statusTotal) }}
                            </p>

                            <p class="mt-1 text-[10px] text-[var(--admin-muted)]">
                                سفارش
                            </p>

                        </div>

                    </div>


                    <div class="mt-7 grid grid-cols-2 gap-3">

                        @foreach($statusLabels as $status => $label)

                            @php
                                $count =
                                    $orderStatus[$status]
                                    ?? 0;

                                $color =
                                    $statusColors[$status]
                                    ?? [
                                        'text' => 'text-gray-600',
                                        'bg' => 'bg-gray-50',
                                    ];
                            @endphp

                            <div class="flex items-center justify-between gap-2 rounded-2xl {{ $color['bg'] }} px-3 py-2.5">

                            <span class="flex items-center gap-2">

                                <span class="h-2 w-2 rounded-full {{ str_replace('text-', 'bg-', $color['text']) }}"></span>

                                <span class="text-[10px] {{ $color['text'] }}">
                                    {{ $label }}
                                </span>

                            </span>

                                <span class="text-[10px] font-bold {{ $color['text'] }}">
                                {{ \App\Helpers\PersianNumberHelper::format($count) }}
                            </span>

                            </div>

                        @endforeach

                    </div>

                </div>

            </section>

        </div>


        {{-- =========================================================
             TOP PRODUCTS + QUICK METRICS
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- Top Products --}}
            <section class="admin-card xl:col-span-2">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <div class="flex items-center justify-between gap-4">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                                BEST SELLERS
                            </p>

                            <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                                محصولات پرفروش
                            </h2>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                بر اساس تعداد فروش سفارش‌های پرداخت‌شده.
                            </p>

                        </div>

                        <a
                            href="{{ route('admin.products.index') }}"
                            class="text-xs font-medium text-[var(--admin-accent)]"
                        >
                            مدیریت محصولات
                            ←
                        </a>

                    </div>

                </div>


                <div class="p-6">

                    @if($topProducts->isNotEmpty())

                        <div class="space-y-4">

                            @foreach($topProducts as $index => $product)

                                @php
                                    $percentage =
                                        max(
                                            3,
                                            ($product['quantity'] / $maxProductQuantity)
                                            * 100
                                        );
                                @endphp

                                <div class="group">

                                    <div class="flex items-center gap-4">

                                        {{-- Rank --}}
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-xs font-bold text-[var(--admin-text)]">
                                            {{ \App\Helpers\PersianNumberHelper::format($index + 1) }}
                                        </div>


                                        {{-- Image --}}
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-[var(--admin-surface)]">

                                            @if($product['image'])

                                                <img
                                                    src="{{ $product['image'] }}"
                                                    alt="{{ $product['name'] }}"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                >

                                            @else

                                                <div class="flex h-full w-full items-center justify-center text-[9px] tracking-widest text-[var(--admin-muted)]">
                                                    LV
                                                </div>

                                            @endif

                                        </div>


                                        {{-- Product --}}
                                        <div class="min-w-0 flex-1">

                                            <div class="flex items-start justify-between gap-3">

                                                <div class="min-w-0">

                                                    <p class="truncate text-sm font-semibold text-[var(--admin-text)]">
                                                        {{ $product['name'] }}
                                                    </p>

                                                    <p class="mt-1 text-[10px] text-[var(--admin-muted)]">
                                                        {{ \App\Helpers\PersianNumberHelper::format($product['quantity']) }}
                                                        عدد فروخته شده
                                                    </p>

                                                </div>

                                                <p class="shrink-0 text-xs font-semibold text-[var(--admin-text)]">
                                                    {{ \App\Helpers\PersianNumberHelper::format($product['revenue']) }}
                                                    تومان
                                                </p>

                                            </div>


                                            {{-- Progress --}}
                                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-[var(--admin-surface)]">

                                                <div
                                                    class="h-full rounded-full bg-gradient-to-l from-[var(--admin-accent)] to-[#d5b28f] transition-all duration-700"
                                                    style="width: {{ $percentage }}%;"
                                                ></div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="py-12 text-center">

                            <p class="text-sm font-semibold text-[var(--admin-text)]">
                                هنوز فروش ثبت نشده است.
                            </p>

                            <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                بعد از ثبت سفارش‌های پرداخت‌شده، محصولات پرفروش اینجا نمایش داده می‌شوند.
                            </p>

                        </div>

                    @endif

                </div>

            </section>


            {{-- Quick Business Metrics --}}
            <section class="admin-card">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        BUSINESS SNAPSHOT
                    </p>

                    <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                        وضعیت کسب‌وکار
                    </h2>

                </div>

                <div class="p-6 space-y-3">

                    {{-- Current Month --}}
                    <div class="rounded-2xl bg-[var(--admin-surface)] p-4">

                        <div class="flex items-center justify-between gap-3">

                            <div>

                                <p class="text-[10px] text-[var(--admin-muted)]">
                                    درآمد این ماه
                                </p>

                                <p class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($currentMonthRevenue) }}
                                </p>

                            </div>

                            <span class="rounded-xl bg-white px-3 py-2 text-[10px] font-semibold text-[var(--admin-accent)]">
                            تومان
                        </span>

                        </div>

                        <p class="mt-3 text-[10px] text-[var(--admin-muted)]">
                            {{ \App\Helpers\PersianNumberHelper::format($currentMonthOrders) }}
                            سفارش در این ماه
                        </p>

                    </div>


                    {{-- Delivered --}}
                    <div class="rounded-2xl bg-emerald-50 p-4">

                        <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-emerald-800">
                            نرخ تحویل
                        </span>

                            <span class="text-sm font-bold text-emerald-700">
                            {{ \App\Helpers\PersianNumberHelper::format($deliveredPercent) }}٪
                        </span>

                        </div>

                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-emerald-100">

                            <div
                                class="h-full rounded-full bg-emerald-600"
                                style="width: {{ min(100, $deliveredPercent) }}%;"
                            ></div>

                        </div>

                    </div>


                    {{-- Installment --}}
                    <div class="rounded-2xl bg-[var(--admin-surface)] p-4">

                        <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-[var(--admin-text)]">
                            سفارش اقساطی
                        </span>

                            <span class="text-sm font-bold text-[var(--admin-accent)]">
                            {{ \App\Helpers\PersianNumberHelper::format($installmentOrders) }}
                        </span>

                        </div>

                        <p class="mt-2 text-[10px] text-[var(--admin-muted)]">
                            {{ \App\Helpers\PersianNumberHelper::format($installmentPaidPercent) }}٪ از سفارش‌های اقساطی پرداخت شده‌اند.
                        </p>

                    </div>


                    {{-- Inventory --}}
                    <div class="grid grid-cols-2 gap-3">

                        <div class="rounded-2xl border border-[var(--admin-border)] p-4">

                            <p class="text-[10px] text-[var(--admin-muted)]">
                                بدون موجودی
                            </p>

                            <p class="mt-2 text-xl font-bold text-red-600">
                                {{ \App\Helpers\PersianNumberHelper::format($outOfStockProducts) }}
                            </p>

                        </div>

                        <div class="rounded-2xl border border-[var(--admin-border)] p-4">

                            <p class="text-[10px] text-[var(--admin-muted)]">
                                ویژه
                            </p>

                            <p class="mt-2 text-xl font-bold text-[var(--admin-text)]">
                                {{ \App\Helpers\PersianNumberHelper::format($featuredProductsCount) }}
                            </p>

                        </div>

                    </div>

                </div>

            </section>

        </div>


        {{-- =========================================================
             RECENT ORDERS + CUSTOMERS
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- Recent Orders --}}
            <section class="admin-card overflow-hidden">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                                RECENT ORDERS
                            </p>

                            <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                                آخرین سفارش‌ها
                            </h2>

                        </div>

                        <a
                            href="{{ route('admin.orders.index') }}"
                            class="text-xs font-medium text-[var(--admin-accent)]"
                        >
                            همه
                        </a>

                    </div>

                </div>

                <div class="divide-y divide-[var(--admin-border)]">

                    @forelse($recentOrders as $order)

                        @php
                            $orderStatus =
                                $statusLabels[$order->status]
                                ?? $order->status;

                            $statusColor =
                                $statusColors[$order->status]
                                ?? $statusColors['pending'];
                        @endphp

                        <a
                            href="{{ route('admin.orders.show', $order) }}"
                            class="flex items-center justify-between gap-4 p-5 transition hover:bg-[var(--admin-surface)]"
                        >

                            <div class="min-w-0">

                                <div class="flex flex-wrap items-center gap-2">

                                    <p class="text-sm font-semibold text-[var(--admin-text)]">
                                        {{ $order->order_number }}
                                    </p>

                                    <span class="rounded-full {{ $statusColor['bg'] }} px-2.5 py-1 text-[9px] {{ $statusColor['text'] }}">
                                    {{ $orderStatus }}
                                </span>

                                </div>

                                <p class="mt-2 truncate text-[10px] text-[var(--admin-muted)]">
                                    {{ $order->user?->name ?? $order->first_name . ' ' . $order->last_name }}
                                </p>

                            </div>

                            <div class="shrink-0 text-left">

                                <p class="text-xs font-bold text-[var(--admin-text)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($order->total) }}
                                </p>

                                <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                    تومان
                                </p>

                            </div>

                        </a>

                    @empty

                        <div class="px-6 py-12 text-center">

                            <p class="text-sm font-semibold">
                                سفارش جدیدی وجود ندارد.
                            </p>

                        </div>

                    @endforelse

                </div>

            </section>


            {{-- Recent Customers --}}
            <section class="admin-card overflow-hidden">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                                CUSTOMERS
                            </p>

                            <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                                مشتریان اخیر
                            </h2>

                        </div>

                        <a
                            href="{{ route('admin.customers.index') }}"
                            class="text-xs font-medium text-[var(--admin-accent)]"
                        >
                            همه
                        </a>

                    </div>

                </div>

                <div class="divide-y divide-[var(--admin-border)]">

                    @forelse($recentCustomers as $customer)

                        <a
                            href="{{ route('admin.customers.show', $customer) }}"
                            class="flex items-center gap-4 p-5 transition hover:bg-[var(--admin-surface)]"
                        >

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--admin-surface)] text-xs font-bold text-[var(--admin-text)]">
                                {{ mb_substr($customer->name ?: 'U', 0, 1) }}
                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="truncate text-sm font-semibold text-[var(--admin-text)]">
                                    {{ $customer->name ?: 'بدون نام' }}
                                </p>

                                <p class="mt-1 truncate text-[10px] text-[var(--admin-muted)]">
                                    {{ $customer->email }}
                                </p>

                            </div>

                            <div class="text-left">

                                <p class="text-[9px] text-[var(--admin-muted)]">
                                    ثبت‌نام
                                </p>

                                <p class="mt-1 text-[10px] text-[var(--admin-text-soft)]">
                                    {{ $customer->created_at ? \App\Helpers\JalaliHelper::format($customer->created_at, 'Y/m/d') : '—' }}
                                </p>

                            </div>

                        </a>

                    @empty

                        <div class="px-6 py-12 text-center">

                            <p class="text-sm font-semibold">
                                مشتری جدیدی وجود ندارد.
                            </p>

                        </div>

                    @endforelse

                </div>

            </section>

        </div>


        {{-- =========================================================
             QUICK ACTIONS
        ========================================================== --}}
        <section>

            <div class="mb-4">

                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                    QUICK ACTIONS
                </p>

                <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                    دسترسی سریع
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <a
                    href="{{ route('admin.products.create') }}"
                    class="admin-card admin-card-hover group p-5"
                >

                    <div class="flex items-center gap-4">

                        <div class="admin-stat-icon">
                            +
                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                افزودن محصول
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                ثبت محصول جدید
                            </p>

                        </div>

                    </div>

                </a>


                <a
                    href="{{ route('admin.categories.create') }}"
                    class="admin-card admin-card-hover group p-5"
                >

                    <div class="flex items-center gap-4">

                        <div class="admin-stat-icon">
                            +
                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                افزودن دسته‌بندی
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                ساخت دسته‌بندی جدید
                            </p>

                        </div>

                    </div>

                </a>


                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-card admin-card-hover group p-5"
                >

                    <div class="flex items-center gap-4">

                        <div class="admin-stat-icon">
                            ↗
                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                مدیریت سفارش‌ها
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                بررسی و پردازش سفارش‌ها
                            </p>

                        </div>

                    </div>

                </a>


                <a
                    href="{{ route('admin.customers.index') }}"
                    class="admin-card admin-card-hover group p-5"
                >

                    <div class="flex items-center gap-4">

                        <div class="admin-stat-icon">
                            ◎
                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                مدیریت مشتریان
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                مشاهده مشتریان و سفارش‌هایشان
                            </p>

                        </div>

                    </div>

                </a>

            </div>

        </section>

    </div>

@endsection
