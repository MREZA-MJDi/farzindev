@extends('admin.layouts.app')
@section('title', 'مدیریت Redirectهای SEO')
@section('page_title', 'Redirect Manager')

@section('content')
    <div class="mb-7">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-accent)]">SEO / REDIRECTS</p>
        <h1 class="admin-title mt-2">مدیریت ریدایرکت‌ها</h1>
        <p class="admin-subtitle mt-2">برای URLهای تغییرکرده یا حذف‌شده، مقصد مرتبط و کد 301 یا 302 مشخص کنید. تغییر slug مقاله به‌صورت خودکار Redirect 301 می‌سازد.</p>
    </div>
    @if(session('success'))
        <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.seo-redirects.store') }}" class="admin-card mb-7 grid gap-4 p-5 xl:grid-cols-[1fr_1fr_130px_1fr_auto]">
        @csrf
        <label class="block text-xs">مسیر قدیمی
            <input name="source_path" value="{{ old('source_path') }}" required maxlength="255" dir="ltr" placeholder="/old-url" class="admin-input mt-2 w-full text-left">
        </label>
        <label class="block text-xs">مقصد جدید
            <input name="target_url" value="{{ old('target_url') }}" required maxlength="2048" dir="ltr" placeholder="/new-url یا https://..." class="admin-input mt-2 w-full text-left">
        </label>
        <label class="block text-xs">کد
            <select name="status_code" class="admin-select mt-2 w-full"><option value="301">301 دائمی</option><option value="302">302 موقت</option></select>
        </label>
        <label class="block text-xs">یادداشت
            <input name="notes" value="{{ old('notes') }}" maxlength="255" class="admin-input mt-2 w-full">
        </label>
        <div class="flex items-end"><button type="submit" class="admin-btn admin-btn-primary w-full">افزودن</button></div>
    </form>

    <div class="admin-table-wrap overflow-x-auto">
        <table class="admin-table">
            <thead><tr><th>مسیر مبدأ</th><th>مقصد</th><th>کد</th><th>بازدید Redirect</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($redirects as $redirect)
                    <tr>
                        <td colspan="6">
                            <form method="POST" action="{{ route('admin.seo-redirects.update', $redirect) }}" class="grid min-w-[900px] items-end gap-3 lg:grid-cols-[1fr_1.2fr_110px_90px_80px_auto]">
                                @csrf
                                @method('PUT')
                                <label class="block text-xs">مبدأ
                                    <input name="source_path" value="{{ $redirect->source_path }}" required maxlength="255" dir="ltr" class="admin-input mt-2 w-full text-left">
                                </label>
                                <label class="block text-xs">مقصد
                                    <input name="target_url" value="{{ $redirect->target_url }}" required maxlength="2048" dir="ltr" class="admin-input mt-2 w-full text-left">
                                </label>
                                <label class="block text-xs">کد
                                    <select name="status_code" class="admin-select mt-2 w-full">
                                        <option value="301" @selected($redirect->status_code === 301)>301</option>
                                        <option value="302" @selected($redirect->status_code === 302)>302</option>
                                    </select>
                                </label>
                                <label class="block text-xs">تعداد
                                    <span class="mt-3 block text-sm font-semibold">{{ number_format($redirect->hits) }}</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($redirect->is_active) class="admin-checkbox h-4 w-4"> فعال
                                </label>
                                <div class="flex gap-2">
                                    <button type="submit" class="admin-btn admin-btn-secondary px-3 py-2">ذخیره</button>
                                    <button form="delete-redirect-{{ $redirect->id }}" type="submit" class="admin-btn admin-btn-ghost px-3 py-2 text-red-600">حذف</button>
                                </div>
                            </form>
                            <form id="delete-redirect-{{ $redirect->id }}" method="POST" action="{{ route('admin.seo-redirects.destroy', $redirect) }}" onsubmit="return confirm('این Redirect حذف شود؟');" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            @if($redirect->notes)<p class="mt-2 text-xs text-[var(--admin-muted)]">{{ $redirect->notes }}</p>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-sm text-[var(--admin-muted)]">ریدایرکتی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $redirects->links() }}</div>
@endsection
