@extends('admin.layouts.app')
@section('title', 'دسته‌ها و برچسب‌های بلاگ')
@section('page_title', 'Taxonomy بلاگ')

@section('content')
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-accent)]">BLOG / TAXONOMY</p>
            <h1 class="admin-title mt-2">دسته‌بندی‌ها و برچسب‌ها</h1>
            <p class="admin-subtitle mt-2">آرشیوهای موضوعی با URL، توضیحات و تنظیمات SEO مستقل.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.blog-terms.create', ['type' => 'category']) }}" class="admin-btn admin-btn-primary">+ دسته‌بندی</a>
            <a href="{{ route('admin.blog-terms.create', ['type' => 'tag']) }}" class="admin-btn admin-btn-secondary">+ برچسب</a>
        </div>
    </div>

    @if(session('success'))
        <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="admin-table-wrap overflow-x-auto">
        <table class="admin-table">
            <thead><tr><th>نام</th><th>نوع</th><th>Slug</th><th>مقاله‌ها</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($terms as $term)
                    <tr>
                        <td class="font-semibold">{{ $term->name }}</td>
                        <td>{{ $term->type === 'category' ? 'دسته‌بندی' : 'برچسب' }}</td>
                        <td dir="ltr" class="font-mono text-xs">{{ $term->slug }}</td>
                        <td>{{ number_format($term->posts_count) }}</td>
                        <td><span class="admin-badge {{ $term->is_active ? 'admin-badge-success' : 'admin-badge-neutral' }}">{{ $term->is_active ? 'فعال' : 'غیرفعال' }}</span></td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.blog-terms.edit', $term) }}" class="admin-btn admin-btn-secondary px-3 py-2">ویرایش SEO</a>
                                @if($term->is_active)
                                    <a href="{{ $term->type === 'category' ? route('blog.category', $term->slug) : route('blog.tag', $term->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-ghost px-3 py-2">مشاهده</a>
                                @endif
                                <form method="POST" action="{{ route('admin.blog-terms.destroy', $term) }}" onsubmit="return confirm('این مورد و ارتباط آن با مقاله‌ها حذف شود؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-btn admin-btn-ghost px-3 py-2 text-red-600" type="submit">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-sm text-[var(--admin-muted)]">هنوز دسته یا برچسبی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $terms->links() }}</div>
@endsection
