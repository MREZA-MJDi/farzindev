@extends('admin.layouts.app')

@section('title', 'مدیریت محتوای سایت')
@section('page_title', 'مرکز مدیریت محتوا')

@section('content')
    @php
        $tabs = [
            'content' => ['label' => 'صفحات سایت', 'hint' => 'متن‌ها و محتوای صفحات عمومی'],
            'blog' => ['label' => 'بلاگ و اسلاگ‌ها', 'hint' => 'مقاله‌ها، دسته‌ها و برچسب‌ها'],
            'seo' => ['label' => 'سئو', 'hint' => 'عنوان، توضیحات، ایندکس و داده ساختاریافته'],
            'storefront' => ['label' => 'بنر و تماس', 'hint' => 'بنرهای فعال و اطلاعات ارتباطی'],
            'media' => ['label' => 'رسانه‌ها', 'hint' => 'کتابخانه و تصاویر آپلودشده'],
        ];
    @endphp

    <div class="space-y-6">
        <header>
            <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[var(--admin-accent)]">FARAJI HOME / CONTENT WORKSPACE</p>
            <h1 class="admin-title mt-2">مرکز مدیریت محتوای سایت</h1>
            <p class="admin-subtitle mt-2">تنظیمات مرتبط در یک فضای واحد دسته‌بندی شده‌اند. فهرست‌های بزرگ فقط هنگام بازکردن تب مربوط به خودشان از پایگاه داده خوانده می‌شوند.</p>
        </header>

        <nav class="flex flex-wrap gap-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-2" aria-label="بخش‌های مدیریت محتوا">
            @foreach($tabs as $key => $item)
                <a href="{{ route('admin.cms.index', ['tab' => $key]) }}"
                   @class(['rounded-xl px-4 py-3 text-xs font-semibold transition', 'bg-[var(--admin-text)] text-white' => $tab === $key, 'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)]' => $tab !== $key])>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <section class="admin-card p-5 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-[var(--admin-text)]">{{ $tabs[$tab]['label'] }}</h2>
                    <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ $tabs[$tab]['hint'] }}</p>
                </div>
                @if($tab === 'blog')
                    <a href="{{ route('admin.blog-posts.create') }}" class="admin-btn admin-btn-primary">+ مقاله جدید</a>
                @elseif($tab === 'media')
                    <a href="{{ route('admin.media.index') }}" class="admin-btn admin-btn-primary">بازکردن کتابخانه رسانه</a>
                @endif
            </div>

            @if($tab === 'content')
                <div class="mt-6 space-y-3">
                    <details open class="rounded-xl border border-[var(--admin-border)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold">محتوای صفحات عمومی</summary>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">عنوان‌ها، توضیحات و تصاویر بخش‌های صفحه اصلی، درباره ما، تماس و صفحات محتوایی.</p>
                        <a class="admin-btn admin-btn-secondary mt-3" href="{{ route('admin.page-content.edit') }}">مدیریت محتوای صفحات</a>
                    </details>
                    <details class="rounded-xl border border-[var(--admin-border)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold">ظاهر و تصاویر مجله</summary>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تصاویر Hero و بخش پایانی بلاگ و تنظیمات نمایش آن‌ها.</p>
                        <a class="admin-btn admin-btn-secondary mt-3" href="{{ route('admin.blog-appearance.edit') }}">تنظیم ظاهر مجله</a>
                    </details>
                </div>
            @elseif($tab === 'blog')
                <form method="GET" action="{{ route('admin.cms.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <input type="hidden" name="tab" value="blog">
                    <input name="q" value="{{ request('q') }}" class="admin-input min-w-0 flex-1" placeholder="جستجوی عنوان، نام یا slug مقاله و دسته">
                    <button class="admin-btn admin-btn-secondary" type="submit">جستجو</button>
                    <a href="{{ route('admin.cms.index', ['tab' => 'blog']) }}" class="admin-btn admin-btn-ghost">پاک‌کردن</a>
                </form>

                <div class="mt-7">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-bold text-[var(--admin-text)]">مقاله‌ها</h3>
                        <span class="text-xs text-[var(--admin-muted)]">{{ $posts->total() }} مورد</span>
                    </div>
                    <div class="admin-table-wrap overflow-x-auto">
                        <table class="admin-table">
                            <thead><tr><th>عنوان و اسلاگ</th><th>دسته / برچسب</th><th>وضعیت</th><th>آخرین تغییر</th><th>عملیات</th></tr></thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td class="min-w-[220px]">
                                            <strong>{{ $post->title }}</strong>
                                            <p dir="ltr" class="mt-1 text-[10px] text-[var(--admin-muted)]">{{ $post->slug }}</p>
                                        </td>
                                        <td><div class="flex flex-wrap gap-1">@forelse($post->terms as $term)<span class="admin-badge admin-badge-neutral">{{ $term->name }}</span>@empty<span>—</span>@endforelse</div></td>
                                        <td>{{ $post->status === 'published' ? 'منتشرشده / زمان‌بندی‌شده' : ($post->status === 'draft' ? 'پیش‌نویس' : 'زباله‌دان') }}</td>
                                        <td class="text-xs">{{ $post->updated_at?->format('Y/m/d H:i') }}</td>
                                        <td><a href="{{ route('admin.blog-posts.edit', $post) }}" class="admin-btn admin-btn-secondary px-3 py-2">ویرایش</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-8 text-center">مقاله‌ای پیدا نشد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $posts->links() }}</div>
                </div>

                <div class="mt-9">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-bold text-[var(--admin-text)]">دسته‌ها، برچسب‌ها و اسلاگ‌ها</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.blog-terms.create', ['type' => 'category']) }}" class="admin-btn admin-btn-secondary">+ دسته</a>
                            <a href="{{ route('admin.blog-terms.create', ['type' => 'tag']) }}" class="admin-btn admin-btn-secondary">+ برچسب</a>
                        </div>
                    </div>
                    <div class="admin-table-wrap overflow-x-auto">
                        <table class="admin-table">
                            <thead><tr><th>نام</th><th>نوع</th><th>اسلاگ</th><th>تعداد مقاله</th><th>وضعیت</th><th>عملیات</th></tr></thead>
                            <tbody>
                                @forelse($terms as $term)
                                    <tr>
                                        <td class="font-semibold">{{ $term->name }}</td>
                                        <td>{{ $term->type === 'category' ? 'دسته' : 'برچسب' }}</td>
                                        <td dir="ltr" class="font-mono text-xs">{{ $term->slug }}</td>
                                        <td>{{ number_format($term->posts_count) }}</td>
                                        <td>{{ $term->is_active ? 'فعال' : 'غیرفعال' }}</td>
                                        <td><a href="{{ route('admin.blog-terms.edit', $term) }}" class="admin-btn admin-btn-secondary px-3 py-2">ویرایش</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-8 text-center">دسته یا برچسبی پیدا نشد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $terms->links() }}</div>
                </div>
            @elseif($tab === 'seo')
                <div class="mt-6 space-y-3">
                    <details open class="rounded-xl border border-[var(--admin-border)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold">تنظیمات سئو و ایندکس</summary>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">عنوان و توضیحات متا، canonical، robots، sitemap، Open Graph، داده ساختاریافته و اتصال ابزارهای وبمستر. فیلد کلمات کلیدی در این CMS ارائه نمی‌شود.</p>
                        <a href="{{ route('admin.seo-settings.edit') }}" class="admin-btn admin-btn-secondary mt-3">تنظیم سئو</a>
                    </details>
                    <details class="rounded-xl border border-[var(--admin-border)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold">تغییر مسیر آدرس‌ها</summary>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">مدیریت Redirectهای 301 و 302 برای اسلاگ‌های تغییرکرده.</p>
                        <a href="{{ route('admin.seo-redirects.index') }}" class="admin-btn admin-btn-secondary mt-3">مدیریت تغییرمسیرها</a>
                    </details>
                </div>
            @elseif($tab === 'storefront')
                <div class="mt-6 space-y-3">
                    <details open class="rounded-xl border border-[var(--admin-border)] p-4">
                        <summary class="cursor-pointer text-sm font-semibold">بنرها و اطلاعات تماس</summary>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">بنر صفحه فروشگاه و اطلاعات تماس واقعی. کنترل‌های رفتاری غیرضروری از این بخش حذف می‌شوند؛ تنظیم بنرها حفظ می‌شود.</p>
                        <a href="{{ route('admin.storefront-settings.edit') }}" class="admin-btn admin-btn-secondary mt-3">تنظیم بنر و تماس</a>
                    </details>
                </div>
            @elseif($tab === 'media')
                <div class="mt-6 rounded-xl border border-[var(--admin-border)] p-4">
                    <p class="text-sm font-semibold">کتابخانه رسانه</p>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تصاویر و فایل‌ها از کتابخانه مرکزی مدیریت می‌شوند تا نسخه‌های تکراری در فرم‌های مختلف ساخته نشود.</p>
                    <a href="{{ route('admin.media.index') }}" class="admin-btn admin-btn-secondary mt-3">رفتن به کتابخانه رسانه</a>
                </div>
            @endif
        </section>
    </div>
@endsection
