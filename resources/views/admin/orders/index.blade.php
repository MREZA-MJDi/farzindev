@extends('admin.layouts.app')

@section('title', 'سفارش‌ها')
@section('page_title', 'سفارش‌ها')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <span class="text-xs font-medium text-[var(--admin-accent)]">
                SALES / ORDERS
            </span>

            <h2 class="admin-title mt-2">
                سفارش‌ها
            </h2>

            <p class="admin-subtitle mt-2">
                مشاهده و مدیریت سفارش‌های ثبت‌شده
            </p>
        </div>

    </div>


    {{-- Filters --}}
    <div class="admin-card mb-6 p-5">

        <form
            action="{{ route('admin.orders.index') }}"
            method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >

            <div class="xl:col-span-2">

                <label for="search" class="admin-label">
                    جستجو
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ request('search') }}"
                    class="admin-input"
                    placeholder="شماره سفارش، نام، تلفن یا ایمیل..."
                >

            </div>


            <div>

                <label for="status" class="admin-label">
                    وضعیت سفارش
                </label>

                <select
                    id="status"
                    name="status"
                    class="admin-select"
                >
                    <option value="">
                        همه وضعیت‌ها
                    </option>

                    <option value="pending" @selected(request('status') === 'pending')>
                    در انتظار
                    </option>

                    <option value="processing" @selected(request('status') === 'processing')>
                    در حال پردازش
                    </option>

                    <option value="shipped" @selected(request('status') === 'shipped')>
                    ارسال شده
                    </option>

                    <option value="delivered" @selected(request('status') === 'delivered')>
                    تحویل شده
                    </option>

                    <option value="cancelled" @selected(request('status') === 'cancelled')>
                    لغو شده
                    </option>
                </select>

            </div>


            <div>

                <label for="payment_status" class="admin-label">
                    وضعیت پرداخت
                </label>

                <select
                    id="payment_status"
                    name="payment_status"
                    class="admin-select"
                >
                    <option value="">
                        همه
                    </option>

                    <option value="pending" @selected(request('payment_status') === 'pending')>
                    در انتظار پرداخت
                    </option>

                    <option value="paid" @selected(request('payment_status') === 'paid')>
                    پرداخت شده
                    </option>

                    <option value="failed" @selected(request('payment_status') === 'failed')>
                    ناموفق
                    </option>

                    <option value="refunded" @selected(request('payment_status') === 'refunded')>
                    بازپرداخت شده
                    </option>
                </select>

            </div>


            <div class="flex items-end gap-2 xl:col-span-4">

                <button
                    type="submit"
                    class="admin-btn admin-btn-secondary"
                >
                    اعمال فیلتر
                </button>

                @if(request()->hasAny(['search', 'status', 'payment_status']))

                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="admin-btn admin-btn-ghost"
                    >
                        پاک کردن
                    </a>

                @endif

            </div>

        </form>

    </div>


    {{-- Table --}}
    <div class="admin-table-wrap">

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>مشتری</th>
                    <th>تلفن</th>
                    <th>مبلغ نهایی</th>
                    <th>پرداخت</th>
                    <th>وضعیت سفارش</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($orders as $order)

                    <tr>

                        <td>
                            <a
                                href="{{ route('admin.orders.show', $order) }}"
                                class="font-mono text-sm font-semibold text-[var(--admin-accent)] hover:text-[var(--admin-accent-hover)]"
                            >
                                {{ $order->order_number }}
                            </a>
                            @if($order->inventory_issue_at)
                                <span class="mt-2 inline-flex rounded-full border border-amber-300 bg-amber-50 px-2 py-1 text-[9px] font-semibold text-amber-900">
                                    بررسی موجودی
                                </span>
                            @endif
                        </td>


                        <td>

                            <div>
                                <p class="text-sm font-semibold text-[var(--admin-text)]">
                                    {{ $order->full_name ?: '—' }}
                                </p>

                                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                    {{ $order->email ?: ($order->user?->email ?? '—') }}
                                </p>
                            </div>

                        </td>


                        <td>
                                <span class="text-sm text-[var(--admin-text-soft)]">
                                    {{ $order->phone ?: '—' }}
                                </span>
                        </td>


                        <td>

                                <span class="font-semibold text-[var(--admin-text)]">
                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}
                                </span>

                            <span class="mr-1 text-xs text-[var(--admin-muted)]">
                                    تومان
                                </span>

                        </td>


                        <td>

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

                        </td>


                        <td>

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

                        </td>


                        <td>
                                <span class="whitespace-nowrap text-xs text-[var(--admin-muted)]">
                                    {{ $order->created_at ? \App\Helpers\JalaliHelper::format($order->created_at, 'Y/m/d H:i') : '—' }}
                                </span>
                        </td>


                        <td>

                            <a
                                href="{{ route('admin.orders.show', $order) }}"
                                class="admin-btn admin-btn-secondary px-3"
                            >
                                مشاهده
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8">

                            <div class="admin-empty">

                                <div class="admin-empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor"
                                         class="h-6 w-6">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                    سفارشی پیدا نشد.
                                </h3>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    با تغییر فیلترها دوباره جستجو کنید.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    @if($orders->hasPages())

        <div class="mt-6">
            {{ $orders->links() }}
        </div>

    @endif

@endsection
