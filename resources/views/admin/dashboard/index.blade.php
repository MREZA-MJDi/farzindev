@extends('admin.layouts.app')

@section('title', 'داشبورد')
@section('page_title', 'نمای کلی فروشگاه')

@section('content')
    @php
        $maxRevenue = max(1, (float) $monthlyRevenue->max('revenue'));
        $statusLabels = [
            'pending' => 'در انتظار',
            'processing' => 'در حال پردازش',
            'shipped' => 'ارسال‌شده',
            'delivered' => 'تحویل‌شده',
            'cancelled' => 'لغوشده',
        ];
        $statusTotal = max(1, array_sum($orderStatus));
        $quickLinks = [
            ['label' => 'محصولات و تنوع‌ها', 'description' => 'محصول، تصویر، موجودی و واریانت', 'route' => 'admin.products.index'],
            ['label' => 'دسته‌بندی‌ها', 'description' => 'ساختار دسته‌ها و ویژگی‌ها', 'route' => 'admin.categories.index'],
            ['label' => 'سفارش‌ها', 'description' => 'بررسی و پردازش سفارش‌های مشتریان', 'route' => 'admin.orders.index'],
            ['label' => 'مشتریان', 'description' => 'اطلاعات و سوابق خرید', 'route' => 'admin.customers.index'],
            ['label' => 'مدیریت محتوا', 'description' => 'صفحات، بلاگ، رسانه و سئو', 'route' => 'admin.cms.index'],
            ['label' => 'پیام‌های تماس', 'description' => 'پیام‌های دریافتی سایت', 'route' => 'admin.contact-messages.index'],
        ];
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($quickLinks as $link)
                <a href="{{ route($link['route']) }}" class="admin-card admin-card-hover flex items-center justify-between gap-4 p-5">
                    <div class="min-w-0">
                        <h2 class="font-bold text-[var(--admin-text)]">{{ $link['label'] }}</h2>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">{{ $link['description'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-lg text-[var(--admin-accent)]" aria-hidden="true">←</span>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,.8fr)]">
            <div class="admin-card p-5 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[var(--admin-accent)]">REVENUE / LAST 6 MONTHS</p>
                        <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">روند فروش شش ماه اخیر</h2>
                        <p class="mt-1 text-xs text-[var(--admin-muted)]">مبالغ بر اساس سفارش‌های پرداخت‌شده محاسبه شده‌اند.</p>
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-[var(--admin-muted)]">درآمد کل ثبت‌شده</p>
                        <p class="mt-1 text-lg font-bold tabular-nums text-[var(--admin-text)]">{{ \App\Helpers\PersianNumberHelper::format($totalRevenue) }} تومان</p>
                    </div>
                </div>

                <div class="mt-8 grid h-64 grid-cols-6 items-end gap-3 sm:gap-5" role="img" aria-label="نمودار درآمد شش ماه اخیر">
                    @foreach($monthlyRevenue as $month)
                        @php($height = max(3, (int) round(((float) $month['revenue'] / $maxRevenue) * 100)))
                        <div class="flex h-full min-w-0 flex-col items-center justify-end gap-3">
                            <span class="text-center text-[10px] font-semibold tabular-nums text-[var(--admin-muted)]">{{ \App\Helpers\PersianNumberHelper::format($month['orders']) }}</span>
                            <div class="flex h-full w-full items-end overflow-hidden rounded-t-xl bg-[var(--admin-surface)]">
                                <div class="w-full rounded-t-xl bg-[var(--admin-accent)] transition-all" style="height: {{ $height }}%" title="{{ $month['full_label'] }}: {{ number_format($month['revenue']) }} تومان"></div>
                            </div>
                            <span class="text-xs font-medium text-[var(--admin-text-soft)]">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-[10px] text-[var(--admin-muted)]">عدد بالای هر ستون: تعداد سفارش‌های همان ماه</p>
            </div>

            <div class="admin-card p-5 sm:p-7">
                <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[var(--admin-accent)]">ORDER STATUS</p>
                <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">وضعیت سفارش‌ها</h2>
                <p class="mt-1 text-xs text-[var(--admin-muted)]">توزیع سفارش‌ها بر اساس آخرین وضعیت ثبت‌شده.</p>
                <div class="mt-6 space-y-5">
                    @foreach($orderStatus as $status => $count)
                        @php($percentage = (int) round(($count / $statusTotal) * 100))
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-xs">
                                <span class="text-[var(--admin-text-soft)]">{{ $statusLabels[$status] ?? $status }}</span>
                                <strong class="tabular-nums text-[var(--admin-text)]">{{ \App\Helpers\PersianNumberHelper::format($count) }}</strong>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-[var(--admin-surface)]">
                                <div class="h-full rounded-full bg-[var(--admin-accent)]" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-secondary mt-7 w-full justify-center">رفتن به سفارش‌ها</a>
            </div>
        </section>
    </div>
@endsection
