@extends('admin.layouts.app')

@section('title', 'سفارش ' . $order->order_number)
@section('page_title', 'جزئیات سفارش')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <span class="text-xs font-medium text-[var(--admin-accent)]">
                SALES / ORDERS / VIEW
            </span>

            <h2 class="admin-title mt-2">
                سفارش {{ $order->order_number }}
            </h2>

            <p class="admin-subtitle mt-2">
                جزئیات کامل سفارش و وضعیت پرداخت
            </p>

        </div>


        <a
            href="{{ route('admin.orders.index') }}"
            class="admin-btn admin-btn-secondary"
        >
            بازگشت به سفارش‌ها
        </a>

    </div>


    {{-- Order Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                شماره سفارش
            </p>

            <p class="mt-3 font-mono text-sm font-bold text-[var(--admin-text)]">
                {{ $order->order_number }}
            </p>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                مبلغ نهایی
            </p>

            <p class="admin-stat-value">
                {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}

                <span class="text-xs font-normal text-[var(--admin-muted)]">
                    تومان
                </span>
            </p>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                وضعیت سفارش
            </p>

            <div class="mt-3">

                @switch($order->status)

                    @case('pending')
                    <span class="admin-badge admin-badge-warning">
                            در انتظار
                        </span>
                    @break

                    @case('processing')
                    <span class="admin-badge admin-badge-info">
                            در حال پردازش
                        </span>
                    @break

                    @case('shipped')
                    <span class="admin-badge admin-badge-info">
                            ارسال شده
                        </span>
                    @break

                    @case('delivered')
                    <span class="admin-badge admin-badge-success">
                            تحویل شده
                        </span>
                    @break

                    @case('cancelled')
                    <span class="admin-badge admin-badge-danger">
                            لغو شده
                        </span>
                    @break

                    @default
                    <span class="admin-badge admin-badge-neutral">
                            {{ $order->status }}
                        </span>

                @endswitch

            </div>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                وضعیت پرداخت
            </p>

            <div class="mt-3">

                @switch($order->payment_status)

                    @case('paid')
                    <span class="admin-badge admin-badge-success">
                            پرداخت شده
                        </span>
                    @break

                    @case('pending')
                    <span class="admin-badge admin-badge-warning">
                            در انتظار
                        </span>
                    @break

                    @case('failed')
                    <span class="admin-badge admin-badge-danger">
                            ناموفق
                        </span>
                    @break

                    @case('refunded')
                    <span class="admin-badge admin-badge-info">
                            بازپرداخت
                        </span>
                    @break

                    @default
                    <span class="admin-badge admin-badge-neutral">
                            {{ $order->payment_status ?: '—' }}
                        </span>

                @endswitch

            </div>

        </div>

    </div>


    @if($order->inventory_issue_at)
        <div role="alert" class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-lg" aria-hidden="true">!</span>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold">پرداخت موفق است؛ بررسی موجودی لازم است</h3>
                    <p class="mt-2 text-xs leading-7">
                        موجودی سفارش در زمان تأیید پرداخت قابل رزرو نبود. لطفاً موجودی واقعی را بررسی کنید و برای تأمین کالا یا هماهنگی بازپرداخت اقدام کنید.
                    </p>
                    @if($order->inventory_issue_reason)
                        <p class="mt-2 break-words text-xs leading-6 text-amber-900">{{ $order->inventory_issue_reason }}</p>
                    @endif
                    <p class="mt-2 text-[11px] text-amber-800">
                        ثبت هشدار: {{ \App\Helpers\JalaliHelper::format($order->inventory_issue_at, 'Y/m/d H:i') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            @include('admin.orders.partials.customer', [
                'order' => $order,
            ])

            @include('admin.orders.partials.items', [
                'order' => $order,
            ])

            @include('admin.orders.partials.payment', [
                'order' => $order,
            ])

        </div>


        <div class="space-y-6">

            @include('admin.orders.partials.status', [
                'order' => $order,
            ])


            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    خلاصه مالی
                </h3>

                <dl class="mt-6 space-y-4">

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-xs text-[var(--admin-muted)]">
                            مبلغ اقلام
                        </dt>

                        <dd class="text-sm text-[var(--admin-text-soft)]">
                            {{ \App\Helpers\PersianNumberHelper::format((float) $order->subtotal) }}
                            تومان
                        </dd>
                    </div>


                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-xs text-[var(--admin-muted)]">
                            هزینه ارسال
                        </dt>

                        <dd class="text-sm text-[var(--admin-text-soft)]">
                            {{ \App\Helpers\PersianNumberHelper::format((float) $order->shipping_cost) }}
                            تومان
                        </dd>
                    </div>


                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-xs text-[var(--admin-muted)]">
                            تخفیف
                        </dt>

                        <dd class="text-sm text-[var(--admin-text-soft)]">
                            {{ \App\Helpers\PersianNumberHelper::format((float) $order->discount) }}
                            تومان
                        </dd>
                    </div>


                    <div class="border-t border-[var(--admin-border-soft)] pt-4">

                        <div class="flex items-center justify-between gap-4">

                            <dt class="text-sm font-semibold text-[var(--admin-text)]">
                                مبلغ نهایی
                            </dt>

                            <dd class="text-base font-bold text-[var(--admin-accent)]">
                                {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}
                                تومان
                            </dd>

                        </div>

                    </div>

                </dl>

            </div>


            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات سفارش
                </h3>

                <dl class="mt-6 space-y-5">

                    <div>

                        <dt class="admin-stat-label">
                            تاریخ ثبت
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $order->created_at ? \App\Helpers\JalaliHelper::format($order->created_at, 'Y/m/d H:i') : '—' }}
                        </dd>

                    </div>


                    <div>

                        <dt class="admin-stat-label">
                            آخرین بروزرسانی
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $order->updated_at ? \App\Helpers\JalaliHelper::format($order->updated_at, 'Y/m/d H:i') : '—' }}
                        </dd>

                    </div>

                </dl>

            </div>

        </div>

    </div>

@endsection
