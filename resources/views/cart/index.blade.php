@extends('layouts.app')

@section('title', 'سبد خرید | فرزین')

@section(
    'description',
    'سبد خرید فرزین؛ بررسی محصولات انتخاب‌شده، مبلغ سفارش و شرایط خرید اقساطی قبل از ورود به تسویه حساب.'
)

@section('canonical', route('cart.index'))



@section('content')

    @php
        $cartItems = $cart->items;

        $subtotal = (float) ($currentSubtotal ?? $cart->subtotal());

        $cartReadyForCheckout = (bool) ($cartReadyForCheckout ?? true);

        $itemCount = (int) $cart->itemCount();

        /*
         |--------------------------------------------------------------------------
         | Installment Availability
         |--------------------------------------------------------------------------
         |
         | Internal / provider installment is available only when every
         | product in the cart supports installment sales.
         |
         */

        $installmentAvailable =
            $cartItems->isNotEmpty()
            && $cartItems->every(
                fn ($item) =>
                    $item->product
                    && (bool) $item->product->installment_enabled
                    && filled($item->current_unit_price)
                    && (bool) ($item->is_currently_available ?? false)
                    && ! (bool) ($item->price_changed ?? false)
            );

        $installmentProduct = $cartItems
            ->first()?->product;

        $cashPercent = $installmentAvailable
            ? (int) ($installmentProduct?->installment_cash_percent ?? 0)
            : 0;

        $chequeCount = $installmentAvailable
            ? (int) ($installmentProduct?->installment_cheque_count ?? 0)
            : 0;

        $intervalMonths = $installmentAvailable
            ? (int) ($installmentProduct?->installment_interval_months ?? 0)
            : 0;

        $cashAmount = $installmentAvailable && $cashPercent > 0
            ? round(
                $subtotal * ($cashPercent / 100),
                2
            )
            : 0;

        $deferredAmount = $installmentAvailable
            ? round(
                $subtotal - $cashAmount,
                2
            )
            : 0;

        $chequeAmounts = [];

        if (
            $installmentAvailable
            && $chequeCount > 0
            && $deferredAmount > 0
        ) {
            $baseChequeAmount = round(
                $deferredAmount / $chequeCount,
                2
            );

            $distributed = 0;

            for ($i = 1; $i <= $chequeCount; $i++) {

                if ($i === $chequeCount) {
                    $amount = round(
                        $deferredAmount - $distributed,
                        2
                    );
                } else {
                    $amount = $baseChequeAmount;
                }

                $chequeAmounts[] = $amount;

                $distributed = round(
                    $distributed + $amount,
                    2
                );
            }
        }

        /*
         |--------------------------------------------------------------------------
         | Cart state
         |--------------------------------------------------------------------------
         */

        $isAuthenticated =
            auth()->check();

        $canCheckout =
            $isAuthenticated
            && auth()->user()->isCustomer()
            && $cartReadyForCheckout;

    @endphp

    <div
        class="min-h-[70vh] bg-[var(--livora-cream)]"
        x-data="{
        installmentOpen: false,
        removeOpen: false
    }"
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

                        <span class="text-[var(--livora-text)]">
                        سبد خرید
                    </span>

                    </nav>

                    <div class="mt-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                YOUR SELECTION
                            </p>

                            <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                                سبد خرید
                            </h1>

                            @if($cartItems->isNotEmpty())

                                <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($itemCount) }}
                                    کالا در انتخاب شما قرار دارد.
                                </p>

                            @else

                                <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                                    انتخاب‌های شما هنوز اینجا قرار نگرفته‌اند.
                                </p>

                            @endif

                        </div>

                        @if($cartItems->isNotEmpty())

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex w-fit items-center text-sm font-medium text-[var(--livora-accent)]"
                            >
                                ادامه خرید
                                <span class="mr-2">←</span>
                            </a>

                        @endif

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FLASH
        ========================================================== --}}
        @if(session('success') || session('error'))

            <section class="bg-[var(--livora-cream)]">

                <x-layout.container>

                    <div class="py-5">

                        @if(session('success'))

                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-7 text-emerald-800">
                                {{ session('success') }}
                            </div>

                        @endif

                        @if(session('error'))

                            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-7 text-red-800">
                                {{ session('error') }}
                            </div>

                        @endif

                    </div>

                </x-layout.container>

            </section>

        @endif


        {{-- =========================================================
             EMPTY CART
        ========================================================== --}}
        @if($cartItems->isEmpty())

            <section>

                <x-layout.container>

                    <div class="py-20 sm:py-28">

                        <div class="mx-auto max-w-xl text-center">

                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-[var(--livora-white)] text-[var(--livora-stone)] shadow-sm">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.35"
                                    stroke="currentColor"
                                    class="h-8 w-8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.25 3h1.386c.51 0 .955.343 1.087.836L5.75 7.5m0 0h13.5l1.125-4.5M5.75 7.5l1.5 6h9.5l1.5-6m-8 9.75h4.5M9 19.5a.75.75 0 1 1-1.5 0A.75.75 0 0 1 9 19.5Zm7.5 0a.75.75 0 1 1-1.5 0A.75.75 0 0 1 16.5 19.5Z"
                                    />
                                </svg>

                            </div>

                            <p class="mt-7 text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                NOTHING SELECTED
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight">
                                سبد خرید شما خالی است
                            </h2>

                            <p class="mx-auto mt-4 max-w-md text-sm leading-8 text-[var(--livora-stone)]">
                                محصولات موردنظرتان را از فروشگاه انتخاب کنید
                                و برای ادامه خرید به سبد اضافه کنید.
                            </p>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-8 inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                کشف فروشگاه
                            </a>

                        </div>

                    </div>

                </x-layout.container>

            </section>


            {{-- =========================================================
                 CART CONTENT
            ========================================================== --}}
        @else

            <section>

                <x-layout.container>

                    <div class="grid gap-6 py-8 sm:py-10 lg:grid-cols-[minmax(0,1fr)_390px]">

                        {{-- =================================================
                             LEFT
                        ================================================== --}}
                        <div class="min-w-0 space-y-5">

                            {{-- Cart card --}}
                            <div class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">

                                <div class="border-b border-[var(--livora-border)] px-5 py-5 sm:px-7">

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                        <div>

                                            <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                                CART ITEMS
                                            </p>

                                            <h2 class="mt-2 text-lg font-semibold">
                                                انتخاب‌های شما
                                            </h2>

                                        </div>

                                        <form
                                            action="{{ route('cart.clear') }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-xs text-[var(--livora-stone)] transition hover:text-red-600"
                                                onclick="return confirm('آیا از خالی کردن سبد خرید مطمئن هستید؟')"
                                            >
                                                پاک کردن سبد
                                            </button>
                                        </form>

                                    </div>

                                </div>


                                {{-- Items --}}
                                <div class="divide-y divide-[var(--livora-border)]">

                                    @foreach($cartItems as $item)

                                        @php
                                            $product = $item->product;

                                            $variant = $item->variant;

                                            $variantOptions = collect(
                                                $item->variant_options ?? []
                                            );

                                            $itemStock = (int) (
                                                $item->current_stock
                                                ?? (
                                                    $variantOptions->isNotEmpty()
                                                        ? $variantOptions->min('stock')
                                                        : ($variant?->stock ?? $product?->stock ?? 0)
                                                )
                                            );

                                            $itemUnitPrice = $item->current_unit_price;
                                            $itemPriceChanged = (bool) ($item->price_changed ?? false);
                                            $itemIssueMessage = $item->cart_issue_message;
                                            $itemTotal = (float) ($item->current_line_total ?? 0);

                                            $itemInstallment =
                                                (bool) ($product?->installment_enabled ?? false)
                                                && filled($itemUnitPrice)
                                                && (bool) ($item->is_currently_available ?? false)
                                                && ! $itemPriceChanged;

                                            $itemCashPercent =
                                                $itemInstallment
                                                    ? (int) ($product?->installment_cash_percent ?? 0)
                                                    : 0;

                                            $itemCashAmount =
                                                $itemInstallment
                                                    ? round(
                                                        $itemTotal
                                                        * ($itemCashPercent / 100),
                                                        2
                                                    )
                                                    : 0;
                                        @endphp

                                        @if(! $product)
                                            <article class="flex items-center justify-between gap-4 p-5 sm:p-7">
                                                <div>
                                                    <h3 class="font-semibold">محصول دیگر در دسترس نیست</h3>
                                                    <p class="mt-2 text-sm leading-6 text-[var(--livora-stone)]">
                                                        این کالا حذف شده است و دیگر در مبلغ سبد محاسبه نمی‌شود.
                                                    </p>
                                                </div>
                                                <form action="{{ route('cart.remove', $item) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-xl border border-[var(--livora-border)] px-3 py-2 text-xs font-medium">
                                                        حذف
                                                    </button>
                                                </form>
                                            </article>
                                        @else
                                        <article class="p-5 sm:p-7">

                                            <div class="flex flex-col gap-5 sm:flex-row">

                                                {{-- Image --}}
                                                <a
                                                    href="{{ route('product.show', $product->slug) }}"
                                                    class="group h-32 w-full shrink-0 overflow-hidden rounded-2xl bg-[var(--livora-surface)] sm:h-32 sm:w-28"
                                                >

                                                    @if($product->images->first()?->url)

                                                        <img
                                                            src="{{ $product->images->first()->url }}"
                                                            alt="{{ $product->name }}"
                                                            loading="lazy"
                                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                        >

                                                    @else

                                                        <div class="flex h-full w-full items-center justify-center text-[10px] tracking-[0.18em] text-[var(--livora-stone)]">
                                                            فرزین
                                                        </div>

                                                    @endif

                                                </a>


                                                {{-- Content --}}
                                                <div class="min-w-0 flex-1">

                                                    <div class="flex items-start justify-between gap-4">

                                                        <div class="min-w-0">

                                                            @if($product->category)

                                                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                                                    {{ $product->category->name }}
                                                                </p>

                                                            @endif

                                                            <a
                                                                href="{{ route('product.show', $product->slug) }}"
                                                                class="mt-2 block"
                                                            >

                                                                <h3 class="line-clamp-2 text-base font-semibold leading-6 transition hover:text-[var(--livora-accent)]">
                                                                    {{ $product->name }}
                                                                </h3>

                                                            </a>

                                                            @if($variantOptions->isNotEmpty())

                                                                <div class="mt-3 flex flex-wrap gap-2">

                                                                    @foreach($variantOptions as $option)

                                                                        <span class="rounded-full border border-[var(--livora-border)] bg-[var(--livora-surface)] px-2.5 py-1 text-[10px] text-[var(--livora-stone)]">

                                                                            {{ $option['name'] ?? $option['type'] ?? 'ویژگی' }}:
                                                                            <span class="font-medium text-[var(--livora-text)]">
                                                                                {{ $option['value'] ?? '—' }}
                                                                            </span>

                                                                        </span>

                                                                    @endforeach

                                                                </div>

                                                            @elseif($variant)

                                                                <p class="mt-2 text-xs text-[var(--livora-stone)]">

                                                                    {{ $variant->name }}:
                                                                    {{ $variant->value }}

                                                                </p>

                                                            @endif

                                                            <p class="mt-2 text-[11px] text-[var(--livora-stone)]">
                                                                قیمت واحد:
                                                                @if($itemUnitPrice !== null)
                                                                    {{ \App\Helpers\PersianNumberHelper::format((float) $itemUnitPrice) }} تومان
                                                                @else
                                                                    <span>قیمت در دسترس نیست</span>
                                                                @endif
                                                            </p>

                                                            @if($itemIssueMessage)
                                                                <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-900">
                                                                    {{ $itemIssueMessage }}
                                                                    @if($itemPriceChanged)
                                                                        <span class="block mt-1">قیمت قبلی: {{ \App\Helpers\PersianNumberHelper::format((float) $item->unit_price) }} تومان</span>
                                                                    @endif
                                                                </p>
                                                            @endif

                                                        </div>


                                                        {{-- Remove --}}

                                                            <form
                                                                action="{{ route('cart.remove', $item) }}"
                                                                method="POST"
                                                            >

                                                                @csrf
                                                                @method('DELETE')

                                                                <button
                                                                    type="submit"
                                                                    aria-label="حذف {{ $product->name }}"
                                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[var(--livora-stone)] transition hover:bg-red-50 hover:text-red-600"
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
                                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.77.562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0C7.91 2.756 7 3.74 7 4.92v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                                                        />
                                                                    </svg>

                                                                </button>

                                                            </form>



                                                    </div>


                                                    <div class="mt-6 flex flex-col gap-4 border-t border-[var(--livora-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">

                                                        <div>
                                                            <p class="mb-2 text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                                                تعداد
                                                            </p>

                                                            <form
                                                                action="{{ route('cart.update', $item) }}"
                                                                method="POST"
                                                                class="flex items-center gap-3"
                                                            >
                                                                @csrf
                                                                @method('PATCH')

                                                                <x-shop.quantity
                                                                    name="quantity"
                                                                    :value="$item->quantity"
                                                                    :max="max(1, $itemStock)"
                                                                />

                                                                <button
                                                                    type="submit"
                                                                    class="rounded-xl border border-[var(--livora-border)] px-3 py-2 text-[11px] font-medium text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                                                                >
                                                                    {{ $itemPriceChanged ? 'تأیید قیمت و تعداد' : 'بروزرسانی' }}
                                                                </button>
                                                            </form>
                                                        </div>


                                                        {{-- Total --}}
                                                        <div class="text-right">

                                                            <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                                                مبلغ
                                                            </p>

                                                            <p class="mt-2 text-lg font-semibold">
                                                                @if($itemUnitPrice !== null)
                                                                    {{ \App\Helpers\PersianNumberHelper::format($itemTotal) }}
                                                                    <span class="text-[10px] font-normal text-[var(--livora-stone)]">تومان</span>
                                                                @else
                                                                    <span class="text-sm text-[var(--livora-stone)]">—</span>
                                                                @endif
                                                            </p>

                                                        </div>

                                                    </div>


                                                    {{-- Item Installment --}}
                                                    @if($itemInstallment && $itemCashAmount > 0)

                                                        <div class="mt-5 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

                                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                                                <div>

                                                                    <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-accent)]">
                                                                        INSTALLMENT
                                                                    </p>

                                                                    <p class="mt-1 text-xs font-medium">
                                                                        این محصول امکان خرید اقساطی دارد.
                                                                    </p>

                                                                </div>

                                                                <div class="text-right">

                                                                    <p class="text-[10px] text-[var(--livora-stone)]">
                                                                        پیش‌پرداخت تقریبی
                                                                    </p>

                                                                    <p class="mt-1 text-sm font-semibold">
                                                                        {{ \App\Helpers\PersianNumberHelper::format($itemCashAmount) }}
                                                                        تومان
                                                                    </p>

                                                                </div>

                                                            </div>

                                                        </div>

                                                    @endif

                                                </div>

                                            </div>

                                        </article>
                                        @endif

                                    @endforeach

                                </div>

                            </div>


                            {{-- =================================================
                                 INSTALLMENT SUMMARY
                            ================================================== --}}
                            @if($installmentAvailable)

                                <div class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">

                                    <button
                                        type="button"
                                        @click="installmentOpen = !installmentOpen"
                                        class="flex w-full items-center justify-between gap-5 p-5 text-right sm:p-7"
                                    >

                                        <div>

                                            <p class="text-[10px] uppercase tracking-[0.2em] text-white/45">
                                                FLEXIBLE PAYMENT
                                            </p>

                                            <h2 class="mt-2 text-lg font-semibold">
                                                این سبد قابلیت خرید اقساطی دارد
                                            </h2>

                                            <p class="mt-2 text-xs leading-7 text-white/55">
                                                شرایط اقساط بر اساس تنظیمات محصولات این سبد محاسبه شده است.
                                            </p>

                                        </div>

                                        <div class="shrink-0">

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                                class="h-5 w-5 transition-transform"
                                                :class="installmentOpen ? 'rotate-180' : ''"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                                />
                                            </svg>

                                        </div>

                                    </button>


                                    <div
                                        x-show="installmentOpen"
                                        x-cloak
                                        x-collapse
                                        class="border-t border-white/10"
                                    >

                                        <div class="grid grid-cols-1 gap-px bg-white/10 sm:grid-cols-3">

                                            <div class="bg-[var(--livora-ink)] p-5 sm:p-7">

                                                <p class="text-[10px] text-white/45">
                                                    پیش‌پرداخت
                                                </p>

                                                <p class="mt-2 text-xl font-semibold">
                                                    {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}
                                                </p>

                                                <p class="mt-1 text-[10px] text-white/40">
                                                    {{ \App\Helpers\PersianNumberHelper::format($cashPercent) }}٪
                                                </p>

                                            </div>

                                            <div class="bg-[var(--livora-ink)] p-5 sm:p-7">

                                                <p class="text-[10px] text-white/45">
                                                    باقی‌مانده
                                                </p>

                                                <p class="mt-2 text-xl font-semibold">
                                                    {{ \App\Helpers\PersianNumberHelper::format($deferredAmount) }}
                                                </p>

                                                <p class="mt-1 text-[10px] text-white/40">
                                                    تومان
                                                </p>

                                            </div>

                                            <div class="bg-[var(--livora-ink)] p-5 sm:p-7">

                                                <p class="text-[10px] text-white/45">
                                                    برنامه
                                                </p>

                                                <p class="mt-2 text-xl font-semibold">
                                                    {{ \App\Helpers\PersianNumberHelper::format($chequeCount) }}
                                                    چک
                                                </p>

                                                <p class="mt-1 text-[10px] text-white/40">
                                                    هر {{ \App\Helpers\PersianNumberHelper::format($intervalMonths) }} ماه
                                                </p>

                                            </div>

                                        </div>


                                        <div class="space-y-2 p-5 sm:p-7">

                                            @foreach($chequeAmounts as $index => $amount)

                                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">

                                                    <div>

                                                        <p class="text-xs font-medium">
                                                            چک {{ \App\Helpers\PersianNumberHelper::format($index + 1) }}
                                                        </p>

                                                        <p class="mt-1 text-[10px] text-white/45">
                                                            {{ \App\Helpers\PersianNumberHelper::format(($index + 1) * $intervalMonths) }}
                                                            ماه بعد
                                                        </p>

                                                    </div>

                                                    <p class="text-sm font-semibold">
                                                        {{ \App\Helpers\PersianNumberHelper::format($amount) }}
                                                        تومان
                                                    </p>

                                                </div>

                                            @endforeach

                                        </div>

                                    </div>

                                </div>

                            @else

                                <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-6">

                                    <div class="flex items-start gap-4">

                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                                        i
                                    </span>

                                        <div>

                                            <p class="text-sm font-semibold">
                                                این سبد برای خرید اقساطی یکپارچه نیست
                                            </p>

                                            <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                                برای استفاده از شرایط اقساطی، همه محصولات این سفارش باید
                                                شرایط اقساطی سازگار داشته باشند.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            @endif

                        </div>


                        {{-- =================================================
                             RIGHT SUMMARY
                        ================================================== --}}
                        <aside class="lg:sticky lg:top-28 lg:self-start">

                            <div class="overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">

                                <div class="p-6 sm:p-7">

                                    <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                        ORDER SUMMARY
                                    </p>

                                    <h2 class="mt-2 text-lg font-semibold">
                                        خلاصه سفارش
                                    </h2>


                                    <div class="mt-7 space-y-4">

                                        <div class="flex items-center justify-between gap-4">

                                        <span class="text-sm text-[var(--livora-stone)]">
                                            کالاها
                                        </span>

                                            <span class="text-sm font-medium">
                                            {{ \App\Helpers\PersianNumberHelper::format($itemCount) }}
                                        </span>

                                        </div>

                                        <div class="flex items-center justify-between gap-4">

                                        <span class="text-sm text-[var(--livora-stone)]">
                                            مبلغ محصولات
                                        </span>

                                            <span class="text-sm font-semibold">
                                            {{ \App\Helpers\PersianNumberHelper::format($subtotal) }}
                                            تومان
                                        </span>

                                        </div>

                                    </div>


                                    <div class="my-6 h-px bg-[var(--livora-border)]"></div>


                                    <div class="flex items-end justify-between gap-4">

                                        <div>

                                            <p class="text-xs text-[var(--livora-stone)]">
                                                جمع سبد
                                            </p>

                                            <p class="mt-2 text-2xl font-semibold tracking-tight">
                                                {{ \App\Helpers\PersianNumberHelper::format($subtotal) }}
                                            </p>

                                        </div>

                                        <span class="text-[10px] text-[var(--livora-stone)]">
                                        تومان
                                    </span>

                                    </div>


                                    @if($installmentAvailable)

                                        <div class="mt-6 rounded-2xl bg-[var(--livora-ink)] p-5 text-white">

                                            <p class="text-[10px] uppercase tracking-[0.16em] text-white/45">
                                                INSTALLMENT
                                            </p>

                                            <p class="mt-2 text-xs text-white/60">
                                                شروع پرداخت
                                            </p>

                                            <p class="mt-2 text-xl font-semibold">
                                                {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}
                                                تومان
                                            </p>

                                            <p class="mt-2 text-[10px] leading-6 text-white/40">
                                                {{ \App\Helpers\PersianNumberHelper::format($cashPercent) }}٪
                                                پیش‌پرداخت +
                                                {{ \App\Helpers\PersianNumberHelper::format($chequeCount) }}
                                                فقره چک
                                            </p>

                                        </div>

                                    @endif


                                    @if($canCheckout)

                                        <a
                                            href="{{ route('checkout.index') }}"
                                            class="mt-6 flex w-full items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                        >
                                            ادامه به تسویه حساب
                                        </a>

                                    @elseif($isAuthenticated && auth()->user()->isCustomer())

                                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                            <p class="text-xs leading-7 text-amber-900">
                                                قبل از تسویه، هشدارهای سبد را برطرف کنید: قیمت‌های جدید را تأیید کنید، تعداد را با موجودی تطبیق دهید یا کالاهای ناموجود را حذف کنید.
                                            </p>
                                        </div>

                                    @else

                                        <div class="mt-6 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

                                            <p class="text-xs leading-7 text-[var(--livora-stone)]">
                                                برای ادامه به تسویه حساب، ابتدا وارد حساب مشتری شوید.
                                            </p>

                                            <a
                                                href="{{ route('login') }}"
                                                class="mt-3 inline-flex text-xs font-medium text-[var(--livora-text)] underline underline-offset-4"
                                            >
                                                ورود به حساب
                                            </a>

                                        </div>

                                    @endif


                                    <a
                                        href="{{ route('shop.index') }}"
                                        class="mt-3 flex w-full items-center justify-center rounded-2xl border border-[var(--livora-border)] px-6 py-3.5 text-sm font-medium text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                                    >
                                        ادامه خرید
                                    </a>

                                </div>


                                {{-- Trust --}}
                                <div class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)] p-5 sm:p-6">

                                    <div class="space-y-4">

                                        <div class="flex gap-3">

                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-white)] text-[10px] font-semibold">
                                            01
                                        </span>

                                            <div>

                                                <p class="text-xs font-semibold">
                                                    قیمت شفاف
                                                </p>

                                                <p class="mt-1 text-[10px] leading-6 text-[var(--livora-stone)]">
                                                    مبلغ هر کالا و جمع سبد در همین صفحه مشخص است.
                                                </p>

                                            </div>

                                        </div>

                                        <div class="flex gap-3">

                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-white)] text-[10px] font-semibold">
                                            02
                                        </span>

                                            <div>

                                                <p class="text-xs font-semibold">
                                                    شرایط اقساط
                                                </p>

                                                <p class="mt-1 text-[10px] leading-6 text-[var(--livora-stone)]">
                                                    در صورت سازگاری محصولات، مبلغ پیش‌پرداخت و چک‌ها نمایش داده می‌شود.
                                                </p>

                                            </div>

                                        </div>

                                        <div class="flex gap-3">

                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-white)] text-[10px] font-semibold">
                                            03
                                        </span>

                                            <div>

                                                <p class="text-xs font-semibold">
                                                    انتخاب آزاد
                                                </p>

                                                <p class="mt-1 text-[10px] leading-6 text-[var(--livora-stone)]">
                                                    برای بررسی مدل‌های دیگر می‌توانید هر زمان به فروشگاه برگردید.
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </aside>

                    </div>

                </x-layout.container>

            </section>

        @endif

    </div>

@endsection
