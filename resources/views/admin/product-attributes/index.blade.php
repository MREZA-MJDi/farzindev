@extends('admin.layouts.app')
@section('title','ویژگی‌های فنی')
@section('page_title','ویژگی‌های فنی')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><h1 class="admin-title">مدیریت ویژگی‌های فنی</h1><p class="admin-subtitle mt-2">تعریف نوع داده، واحد، گروه و گزینه‌های قابل انتخاب برای مشخصات محصولات</p></div><a class="admin-btn admin-btn-primary" href="{{ route('admin.product-attributes.create') }}">افزودن ویژگی</a></div>
@if(session('success'))<div role="status" class="mb-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
@if(session('error'))<div role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>@endif
<div class="admin-table-wrap overflow-x-auto"><table class="admin-table"><thead><tr><th>ویژگی</th><th>شناسه</th><th>نوع</th><th>واحد / گروه</th><th>گزینه‌ها</th><th>اتصال / مقدار</th><th>عملیات</th></tr></thead><tbody>
@forelse($attributes as $attribute)<tr><td class="font-semibold">{{ $attribute->label }}</td><td dir="ltr" class="font-mono text-xs">{{ $attribute->key }}</td><td>{{ $attribute->type }}</td><td>{{ $attribute->unit ?: '—' }}<span class="block text-xs text-[var(--admin-muted)]">{{ $attribute->group ?: '—' }}</span></td><td class="text-xs">{{ $attribute->options->pluck('label')->join('، ') ?: '—' }}</td><td>{{ $attribute->categories_count }} دسته / {{ $attribute->values_count }} مقدار</td><td><div class="flex gap-2"><a class="admin-btn admin-btn-secondary" href="{{ route('admin.product-attributes.edit',$attribute) }}">ویرایش</a><form method="POST" action="{{ route('admin.product-attributes.destroy',$attribute) }}" onsubmit="return confirm('ویژگی حذف شود؟')">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger" type="submit">حذف</button></form></div></td></tr>@empty<tr><td colspan="7">هنوز ویژگی فنی تعریف نشده است.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-5">{{ $attributes->links() }}</div>
@endsection
