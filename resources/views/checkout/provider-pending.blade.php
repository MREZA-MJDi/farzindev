@extends('layouts.app')

@section('title', 'وضعیت اتصال درگاه | فرزین')
@section('description', 'وضعیت آماده‌سازی مسیر امن پرداخت سفارش شما در فرزین.')
@section('canonical', route('checkout.payment', $order))

@push('seo')
    <meta name="robots" content="noindex,nofollow">
@endpush

@section('content')
    <section class="min-h-[65vh] bg-[var(--livora-cream)] py-12 sm:py-20">
        <x-layout.container>
            <div class="mx-auto max-w-2xl overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] shadow-sm">
                <div class="border-b border-[var(--livora-border)] bg-[var(--livora-ink)] px-6 py-7 text-white sm:px-9">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/55">PAYMENT INTEGRATION STATUS</p>
                    <h1 class="mt-3 text-2xl font-semibold sm:text-3xl">{{ $provider['name'] }}</h1>
                    <p class="mt-2 text-sm text-white/70">سفارش {{ $order->order_number }}</p>
                </div>

                <div class="space-y-6 p-6 sm:p-9">
                    @if($order->payment_status === 'paid')
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900" role="status">
                            <h2 class="font-semibold">این سفارش قبلاً پرداخت شده است.</h2>
                            <p class="mt-2 text-sm leading-7">برای جلوگیری از پرداخت تکراری، امکان شروع درگاه جدید از این صفحه وجود ندارد.</p>
                        </div>
                    @elseif($isReady)
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900" role="status">
                            <h2 class="font-semibold">تنظیمات اولیهٔ اتصال آماده است.</h2>
                            <p class="mt-2 text-sm leading-7">برای شروع پرداخت، به صفحهٔ انتخاب روش پرداخت برگردید و همین درگاه را از مسیر معمول انتخاب کنید.</p>
                        </div>
                    @else
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xl" aria-hidden="true">⌁</span>
                            <div>
                                <h2 class="text-lg font-semibold text-[var(--livora-text)]">اتصال درگاه در حال آماده‌سازی است</h2>
                                <p class="mt-2 text-sm leading-8 text-[var(--livora-stone)]">{{ $provider['detail'] }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950">
                            <p class="text-sm font-semibold">در حال حاضر پرداختی آغاز نشده است.</p>
                            <p class="mt-2 text-xs leading-7">تا زمان دریافت و پیاده‌سازی مستندات رسمی پذیرنده و تأیید تست تراکنش، انتقال به صفحهٔ بانکی یا نمایش موفقیت پرداخت غیرفعال می‌ماند. در این مرحله هیچ مبلغی از طریق این مسیر کسر نشده است.</p>
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 sm:flex-row">
                        @if($order->payment_status === 'paid')
                            <a href="{{ route('account.orders.show', $order) }}" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-accent)]">مشاهده سفارش</a>
                        @else
                            <a href="{{ route('checkout.payment', $order) }}" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-accent)]">بازگشت به روش‌های پرداخت</a>
                        @endif
                        <a href="{{ route('account.orders.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] px-5 py-3.5 text-sm font-medium text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]">سفارش‌های من</a>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
