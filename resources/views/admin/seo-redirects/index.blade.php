@extends('admin.layouts.app')
@section('title', 'مدیریت Redirectهای SEO')
@section('page_title', 'Redirect Manager')

@section('content')
    @php
        $redirectTargetUrls = collect($redirectTargets)->pluck('url')->all();
        $createSelection = old('target_selection', '__custom__');
        $createIsCustom = $createSelection === '__custom__' || ($createSelection !== '' && ! in_array($createSelection, $redirectTargetUrls, true));
    @endphp
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
        <div class="block text-xs">
            <label for="redirect-target-create">مقصد جدید</label>
            <select id="redirect-target-create" name="target_selection" data-redirect-target-select class="admin-select mt-2 w-full">
                @foreach($redirectTargets as $target)
                    <option value="{{ $target['url'] }}" @selected($createSelection === $target['url'])>{{ $target['label'] }} — {{ $target['url'] }}</option>
                @endforeach
                <option value="__custom__" @selected($createIsCustom)>ورود دستی مسیر داخلی یا HTTPS</option>
            </select>
            <input name="target_url_custom" value="{{ old('target_url_custom', old('target_url')) }}" maxlength="2048" dir="ltr" placeholder="/new-url یا https://..." data-redirect-target-custom @if(! $createIsCustom) hidden @endif class="admin-input mt-2 w-full text-left">
        </div>
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
            <thead><tr><th>مسیر مبدأ</th><th>مقصد</th><th>کد</th><th>آخرین تغییر</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @forelse($redirects as $redirect)
                    @php
                        $selectionKey = 'target_selection.' . $redirect->id;
                        $customKey = 'target_url_custom.' . $redirect->id;
                        $storedTargetSelection = old($selectionKey, in_array($redirect->target_url, $redirectTargetUrls, true) ? $redirect->target_url : '__custom__');
                        $isCustomTarget = $storedTargetSelection === '__custom__' || ! in_array($storedTargetSelection, $redirectTargetUrls, true);
                    @endphp
                    <tr>
                        <td colspan="6">
                            <form method="POST" action="{{ route('admin.seo-redirects.update', $redirect) }}" class="grid min-w-[980px] items-end gap-3 lg:grid-cols-[1fr_1.2fr_110px_90px_80px_auto]">
                                @csrf
                                @method('PUT')
                                <label class="block text-xs">مبدأ
                                    <input name="source_path" value="{{ $redirect->source_path }}" required maxlength="255" dir="ltr" class="admin-input mt-2 w-full text-left">
                                </label>
                                <div class="block text-xs">
                                    <label for="redirect-target-{{ $redirect->id }}">مقصد</label>
                                    <select id="redirect-target-{{ $redirect->id }}" name="target_selection[{{ $redirect->id }}]" data-redirect-target-select class="admin-select mt-2 w-full">
                                        @foreach($redirectTargets as $target)
                                            <option value="{{ $target['url'] }}" @selected($storedTargetSelection === $target['url'])>{{ $target['label'] }} — {{ $target['url'] }}</option>
                                        @endforeach
                                        <option value="__custom__" @selected($isCustomTarget)>ورود دستی مسیر داخلی یا HTTPS</option>
                                    </select>
                                    <input name="target_url_custom[{{ $redirect->id }}]" value="{{ old($customKey, $isCustomTarget ? $redirect->target_url : '') }}" maxlength="2048" dir="ltr" placeholder="/new-url یا https://..." data-redirect-target-custom @if(! $isCustomTarget) hidden @endif class="admin-input mt-2 w-full text-left">
                                </div>
                                <label class="block text-xs">کد
                                    <select name="status_code" class="admin-select mt-2 w-full">
                                        <option value="301" @selected($redirect->status_code === 301)>301</option>
                                        <option value="302" @selected($redirect->status_code === 302)>302</option>
                                    </select>
                                </label>
                                <label class="block text-xs">آخرین تغییر
                                    <span class="mt-3 block text-xs text-[var(--admin-muted)]">{{ $redirect->updated_at?->format('Y/m/d H:i') }}</span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-redirect-target-select]').forEach(function (select) {
        const form = select.closest('form');
        const customInput = form && form.querySelector('[data-redirect-target-custom]');
        if (!customInput) return;

        const syncTargetInput = function () {
            const isCustom = select.value === '__custom__';
            customInput.hidden = !isCustom;
            customInput.required = isCustom;
            if (!isCustom) customInput.value = '';
        };

        select.addEventListener('change', syncTargetInput);
        syncTargetInput();
    });
});
</script>
@endpush
