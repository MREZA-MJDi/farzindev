@extends('admin.layouts.app')

@section('title', 'مدیریت بلاگ')
@section('page_title', 'مدیریت بلاگ')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">CONTENT / BLOG</p>
            <h1 class="admin-title mt-2">مقالات بلاگ</h1>
            <p class="admin-subtitle mt-2">تولید، زمان‌بندی انتشار و بهینه‌سازی SEO مقاله‌ها از یک مسیر مرکزی.</p>
        </div>
        <a href="{{ route('admin.blog-posts.create') }}" class="admin-btn admin-btn-primary">+ مقاله جدید</a>
    </div>

    @if(session('success'))
        <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="admin-card mb-6 grid gap-4 p-5 sm:grid-cols-[1fr_180px_auto]">
        <label class="block text-xs text-[var(--admin-text)]">
            جستجو
            <input name="q" value="{{ request('q') }}" placeholder="عنوان یا slug مقاله" class="admin-input mt-2 w-full">
        </label>
        <label class="block text-xs text-[var(--admin-text)]">
            وضعیت
            <select name="status" class="admin-select mt-2 w-full">
                <option value="">همه وضعیت‌ها</option>
                <option value="draft" @selected(request('status') === 'draft')>پیش‌نویس</option>
                <option value="published" @selected(request('status') === 'published')>منتشرشده / زمان‌بندی‌شده</option>
            </select>
        </label>
        <div class="flex items-end gap-2">
            <button class="admin-btn admin-btn-secondary" type="submit">اعمال فیلتر</button>
            <a href="{{ route('admin.blog-posts.index') }}" class="admin-btn admin-btn-ghost">پاک‌کردن</a>
        </div>
    </form>

    <div class="admin-table-wrap">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                <tr><th>مقاله</th><th>Slug</th><th>وضعیت</th><th>انتشار</th><th>آخرین تغییر</th><th>عملیات</th></tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <p class="font-semibold text-[var(--admin-text)]">{{ $post->title }}</p>
                            <p class="mt-1 max-w-sm truncate text-xs text-[var(--admin-muted)]">{{ $post->excerpt }}</p>
                        </td>
                        <td dir="ltr" class="font-mono text-xs">{{ $post->slug }}</td>
                        <td>
                            @if($post->status === 'published' && $post->published_at && $post->published_at->isPast())
                                <span class="admin-badge admin-badge-success">منتشرشده</span>
                            @elseif($post->status === 'published')
                                <span class="admin-badge admin-badge-neutral">زمان‌بندی‌شده</span>
                            @else
                                <span class="admin-badge admin-badge-neutral">پیش‌نویس</span>
                            @endif
                        </td>
                        <td class="text-xs text-[var(--admin-muted)]">{{ $post->published_at?->format('Y/m/d H:i') ?? '—' }}</td>
                        <td class="text-xs text-[var(--admin-muted)]">{{ $post->updated_at?->format('Y/m/d H:i') }}</td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.blog-posts.edit', $post) }}" class="admin-btn admin-btn-secondary px-3 py-2">ویرایش</a>
                                @if($post->status === 'published' && $post->published_at && $post->published_at->isPast())
                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-ghost px-3 py-2">مشاهده</a>
                                @endif
                                <form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" onsubmit="return confirm('این مقاله حذف شود؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-ghost px-3 py-2 text-red-600">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-sm text-[var(--admin-muted)]">هنوز مقاله‌ای وجود ندارد. اولین مقاله را ایجاد کنید.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>
@endsection
