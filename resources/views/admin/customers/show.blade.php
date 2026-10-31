@extends('admin.layouts.app')

@section('title', $customer->name)
@section('page_title', 'جزئیات مشتری')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <span class="text-xs font-medium text-[var(--admin-accent)]">
                USERS / CUSTOMERS / VIEW
            </span>

            <h2 class="admin-title mt-2">
                {{ $customer->name }}
            </h2>

            <p class="admin-subtitle mt-2">
                اطلاعات حساب، آدرس‌ها و سفارش‌های مشتری
            </p>

        </div>


        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('admin.customers.edit', $customer) }}"
                class="admin-btn admin-btn-primary"
            >
                ویرایش
            </a>

            <a
                href="{{ route('admin.customers.index') }}"
                class="admin-btn admin-btn-secondary"
            >
                بازگشت
            </a>

        </div>

    </div>


    {{-- Customer Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-stat p-5">

            <div class="flex items-center gap-3">

                <div class="admin-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.5"
                         stroke="currentColor"
                         class="h-5 w-5">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                        />
                    </svg>
                </div>

                <div>
                    <p class="admin-stat-label">
                        مشتری
                    </p>

                    <p class="mt-1 text-sm font-bold text-[var(--admin-text)]">
                        {{ $customer->name }}
                    </p>
                </div>

            </div>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                سفارش‌ها
            </p>

            <p class="admin-stat-value">
                {{ number_format((int) $customer->orders_count) }}
            </p>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                علاقه‌مندی‌ها
            </p>

            <p class="admin-stat-value">
                {{ number_format((int) $customer->wishlists_count) }}
            </p>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                عضویت
            </p>

            <p class="mt-3 text-sm font-semibold text-[var(--admin-text)]">
                {{ $customer->created_at ? \App\Helpers\JalaliHelper::format($customer->created_at, 'Y/m/d') : '—' }}
            </p>

        </div>

    </div>


    {{-- Main --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            {{-- Account --}}
            <div class="admin-card p-6">

                <div class="mb-6">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        اطلاعات حساب
                    </h3>

                </div>


                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    <div>

                        <dt class="admin-stat-label">
                            نام
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ $customer->name }}
                        </dd>

                    </div>


                    <div>

                        <dt class="admin-stat-label">
                            ایمیل
                        </dt>

                        <dd class="mt-2 break-all text-sm text-[var(--admin-text-soft)]">
                            {{ $customer->email }}
                        </dd>

                    </div>


                    <div>

                        <dt class="admin-stat-label">
                            نقش
                        </dt>

                        <dd class="mt-2">
                            <span class="admin-badge admin-badge-info">
                                مشتری
                            </span>
                        </dd>

                    </div>


                    <div>

                        <dt class="admin-stat-label">
                            تاریخ عضویت
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $customer->created_at ? \App\Helpers\JalaliHelper::format($customer->created_at, 'Y/m/d H:i') : '—' }}
                        </dd>

                    </div>

                </dl>

            </div>


            {{-- Addresses --}}
            <div class="admin-card p-6">

                <div class="mb-6">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        آدرس‌ها
                    </h3>

                    <p class="mt-1 text-xs text-[var(--admin-muted)]">
                        آخرین ۵ آدرس از {{ number_format((int) $customer->addresses_count) }} آدرس ثبت‌شده
                    </p>

                </div>


                @if($customer->addresses->count())

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        @foreach($customer->addresses as $address)

                            <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">

                                @if($address->is_default ?? false)

                                    <span class="admin-badge admin-badge-success mb-3">
                                        آدرس پیش‌فرض
                                    </span>

                                @endif


                                <p class="text-sm leading-8 text-[var(--admin-text-soft)]">

                                    {{ $address->province ?? '' }}

                                    @if($address->city)
                                        ، {{ $address->city }}
                                    @endif

                                    @if($address->address)
                                        ، {{ $address->address }}
                                    @endif

                                </p>


                                @if($address->postal_code)

                                    <p class="mt-3 font-mono text-xs text-[var(--admin-muted)]">
                                        کد پستی: {{ $address->postal_code }}
                                    </p>

                                @endif

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="admin-empty py-8">

                        <p class="text-xs text-[var(--admin-muted)]">
                            مشتری هنوز آدرسی ثبت نکرده است.
                        </p>

                    </div>

                @endif

            </div>


            {{-- Orders --}}
            <div class="admin-card p-6">

                <div class="mb-6">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        سفارش‌های مشتری
                    </h3>

                </div>


                @if($orders->total() > 0)

                    <div class="space-y-3">

                        @foreach($orders as $order)

                            <a
                                href="{{ route('admin.orders.show', $order) }}"
                                class="flex flex-col justify-between gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 transition hover:border-[var(--admin-accent-dark)] sm:flex-row sm:items-center"
                            >

                                <div>

                                    <p class="font-mono text-sm font-semibold text-[var(--admin-accent)]">
                                        {{ $order->order_number }}
                                    </p>

                                    <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                        {{ $order->created_at ? \App\Helpers\JalaliHelper::format($order->created_at, 'Y/m/d H:i') : '—' }}
                                    </p>

                                </div>


                                <div class="flex items-center gap-3">

                                    <span class="text-sm font-semibold text-[var(--admin-text)]">
                                        {{ number_format((float) $order->total) }}
                                        تومان
                                    </span>


                                    @switch($order->status)

                                        @case('pending')
                                        <span class="admin-badge admin-badge-warning">
                                                در انتظار
                                            </span>
                                        @break

                                        @case('processing')
                                        <span class="admin-badge admin-badge-info">
                                                پردازش
                                            </span>
                                        @break

                                        @case('shipped')
                                        <span class="admin-badge admin-badge-info">
                                                ارسال
                                            </span>
                                        @break

                                        @case('delivered')
                                        <span class="admin-badge admin-badge-success">
                                                تحویل
                                            </span>
                                        @break

                                        @case('cancelled')
                                        <span class="admin-badge admin-badge-danger">
                                                لغو
                                            </span>
                                        @break

                                        @default
                                        <span class="admin-badge admin-badge-neutral">
                                                {{ $order->status }}
                                            </span>

                                    @endswitch

                                </div>

                            </a>

                        @endforeach

                    </div>

                    @if($orders->hasPages())
                        <div class="mt-5">
                            {{ $orders->links() }}
                        </div>
                    @endif

                @else

                    <div class="admin-empty py-8">

                        <p class="text-xs text-[var(--admin-muted)]">
                            این مشتری هنوز سفارشی ثبت نکرده است.
                        </p>

                    </div>

                @endif

            </div>

        </div>


        {{-- Sidebar --}}
        <div class="space-y-6">

            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات تماس
                </h3>

                <div class="mt-6 space-y-4">

                    <div>

                        <p class="admin-stat-label">
                            ایمیل
                        </p>

                        <p class="mt-2 break-all text-sm text-[var(--admin-text-soft)]">
                            {{ $customer->email }}
                        </p>

                    </div>


                    @if($customer->addresses->first()?->phone)

                        <div>

                            <p class="admin-stat-label">
                                تلفن
                            </p>

                            <p class="mt-2 text-sm text-[var(--admin-text-soft)]">
                                {{ $customer->addresses->first()->phone }}
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            <div class="admin-card p-6">

                <h3 class="text-sm font-bold text-[var(--admin-text)]">
                    ویرایش مشتری
                </h3>

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    اطلاعات نام و ایمیل مشتری را می‌توانید تغییر دهید.
                </p>

                <a
                    href="{{ route('admin.customers.edit', $customer) }}"
                    class="admin-btn admin-btn-primary mt-5 w-full"
                >
                    ویرایش اطلاعات
                </a>

            </div>

        </div>

    </div>

@endsection
