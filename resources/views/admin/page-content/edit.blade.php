@extends('admin.layouts.app')

@section('title', 'محتوای صفحات عمومی')
@section('page_title', 'CMS صفحات عمومی')

@section('content')
    <div class="space-y-6" data-page-content-cms>
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--admin-accent)]">FARZIN / CONTENT MANAGEMENT</p>
                <h1 class="admin-title mt-2">محتوا و ظاهر صفحات عمومی</h1>
                <p class="admin-subtitle mt-2 max-w-3xl">متن‌های عمومی، نمایش بخش‌ها و تایپوگرافی را از اینجا کنترل کنید. تغییرات پس از ذخیره از همان داده‌های ذخیره‌شده در صفحات عمومی خوانده می‌شوند.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-secondary">مشاهده سایت</a>
                @php
                    $previewUrl = match ($tab) {
                        'about' => route('about'),
                        'legal' => route('terms'),
                        default => route('home'),
                    };
                @endphp
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-primary">پیش‌نمایش تب جاری</a>
            </div>
        </header>

        @if(session('success'))
            <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <nav class="grid grid-cols-2 gap-2 sm:grid-cols-4" aria-label="تب‌های مدیریت محتوا">
            @foreach($groups as $key => $item)
                <a href="{{ route('admin.page-content.edit', ['tab' => $key]) }}"
                   @class([
                       'rounded-2xl border px-4 py-4 text-sm font-semibold transition',
                       'border-[var(--admin-accent)] bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => $tab === $key,
                       'border-[var(--admin-border)] bg-[var(--admin-card)] text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)]' => $tab !== $key,
                   ])
                   @if($tab === $key) aria-current="page" @endif>
                    <span class="block">{{ $item['title'] }}</span>
                    <span class="mt-1 block text-[10px] font-normal opacity-70">{{ count($item['fields']) }} تنظیم</span>
                </a>
            @endforeach
        </nav>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-5 sm:px-7">
                <h2 class="text-base font-bold text-[var(--admin-text)]">{{ $group['title'] }}</h2>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">{{ $group['description'] }}</p>
            </div>

            <form method="POST" action="{{ route('admin.page-content.update') }}" enctype="multipart/form-data" class="space-y-6 p-5 sm:p-7">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($tab === 'home')
                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 text-xs leading-6 text-[var(--admin-muted)]">
                        متن Hero و عنوان بخش‌ها را از اینجا ویرایش کنید. نام و تعداد دسته‌ها و محصولات از کاتالوگ؛ مشخصات فنی از فرم محصول؛ تلفن، نشانی و واتساپ از تنظیمات تماس؛ و شرایط اقساط از تنظیمات همان محصول خوانده می‌شوند.
                    </div>
                    @php
                        $heroImageValue = trim((string) ($values['page_home_hero_image_url'] ?? ''));
                        $heroImagePreview = '';
                        if ($heroImageValue !== '') {
                            $heroImagePreview = \Illuminate\Support\Str::startsWith($heroImageValue, ['http://', 'https://', '//'])
                                ? $heroImageValue
                                : \Illuminate\Support\Facades\Storage::disk('public')->url(
                                    preg_replace('#^(public/|storage/)#', '', ltrim($heroImageValue, '/')) ?? $heroImageValue
                                );
                        }
                    @endphp
                    <div class="grid gap-5 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 sm:grid-cols-[minmax(0,1.2fr)_minmax(250px,.8fr)] sm:p-5">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-[var(--admin-text)]">تصویر واقعی Hero</h3>
                            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">عکس یک آشپزخانه واقعی با هود یا سینک نصب‌شده را بارگذاری کنید. تصویر جایگزین قبلی می‌شود؛ تصویر نمایشی یا ساختگی به‌صورت خودکار انتخاب نمی‌شود.</p>
                            <label for="page_home_hero_image" class="admin-label mt-4 block">انتخاب تصویر آشپزخانه</label>
                            <input id="page_home_hero_image" name="page_home_hero_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full p-2">
                            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">فرمت JPG، PNG یا WEBP؛ حداقل ۱۲۰۰×۷۰۰ پیکسل و حداکثر ۲۰ مگابایت. برای تغییر نقطه تمرکز تصویر، تنظیم کنار فیلدهای متن را انتخاب کنید.</p>
                            @error('page_home_hero_image')<p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                        </div>
                        <div class="min-w-0">
                            @if($heroImagePreview !== '')
                                <img src="{{ $heroImagePreview }}" alt="پیش‌نمایش تصویر فعلی Hero" class="aspect-[16/10] w-full rounded-xl border border-[var(--admin-border)] object-cover" loading="lazy">
                                <p class="mt-2 text-xs text-[var(--admin-muted)]">تصویر فعلی روی صفحه اصلی نمایش داده می‌شود.</p>
                            @else
                                <div class="flex aspect-[16/10] items-center justify-center rounded-xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-card)] p-5 text-center text-xs leading-6 text-[var(--admin-muted)]">هنوز تصویر واقعی برای Hero بارگذاری نشده است.</div>
                            @endif
                        </div>
                    </div>

                @elseif($tab === 'about')
                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 text-xs leading-6 text-[var(--admin-muted)]">
                        اطلاعات فنی و ادعاهای تجاری را فقط با داده تأییدشده مجموعه جایگزین کنید. این فیلدها متن ساده‌اند و HTML یا اسکریپت اجرا نمی‌کنند.
                    </div>
                @elseif($tab === 'legal')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-900">
                        متن قوانین و حریم خصوصی بهتر است پیش از انتشار توسط مسئول حقوقی/کسب‌وکار تأیید شود. متن‌ها به‌صورت امن و بدون اجرای HTML نمایش داده می‌شوند.
                    </div>
                @endif

                <div class="grid gap-5 xl:grid-cols-2">
                    @foreach($group['fields'] as $key => $field)
                        @php
                            $value = old($key, $values[$key] ?? '');
                            $isWide = ($field['type'] ?? '') === 'textarea';
                        @endphp
                        <div @class(['min-w-0', 'xl:col-span-2' => $isWide])>
                            @if($field['type'] === 'toggle')
                                <label class="flex min-h-14 cursor-pointer items-center justify-between gap-4 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-4 py-3">
                                    <span class="text-sm font-semibold text-[var(--admin-text)]">{{ $field['label'] }}</span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-[10px] text-[var(--admin-muted)]">نمایش</span>
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input type="checkbox" name="{{ $key }}" value="1" @checked((string) $value === '1') class="admin-checkbox h-4 w-4">
                                    </span>
                                </label>
                            @else
                                <label for="{{ $key }}" class="admin-label">{{ $field['label'] }}</label>
                                @if($field['type'] === 'textarea')
                                    <textarea id="{{ $key }}" name="{{ $key }}" rows="3" maxlength="{{ $field['max'] }}" class="admin-textarea mt-2 w-full">{{ $value }}</textarea>
                                @elseif($field['type'] === 'select')
                                    <select id="{{ $key }}" name="{{ $key }}" class="admin-select mt-2 w-full">
                                        @foreach($field['options'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="{{ $key }}" name="{{ $key }}" type="{{ $field['type'] === 'number' ? 'number' : 'text' }}"
                                           value="{{ $value }}" @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                           @if(isset($field['max']) && $field['type'] === 'number') max="{{ $field['max'] }}" @elseif(isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                                           @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                           class="admin-input mt-2 w-full @if($field['type'] === 'number') text-left @endif"
                                           @if($field['type'] === 'number') dir="ltr" inputmode="decimal" @endif>
                                @endif
                            @endif
                            @error($key)<p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3 border-t border-[var(--admin-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-6 text-[var(--admin-muted)]">ذخیره هر تب فقط تنظیمات همان تب را به‌روزرسانی می‌کند؛ سایر محتواها دست‌نخورده می‌مانند.</p>
                    <button type="submit" class="admin-btn admin-btn-primary min-w-36">ذخیره تغییرات</button>
                </div>
            </form>
        </section>
    </div>
@endsection
