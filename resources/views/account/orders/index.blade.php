@extends('layouts.app')

@section('title', 'سفارش‌های من | فرزین')

@section(
    'description',
    'مشاهده و مدیریت سفارش‌های شما در فرزین.'
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
    @endphp

    <div class="min-h-[70vh] bg-[var(--livora-cream)]">

        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
            <x-layout.container>

                <div class="py-8 sm:py-12">

                    <nav class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">
                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('account.index') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            حساب کاربری
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        سفارش‌ها
                    </span>
                    </nav>

                    <div class="mt-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                MY ORDERS
                            </p>

                            <h1 class="mt-3 text-4xl font-semibold tracking-tight">
                                سفارش‌های من
                            </h1>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                تاریخچه سفارش‌ها، وضعیت پرداخت و جزئیات هر خرید را اینجا ببین.
                            </p>
                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex w-fit items-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                        >
                            خرید جدید
                        </a>

                    </div>

                </div>

            </x-layout.container>
        </section>


        <section>
            <x-layout.container>

                @if($orders->isEmpty())

                    <div class="py-20 sm:py-28">

                        <div class="mx-auto max-w-xl text-center">

                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-[var(--livora-white)] text-[var(--livora-stone)]">
                                —
                            </div>

                            <p class="mt-7 text-[10px] uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                NO ORDERS
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold">
                                هنوز سفارشی ثبت نکرده‌ای
                            </h2>

                            <p class="mx-auto mt-4 max-w-md text-sm leading-8 text-[var(--livora-stone)]">
                                اولین انتخابت را از مجموعه فرزین پیدا کن.
                            </p>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-8 inline-flex rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                ورود به فروشگاه
                            </a>

                        </div>

                    </div>

                @else

                    <div class="py-8 sm:py-10">

                        <div class="space-y-4">

                            @foreach($orders as $order)

                                <article class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">

                                    <a
                                        href="{{ route('account.orders.show', $order) }}"
                                        class="block p-5 transition hover:bg-[var(--livora-surface)] sm:p-7"
                                    >

                                        <div class="flex flex-col gap-6">

                                            {{-- Header --}}
                                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                                <div>

                                                    <div class="flex flex-wrap items-center gap-2">

                                                    <span class="text-sm font-semibold">
                                                        {{ $order->order_number }}
                                                    </span>

                                                        <x-ui.badge
                                                            :variant="$statusVariants[$order->status] ?? 'warning'"
                                                        >
                                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                                        </x-ui.badge>

                                                    </div>

                                                    <p class="mt-2 text-[11px] text-[var(--livora-stone)]">
                                                        {{ $order->created_at ? \App\Helpers\JalaliHelper::format($order->created_at, 'Y/m/d') : '—' }}
                                                    </p>

                                                </div>

                                                <div class="text-right">

                                                    <p class="text-lg font-semibold">
                                                        {{ \App\Helpers\PersianNumberHelper::format((float) $order->total) }}
                                                        <span class="text-[10px] font-normal text-[var(--livora-stone)]">
                                                        تومان
                                                    </span>
                                                    </p>

                                                    <div class="mt-2">
                                                        <x-ui.badge
                                                            :variant="$paymentVariants[$order->payment_status] ?? 'warning'"
                                                        >
                                                            {{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}
                                                        </x-ui.badge>
                                                    </div>

                                                </div>

                                            </div>


                                            {{-- Meta --}}
                                            <div class="flex flex-wrap gap-2 text-[11px]">

                                            <span class="rounded-full border border-[var(--livora-border)] px-3 py-1.5 text-[var(--livora-stone)]">
                                                {{ \App\Helpers\PersianNumberHelper::format($order->items->count()) }}
                                                محصول
                                            </span>

                                                @if($order->payment_method === 'installment')

                                                    <span class="rounded-full border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-1.5 text-[var(--livora-accent)]">
                                                    خرید اقساطی
                                                </span>

                                                @endif

                                                @if($order->payment_provider)

                                                    <span class="rounded-full border border-[var(--livora-border)] px-3 py-1.5 text-[var(--livora-stone)]">
                                                    {{ $order->payment_provider }}
                                                </span>

                                                @endif

                                            </div>


                                            {{-- Preview Items --}}
                                            @if($order->items->isNotEmpty())

                                                <div class="flex gap-3 overflow-x-auto pb-1">

                                                    @foreach($order->items->take(4) as $item)

                                                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-[var(--livora-surface)]">

                                                            @if($item->product?->images?->first()?->url)

                                                                <img
                                                                    src="{{ $item->product->images->first()->url }}"
                                                                    alt="{{ $item->product_name }}"
                                                                    class="h-full w-full object-cover"
                                                                    loading="lazy"
                                                                >

                                                            @else

                                                                <div class="flex h-full w-full items-center justify-center text-[9px] tracking-wider text-[var(--livora-stone)]">
                                                                    فرزین
                                                                </div>

                                                            @endif

                                                        </div>

                                                    @endforeach

                                                </div>

                                            @endif


                                            {{-- Footer --}}
                                            <div class="flex items-center justify-between border-t border-[var(--livora-border)] pt-5">

                                            <span class="text-xs text-[var(--livora-stone)]">
                                                مشاهده جزئیات سفارش
                                            </span>

                                                <span class="text-[var(--livora-ink)] transition-transform group-hover:-translate-x-1">
                                                ←
                                            </span>

                                            </div>

                                        </div>

                                    </a>

                                </article>

                            @endforeach

                        </div>

                        <div class="mt-8">
                            {{ $orders->withQueryString()->links() }}
                        </div>

                    </div>

                @endif

            </x-layout.container>
        </section>

    </div>

@endsection
