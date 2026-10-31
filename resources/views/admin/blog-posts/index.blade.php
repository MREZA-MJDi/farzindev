@extends('admin.layouts.app')

@section('title', 'مدیریت بلاگ')
@section('page_title', 'مدیریت بلاگ')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 2xl:flex-row 2xl:items-end">
        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">CONTENT / BLOG CMS</p>
            <h1 class="admin-title mt-2">مقالات بلاگ</h1>
            <p class="admin-subtitle mt-2">محتوا، زمان‌بندی، دسته‌ها، برچسب‌ها و چک‌لیست SEO از یک پنل مرکزی.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('blog.feed') }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-secondary">RSS Feed</a>
            <a href="{{ route('admin.blog-terms.index') }}" class="admin-btn admin-btn-secondary">دسته‌ها و برچسب‌ها</a>
            <a href="{{ route('admin.blog-posts.create') }}" class="admin-btn admin-btn-primary">+ مقاله جدید</a>
        </div>
    </div>

    @if(session('success'))
        <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="admin-card mb-6 grid gap-4 p-5 sm:grid-cols-[minmax(0,1fr)_200px_auto]">
        <label class="block text-xs text-[var(--admin-text)]">
            جستجو
            <input name="q" value="{{ request('q') }}" placeholder="عنوان یا slug مقاله" class="admin-input mt-2 w-full">
        </label>
        <label class="block text-xs text-[var(--admin-text)]">
            وضعیت
            <select name="status" class="admin-select mt-2 w-full">
                <option value="">همه محتوا (به‌جز زباله‌دان)</option>
                <option value="draft" @selected(request('status') === 'draft')>پیش‌نویس</option>
                <option value="published" @selected(request('status') === 'published')>منتشرشده / زمان‌بندی‌شده</option>
                <option value="trash" @selected(request('status') === 'trash')>زباله‌دان</option>
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
                    <tr><th>مقاله</th><th>دسته / برچسب</th><th>وضعیت</th><th>انتشار</th><th>نویسنده</th><th>آخرین تغییر</th><th>عملیات</th></tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td class="min-w-[230px]">
                                <p class="font-semibold text-[var(--admin-text)]">{{ $post->title }}</p>
                                <p class="mt-1 max-w-sm truncate text-xs text-[var(--admin-muted)]">{{ $post->excerpt }}</p>
                                <p dir="ltr" class="mt-1 text-[10px] text-[var(--admin-muted)]">{{ $post->slug }}</p>
                                @if($post->is_featured)
                                    <span class="admin-badge admin-badge-neutral mt-2">ویژه</span>
                                @endif
                            </td>
                            <td class="min-w-[150px]">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($post->terms as $term)
                                        <span class="rounded-full border border-[var(--admin-border)] px-2 py-1 text-[10px] text-[var(--admin-muted)]">{{ $term->name }}</span>
                                    @endforeach
                                    @if($post->terms->isEmpty())<span class="text-xs text-[var(--admin-muted)]">—</span>@endif
                                </div>
                            </td>
                            <td>
                                @if($post->status === 'trash')
                                    <span class="admin-badge admin-badge-neutral">زباله‌دان</span>
                                @elseif($post->status === 'published' && $post->published_at && $post->published_at->isPast())
                                    <span class="admin-badge admin-badge-success">منتشرشده</span>
                                @elseif($post->status === 'published')
                                    <span class="admin-badge admin-badge-neutral">زمان‌بندی‌شده</span>
                                @else
                                    <span class="admin-badge admin-badge-neutral">پیش‌نویس</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-xs text-[var(--admin-muted)]">{{ $post->published_at?->format('Y/m/d H:i') ?? '—' }}</td>
                            <td class="text-xs text-[var(--admin-muted)]">{{ $post->author?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap text-xs text-[var(--admin-muted)]">{{ $post->updated_at?->format('Y/m/d H:i') }}</td>
                            <td>
                                <div class="flex min-w-[150px] flex-wrap gap-2">
                                    <a href="{{ route('admin.blog-posts.edit', $post) }}" class="admin-btn admin-btn-secondary px-3 py-2">ویرایش / SEO</a>
                                    @if($post->status === 'published' && $post->published_at && $post->published_at->isPast())
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-ghost px-3 py-2">مشاهده</a>
                                    @endif
                                    @if($post->status !== 'trash')
                                        <form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" onsubmit="return confirm('مقاله به زباله‌دان منتقل شود؟ فایل تصویر حذف نخواهد شد.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn-ghost px-3 py-2 text-red-600">زباله‌دان</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-sm text-[var(--admin-muted)]">مقاله‌ای برای این فیلتر وجود ندارد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>
@endsection
