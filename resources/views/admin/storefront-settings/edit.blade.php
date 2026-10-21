@extends('admin.layouts.app')

@section('title', 'تنظیمات فروشگاه')
@section('page_title', 'تنظیمات فروشگاه')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">STOREFRONT / SETTINGS</p>
            <h1 class="admin-title mt-2">بنر فروشگاه و راه‌های ارتباطی</h1>
            <p class="admin-subtitle mt-2">بنر صفحه فروشگاه و اطلاعات واقعی تماس را از همین بخش مدیریت کنید.</p>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.storefront-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="admin-card p-5 sm:p-7">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[var(--admin-text)]">بنر صفحه فروشگاه</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تصویر بنر اختیاری است. وقتی تصویر ثبت کنید، در بالای صفحه فروشگاه نمایش داده می‌شود؛ انیمیشن ثابت هود جای آن را نمی‌گیرد.</p>
                </div>

                <div class="admin-image-preview mb-5 aspect-[16/5] min-h-[180px]">
                    <img
                        id="shop-banner-preview"
                        data-image-editor-existing-preview
                        src="{{ $shopBannerUrl ?: '' }}"
                        alt="{{ $shopBannerTitle }}"
                        class="admin-image {{ $shopBannerUrl ? '' : 'hidden' }}"
                    >
                    <div
                        id="shop-banner-placeholder"
                        data-image-editor-placeholder
                        class="{{ $shopBannerUrl ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-6 text-center"
                    >
                        <span class="text-sm font-semibold text-[var(--admin-text)]">هنوز بنری انتخاب نشده</span>
                        <span class="mt-2 text-xs text-[var(--admin-muted)]">یک تصویر عریض برای بنر فروشگاه انتخاب کنید.</span>
                    </div>
                </div>

                <label for="shop_banner_image" class="admin-label">تصویر بنر</label>
                <input
                    id="shop_banner_image"
                    name="shop_banner_image"
                    type="file"
                    data-image-editor-input
                    data-image-editor-default-ratio="16:9"
                    data-image-editor-min-width="1200"
                    data-image-editor-min-height="600"
                    accept="image/jpeg,image/png,image/webp"
                    class="admin-input p-2"
                >

                <div data-image-editor-panel hidden class="mt-5 space-y-4 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                    <strong class="block text-sm font-semibold text-[var(--admin-text)]">تنظیم کادر بنر</strong>
                    <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش برش بنر" class="mx-auto block max-w-full rounded-xl bg-[var(--admin-bg)]" style="width:min(100%,960px);height:auto;aspect-ratio:16/9;touch-action:none;cursor:grab;"></canvas>
                    <p class="text-xs leading-6 text-[var(--admin-muted)]">کادر را با کشیدن تصویر تنظیم کنید، زوم را تغییر دهید و پیش از ذخیره نتیجه را ببینید.</p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="block text-xs text-[var(--admin-text)]">نسبت تصویر
                            <select data-image-editor-ratio class="admin-select mt-2">
                                <option value="1:1">مربع ۱:۱</option>
                                <option value="4:3">استاندارد ۴:۳</option>
                                <option value="4:5">پرتره ۴:۵</option>
                                <option value="16:9" selected>بنر عریض ۱۶:۹</option>
                            </select>
                        </label>
                        <label class="block text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                            <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                        </label>
                        <div class="flex flex-wrap items-end gap-2">
                            <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">↶ چرخش</button>
                            <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش ↷</button>
                            <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button>
                        </div>
                    </div>
                    <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر را تنظیم کنید.</p>
                </div>

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">فرمت‌های مجاز JPG، PNG و WEBP؛ حداکثر حجم فایل ۲۰ مگابایت. ویرایشگر قبل از ارسال، تصویر را برش می‌دهد.</p>
                @error('shop_banner_image')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                @enderror

                @if ($shopBannerPath)
                    <label class="mt-5 flex items-center gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 text-xs text-[var(--admin-text-soft)]">
                        <input type="hidden" name="remove_shop_banner" value="0">
                        <input type="checkbox" name="remove_shop_banner" value="1" class="admin-checkbox h-4 w-4">
                        حذف بنر فعلی از صفحه فروشگاه
                    </label>
                @endif

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="shop_banner_title" class="admin-label">عنوان بنر</label>
                        <input id="shop_banner_title" name="shop_banner_title" type="text" value="{{ old('shop_banner_title', $shopBannerTitle) }}" class="admin-input" maxlength="255" required>
                        @error('shop_banner_title')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="shop_banner_subtitle" class="admin-label">توضیح کوتاه</label>
                        <input id="shop_banner_subtitle" name="shop_banner_subtitle" type="text" value="{{ old('shop_banner_subtitle', $shopBannerSubtitle) }}" class="admin-input" maxlength="500">
                        @error('shop_banner_subtitle')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-7">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[var(--admin-text)]">هزینه ارسال</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">مبالغ به تومان هستند. هزینه ثابت برای سفارش‌هایی که به حد ارسال رایگان نرسند اعمال می‌شود؛ مقدار صفر یعنی ارسال رایگان.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="shipping_flat_rate" class="admin-label">هزینه ثابت ارسال (تومان)</label>
                        <input id="shipping_flat_rate" name="shipping_flat_rate" type="number" min="0" max="1000000000000" step="1" required value="{{ old('shipping_flat_rate', $shippingFlatRate) }}" class="admin-input" inputmode="numeric">
                        @error('shipping_flat_rate')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="shipping_free_threshold" class="admin-label">حداقل مبلغ برای ارسال رایگان (تومان)</label>
                        <input id="shipping_free_threshold" name="shipping_free_threshold" type="number" min="0" max="1000000000000" step="1" value="{{ old('shipping_free_threshold', $shippingFreeThreshold ?: '') }}" class="admin-input" inputmode="numeric" placeholder="خالی یا صفر = غیرفعال">
                        @error('shipping_free_threshold')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-7">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[var(--admin-text)]">اطلاعات تماس واقعی</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">شماره تماس، ایمیل و آدرس این بخش در صفحه «تماس با ما» استفاده می‌شود. نقشه گوگل از روی آدرس واردشده ساخته می‌شود.</p>
                </div>

                @php($contact = $contactSetting)
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="admin-label">شماره تماس</label>
                        <input id="phone" name="phone" type="text" dir="ltr" value="{{ old('phone', $contact?->phone) }}" class="admin-input" maxlength="50" placeholder="شماره تماس واقعی">
                        @error('phone')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="admin-label">ایمیل</label>
                        <input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $contact?->email) }}" class="admin-input" maxlength="255" placeholder="info@example.com">
                        @error('email')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="admin-label">آدرس دقیق برای نمایش و نقشه گوگل</label>
                        <textarea id="address" name="address" rows="3" class="admin-input" maxlength="2000" placeholder="استان، شهر، خیابان، پلاک...">{{ old('address', $contact?->address) }}</textarea>
                        @error('address')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="whatsapp" class="admin-label">شماره واتساپ (اختیاری)</label>
                        <input id="whatsapp" name="whatsapp" type="text" dir="ltr" value="{{ old('whatsapp', $contact?->whatsapp) }}" class="admin-input" maxlength="30" placeholder="+98912...">
                        @error('whatsapp')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="instagram_url" class="admin-label">لینک اینستاگرام (اختیاری)</label>
                        <input id="instagram_url" name="instagram_url" type="url" dir="ltr" value="{{ old('instagram_url', $contact?->instagram_url) }}" class="admin-input" maxlength="255" placeholder="https://instagram.com/your-page">
                        @error('instagram_url')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="hours" class="admin-label">ساعات کاری</label>
                        <input id="hours" name="hours" type="text" value="{{ old('hours', $contact?->hours) }}" class="admin-input" maxlength="255">
                        @error('hours')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone_label" class="admin-label">عنوان تماس</label>
                        <input id="phone_label" name="phone_label" type="text" value="{{ old('phone_label', $contact?->phone_label ?: 'تماس تلفنی') }}" class="admin-input" maxlength="100">
                        @error('phone_label')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email_label" class="admin-label">عنوان ایمیل</label>
                        <input id="email_label" name="email_label" type="text" value="{{ old('email_label', $contact?->email_label ?: 'ایمیل') }}" class="admin-input" maxlength="100">
                        @error('email_label')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="support_label" class="admin-label">عنوان پشتیبانی</label>
                        <input id="support_label" name="support_label" type="text" value="{{ old('support_label', $contact?->support_label ?: 'پشتیبانی') }}" class="admin-input" maxlength="100">
                        @error('support_label')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="support_description" class="admin-label">توضیح پشتیبانی</label>
                        <input id="support_description" name="support_description" type="text" value="{{ old('support_description', $contact?->support_description ?: 'پاسخ‌گویی به پرسش‌های خرید و سفارش.') }}" class="admin-input" maxlength="500">
                        @error('support_description')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="online_label" class="admin-label">عنوان ارتباط آنلاین</label>
                        <input id="online_label" name="online_label" type="text" value="{{ old('online_label', $contact?->online_label ?: 'ارتباط آنلاین') }}" class="admin-input" maxlength="100">
                        @error('online_label')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="online_description" class="admin-label">توضیح ارتباط آنلاین</label>
                        <input id="online_description" name="online_description" type="text" value="{{ old('online_description', $contact?->online_description ?: 'پیام خود را ارسال کنید تا با شما تماس بگیریم.') }}" class="admin-input" maxlength="500">
                        @error('online_description')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary">بازگشت</a>
                <button type="submit" class="admin-btn admin-btn-primary">ذخیره همه تنظیمات</button>
            </div>
        </form>
    </div>
@endsection
