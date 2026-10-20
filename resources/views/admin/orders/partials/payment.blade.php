<div class="admin-card p-6">

    <div class="mb-6">

        <h3 class="text-base font-bold text-[var(--admin-text)]">
            اطلاعات پرداخت
        </h3>

        <p class="mt-1 text-xs text-[var(--admin-muted)]">
            آخرین وضعیت پرداخت سفارش
        </p>

    </div>


    @php
        $payment = $order->latestPayment;
    @endphp


    <dl class="space-y-5">

        {{-- =========================================================
             ORDER PAYMENT STATUS
        ========================================================== --}}

        <div class="flex items-center justify-between gap-4">

            <dt class="text-xs text-[var(--admin-muted)]">
                وضعیت پرداخت
            </dt>

            <dd>

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
                            بازپرداخت شده
                        </span>

                    @break

                    @default

                    <span class="admin-badge admin-badge-neutral">
                            {{ $order->payment_status ?: '—' }}
                        </span>

                @endswitch

            </dd>

        </div>


        {{-- =========================================================
             PAYMENT METHOD
        ========================================================== --}}

        <div class="flex items-center justify-between gap-4">

            <dt class="text-xs text-[var(--admin-muted)]">
                روش پرداخت
            </dt>

            <dd class="text-sm text-[var(--admin-text-soft)]">

                @if($order->payment_method === 'installment')

                    خرید اقساطی

                @else

                    پرداخت آنلاین

                @endif

            </dd>

        </div>


        {{-- =========================================================
             PAYMENT PROVIDER
        ========================================================== --}}

        @if($order->payment_provider)

            <div class="flex items-center justify-between gap-4">

                <dt class="text-xs text-[var(--admin-muted)]">
                    ارائه‌دهنده پرداخت
                </dt>

                <dd class="text-sm text-[var(--admin-text-soft)]">
                    {{ $order->payment_provider }}
                </dd>

            </div>

        @endif


        {{-- =========================================================
             ORDER TOTAL
        ========================================================== --}}

        <div class="flex items-center justify-between gap-4">

            <dt class="text-xs text-[var(--admin-muted)]">
                مبلغ سفارش
            </dt>

            <dd class="text-sm font-semibold text-[var(--admin-text)]">
                {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}

                <span class="text-xs font-normal text-[var(--admin-muted)]">
                    تومان
                </span>
            </dd>

        </div>


        {{-- =========================================================
             LATEST PAYMENT
        ========================================================== --}}

        @if($payment)

            {{-- Payment Amount --}}

            @if($payment->amount !== null)

                <div class="flex items-center justify-between gap-4">

                    <dt class="text-xs text-[var(--admin-muted)]">
                        مبلغ پرداختی
                    </dt>

                    <dd class="text-sm text-[var(--admin-text-soft)]">
                        {{ \App\Helpers\PersianNumberHelper::format((float) $payment->amount) }}

                        <span class="text-xs text-[var(--admin-muted)]">
                            تومان
                        </span>
                    </dd>

                </div>

            @endif


            {{-- Gateway --}}

            @if($payment->gateway)

                <div class="flex items-center justify-between gap-4">

                    <dt class="text-xs text-[var(--admin-muted)]">
                        درگاه
                    </dt>

                    <dd class="text-sm font-medium text-[var(--admin-text-soft)]">
                        {{ $payment->gateway }}
                    </dd>

                </div>

            @endif


            {{-- Payment Status --}}

            @if($payment->status)

                <div class="flex items-center justify-between gap-4">

                    <dt class="text-xs text-[var(--admin-muted)]">
                        وضعیت تراکنش
                    </dt>

                    <dd>

                        @switch($payment->status)

                            @case('paid')

                            <span class="admin-badge admin-badge-success">
                                    موفق
                                </span>

                            @break

                            @case('pending')

                            @case('initiated')

                            <span class="admin-badge admin-badge-warning">
                                    در انتظار
                                </span>

                            @break

                            @case('failed')

                            <span class="admin-badge admin-badge-danger">
                                    ناموفق
                                </span>

                            @break

                            @case('cancelled')

                            <span class="admin-badge admin-badge-neutral">
                                    لغو شده
                                </span>

                            @break

                            @case('refunded')

                            <span class="admin-badge admin-badge-info">
                                    بازپرداخت
                                </span>

                            @break

                            @default

                            <span class="admin-badge admin-badge-neutral">
                                    {{ $payment->status }}
                                </span>

                        @endswitch

                    </dd>

                </div>

            @endif


            {{-- Transaction ID --}}

            @if($payment->transaction_id)

                <div>

                    <dt class="text-xs text-[var(--admin-muted)]">
                        شناسه تراکنش
                    </dt>

                    <dd
                        dir="ltr"
                        class="mt-2 break-all font-mono text-xs text-[var(--admin-text-soft)]"
                    >
                        {{ $payment->transaction_id }}
                    </dd>

                </div>

            @endif


            {{-- Authority --}}

            @if($payment->authority)

                <div>

                    <dt class="text-xs text-[var(--admin-muted)]">
                        شناسه Authority
                    </dt>

                    <dd
                        dir="ltr"
                        class="mt-2 break-all font-mono text-xs text-[var(--admin-text-soft)]"
                    >
                        {{ $payment->authority }}
                    </dd>

                </div>

            @endif


            {{-- Reference ID --}}

            @if($payment->reference_id)

                <div>

                    <dt class="text-xs text-[var(--admin-muted)]">
                        شناسه مرجع
                    </dt>

                    <dd
                        dir="ltr"
                        class="mt-2 break-all font-mono text-xs text-[var(--admin-text-soft)]"
                    >
                        {{ $payment->reference_id }}
                    </dd>

                </div>

            @endif


            {{-- Paid At --}}

            @if($payment->paid_at)

                <div>

                    <dt class="text-xs text-[var(--admin-muted)]">
                        زمان پرداخت
                    </dt>

                    <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                        {{ $payment->paid_at ? \App\Helpers\JalaliHelper::format($payment->paid_at, 'Y/m/d H:i') : '—' }}
                    </dd>

                </div>

            @endif


        @else

            {{-- No Payment --}}

            <div class="border-t border-[var(--admin-border-soft)] pt-5">

                <p class="text-xs leading-6 text-[var(--admin-muted)]">
                    هنوز رکورد پرداختی برای این سفارش ثبت نشده است.
                </p>

            </div>

        @endif

    </dl>

</div>
