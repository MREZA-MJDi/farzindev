@extends('layouts.app')

@section('title', 'حساب کاربری | فرزین')

@section(
    'description',
    'مدیریت حساب کاربری، سفارش‌ها، آدرس‌ها و علاقه‌مندی‌های شما در فرزین.'
)

@push('seo')
    <meta
        name="robots"
        content="noindex,nofollow,noarchive"
    >
@endpush

@section('content')

    @php
        $userName = $user->name ?: 'کاربر فرزین';

        $recentOrders = $orders;

        $totalOrders =
            (int) ($stats['orders'] ?? 0);

        $processingOrders =
            (int) ($stats['processing_orders'] ?? 0);

        $wishlistCount =
            (int) ($stats['wishlist'] ?? 0);

        $paidOrders = (int) ($stats['paid_orders'] ?? 0);

        $installmentOrders = (int) ($stats['installment_orders'] ?? 0);

        $statusLabels = [
            'pending' => 'در انتظار بررسی',
            'processing' => 'در حال پردازش',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل شده',
            'cancelled' => 'لغو شده',
        ];

        $paymentStatusLabels = [
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'failed' => 'پرداخت ناموفق',
            'refunded' => 'بازپرداخت شده',
        ];

        $statusVariants = [
            'pending' => 'warning',
            'processing' => 'warning',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
        ];

        $paymentVariants = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
        ];
    @endphp

    <div class="bg-[var(--livora-cream)]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        حساب کاربری
                    </span>

                    </div>

                    <div class="mt-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                MY فرزین
                            </p>

                            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                خوش آمدی، {{ $userName }}
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                                سفارش‌ها، پرداخت‌ها و اطلاعات حساب خود را از اینجا مدیریت کن.
                            </p>

                        </div>

                        <div class="flex flex-wrap gap-3">

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-5 py-3 text-sm font-medium transition hover:border-[var(--livora-ink)]"
                            >
                                ادامه خرید
                            </a>

                            <form
                                action="{{ route('logout') }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                >
                                    خروج
                                </button>
                            </form>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             MAIN
        ========================================================== --}}
        <section>

            <x-layout.container>

                <div class="grid gap-6 py-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:py-10">

                    {{-- =================================================
                         MAIN COLUMN
                    ================================================== --}}
                    <div class="min-w-0 space-y-6">

                        {{-- Stats --}}
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    ORDERS
                                </p>

                                <p class="mt-3 text-2xl font-semibold">
                                    {{ \App\Helpers\PersianNumberHelper::format($totalOrders) }}
                                </p>

                                <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                    کل سفارش‌ها
                                </p>

                            </div>

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    PROCESSING
                                </p>

                                <p class="mt-3 text-2xl font-semibold">
                                    {{ \App\Helpers\PersianNumberHelper::format($processingOrders) }}
                                </p>

                                <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                    در حال پردازش
                                </p>

                            </div>

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    PAID
                                </p>

                                <p class="mt-3 text-2xl font-semibold">
                                    {{ \App\Helpers\PersianNumberHelper::format($paidOrders) }}
                                </p>

                                <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                    سفارش پرداخت‌شده
                                </p>

                            </div>

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    WISHLIST
                                </p>

                                <p class="mt-3 text-2xl font-semibold">
                                    {{ \App\Helpers\PersianNumberHelper::format($wishlistCount) }}
                                </p>

                                <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                    علاقه‌مندی‌ها
                                </p>

                            </div>

                        </div>


                        {{-- Quick Actions --}}
                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">

                            <div class="mb-6">

                                <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                    QUICK ACCESS
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    دسترسی سریع
                                </h2>

                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                                <a
                                    href="{{ route('account.orders.index') }}"
                                    class="group rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4 transition hover:border-[var(--livora-ink)]"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-white)] text-xs font-semibold">
                                        01
                                    </div>

                                    <p class="mt-4 text-sm font-semibold">
                                        سفارش‌ها
                                    </p>

                                    <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                        مشاهده تاریخچه سفارش‌ها
                                    </p>
                                </a>

                                <a
                                    href="{{ route('account.wishlist.index') }}"
                                    class="group rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4 transition hover:border-[var(--livora-ink)]"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-white)] text-xs font-semibold">
                                        02
                                    </div>

                                    <p class="mt-4 text-sm font-semibold">
                                        علاقه‌مندی‌ها
                                    </p>

                                    <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                        محصولات ذخیره‌شده شما
                                    </p>
                                </a>

                                <a
                                    href="{{ route('account.addresses.index') }}"
                                    class="group rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4 transition hover:border-[var(--livora-ink)]"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-white)] text-xs font-semibold">
                                        03
                                    </div>

                                    <p class="mt-4 text-sm font-semibold">
                                        آدرس‌ها
                                    </p>

                                    <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                        مدیریت آدرس‌های ارسال
                                    </p>
                                </a>

                                <a
                                    href="{{ route('account.profile.edit') ?? '#' }}"
                                    class="group rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4 transition hover:border-[var(--livora-ink)]"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-white)] text-xs font-semibold">
                                        04
                                    </div>

                                    <p class="mt-4 text-sm font-semibold">
                                        پروفایل
                                    </p>

                                    <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                        اطلاعات شخصی و حساب
                                    </p>
                                </a>

                            </div>

                        </div>


                        {{-- Recent Orders --}}
                        <div class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">

                            <div class="flex flex-col gap-4 border-b border-[var(--livora-border)] p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">

                                <div>

                                    <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                        RECENT ORDERS
                                    </p>

                                    <h2 class="mt-2 text-xl font-semibold">
                                        سفارش‌های اخیر
                                    </h2>

                                </div>

                                <a
                                    href="{{ route('account.orders.index') }}"
                                    class="text-sm font-medium text-[var(--livora-accent)]"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true"><path d="M6 3h12v18l-3-1.8-3 1.8-3-1.8L6 21V3Z" /><path d="M9 8h6m-6 4h6m-6 4h3" /></svg> مشاهده همه
                                </a>

                            </div>


                            @forelse($recentOrders as $order)

                                <a
                                    href="{{ route('account.orders.show', $order) }}"
                                    class="group block border-b border-[var(--livora-border)] p-5 transition last:border-0 hover:bg-[var(--livora-surface)] sm:p-7"
                                >

                                    <div class="flex flex-col gap-5">

                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                            <div>

                                                <div class="flex flex-wrap items-center gap-2">

                                                    <p class="text-sm font-semibold">
                                                        {{ $order->order_number }}
                                                    </p>

                                                    <x-ui.badge
                                                        :variant="$statusVariants[$order->status] ?? 'warning'"
                                                    >
                                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                                    </x-ui.badge>

                                                </div>

                                                <p class="mt-2 text-[11px] text-[var(--livora-stone)]">
                                                    {{ $order->items->count() }}
                                                    محصول
                                                </p>

                                            </div>

                                            <div class="text-right">

                                                <p class="text-sm font-semibold">
                                                    {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}
                                                    تومان
                                                </p>

                                                <x-ui.badge
                                                    :variant="$paymentVariants[$order->payment_status] ?? 'warning'"
                                                    class="mt-2"
                                                >
                                                    {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}
                                                </x-ui.badge>

                                            </div>

                                        </div>


                                        {{-- Payment / installment hint --}}
                                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">

                                            @if($order->payment_method === 'installment')

                                                <span class="rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-1.5">
                                                خرید اقساطی
                                            </span>

                                            @endif

                                            @if($order->payment_provider)

                                                <span class="rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-1.5">
                                                {{ $order->payment_provider }}
                                            </span>

                                            @endif

                                            <span>
                                            مشاهده جزئیات
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                                        </span>

                                        </div>

                                    </div>

                                </a>

                            @empty

                                <div class="px-6 py-20 text-center">

                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-stone)]">
                                        —
                                    </div>

                                    <h3 class="mt-6 text-lg font-semibold">
                                        هنوز سفارشی نداری
                                    </h3>

                                    <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--livora-stone)]">
                                        اولین انتخابت را از فروشگاه فرزین پیدا کن.
                                    </p>

                                    <a
                                        href="{{ route('shop.index') }}"
                                        class="mt-6 inline-flex rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                    >
                                        ورود به فروشگاه
                                    </a>

                                </div>

                            @endforelse

                        </div>

                    </div>


                    {{-- =================================================
                         SIDEBAR
                    ================================================== --}}
                    <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">

                        {{-- Profile --}}
                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-ink)] p-6 text-white sm:p-7">

                            <p class="text-[10px] uppercase tracking-[0.2em] text-white/45">
                                ACCOUNT
                            </p>

                            <h2 class="mt-3 text-xl font-semibold">
                                {{ $userName }}
                            </h2>

                            @if($user->email)

                                <p class="mt-2 break-all text-xs text-white/50">
                                    {{ $user->email }}
                                </p>

                            @endif

                            @if($user->phone)

                                <p class="mt-1 text-xs text-white/50">
                                    {{ $user->phone }}
                                </p>

                            @endif

                            <div class="mt-6 h-px bg-white/10"></div>

                            <div class="mt-5 grid grid-cols-2 gap-3">

                                <div class="rounded-2xl bg-white/5 p-4">

                                    <p class="text-[10px] text-white/40">
                                        سفارش‌ها
                                    </p>

                                    <p class="mt-2 text-lg font-semibold">
                                        {{ \App\Helpers\PersianNumberHelper::format($totalOrders) }}
                                    </p>

                                </div>

                                <div class="rounded-2xl bg-white/5 p-4">

                                    <p class="text-[10px] text-white/40">
                                        اقساط
                                    </p>

                                    <p class="mt-2 text-lg font-semibold">
                                        {{ \App\Helpers\PersianNumberHelper::format($installmentOrders) }}
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Account Navigation --}}
                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-3">

                            <div class="px-3 py-3">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                                    MY SPACE
                                </p>

                            </div>

                            <a
                                href="{{ route('account.orders.index') }}"
                                class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm transition hover:bg-[var(--livora-surface)]"
                            >
                            <span>
                                سفارش‌ها
                            </span>

                                <span class="text-[11px] text-[var(--livora-stone)]">
                                {{ \App\Helpers\PersianNumberHelper::format($totalOrders) }}
                            </span>
                            </a>

                            <a
                                href="{{ route('account.wishlist.index') }}"
                                class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm transition hover:bg-[var(--livora-surface)]"
                            >
                            <span>
                                علاقه‌مندی‌ها
                            </span>

                                <span class="text-[11px] text-[var(--livora-stone)]">
                                {{ \App\Helpers\PersianNumberHelper::format($wishlistCount) }}
                            </span>
                            </a>

                            <a
                                href="{{ route('account.addresses.index') }}"
                                class="block rounded-2xl px-3 py-3 text-sm transition hover:bg-[var(--livora-surface)]"
                            >
                                آدرس‌های من
                            </a>

                            <a
                                href="{{ route('account.profile.edit') ?? '#' }}"
                                class="block rounded-2xl px-3 py-3 text-sm transition hover:bg-[var(--livora-surface)]"
                            >
                                اطلاعات حساب
                            </a>

                        </div>


                        {{-- Installment note --}}
                        @if($installmentOrders > 0)

                            <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5 sm:p-6">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    INSTALLMENT
                                </p>

                                <h3 class="mt-3 text-sm font-semibold">
                                    سفارش اقساطی داری
                                </h3>

                                <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                    برای مشاهده وضعیت سفارش و جزئیات پرداخت،
                                    وارد جزئیات سفارش موردنظر شو.
                                </p>

                                <a
                                    href="{{ route('account.orders.index') }}"
                                    class="mt-4 inline-flex text-xs font-medium text-[var(--livora-text)] underline underline-offset-4"
                                >
                                    مشاهده سفارش‌ها
                                </a>

                            </div>

                        @endif


                        {{-- Support --}}
                        <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-6">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                NEED HELP?
                            </p>

                            <h3 class="mt-3 text-sm font-semibold">
                                نیاز به راهنمایی داری؟
                            </h3>

                            <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                برای پرسش درباره سفارش یا خرید با ما در تماس باش.
                            </p>

                            <a
                                href="{{ route('contact') }}"
                                class="mt-4 inline-flex text-xs font-medium text-[var(--livora-text)] underline underline-offset-4"
                            >
                                تماس با فرزین
                            </a>

                        </div>

                    </aside>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
