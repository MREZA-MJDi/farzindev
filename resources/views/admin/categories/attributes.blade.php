@extends('admin.layouts.app')
@section('title','ویژگی‌های دسته‌بندی')
@section('page_title','ویژگی‌های فنی دسته‌بندی')
@section('content')
<div class="mb-6"><h1 class="admin-title">ویژگی‌های فنی {{ $category->name }}</h1><p class="admin-subtitle mt-2">ویژگی‌های قابل ورود برای محصولات این دسته را انتخاب کنید. حداکثر سه مورد را به‌عنوان مشخصه کلیدی نشان دهید.</p></div>
@if(session('success'))<div role="status" class="mb-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
@if($errors->any())<div role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.categories.attributes.update',$category) }}" class="space-y-4">@csrf @method('PUT')
@foreach($attributes->groupBy('group') as $group=>$items)
<section class="admin-card overflow-hidden"><h2 class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 text-sm font-bold">{{ $group ?: 'بدون گروه' }}</h2>
<div class="divide-y divide-[var(--admin-border)]">
@foreach($items as $attribute) @php($pivot=$selected->get($attribute->id)?->pivot)
<div class="grid gap-3 p-4 lg:grid-cols-[minmax(180px,1fr)_repeat(5,minmax(90px,auto))] lg:items-center">
<div><label class="flex items-center gap-2 text-sm font-semibold"><input class="admin-checkbox" type="checkbox" name="attributes[{{ $attribute->id }}][enabled]" value="1" @checked($pivot)> {{ $attribute->label }}</label><p class="mt-1 text-[11px] text-[var(--admin-muted)]">{{ $attribute->type }} @if($attribute->unit) · {{ $attribute->unit }} @endif</p></div>
<label class="text-xs">ترتیب<input class="admin-input mt-1 w-full" type="number" min="0" name="attributes[{{ $attribute->id }}][sort_order]" value="{{ old("attributes.{$attribute->id}.sort_order",$pivot?->sort_order ?? $loop->iteration) }}"></label>
@foreach(['is_required'=>'اجباری','is_recommended'=>'پیشنهادی','is_filterable'=>'فیلترپذیر','is_key_spec'=>'مشخصه کلیدی'] as $field=>$label)
<label class="flex items-center gap-2 text-xs"><input class="admin-checkbox" type="checkbox" name="attributes[{{ $attribute->id }}][{{ $field }}]" value="1" @checked(old("attributes.{$attribute->id}.{$field}",$pivot?->{$field} ?? false))>{{ $label }}</label>
@endforeach
</div>
@endforeach
</div></section>
@endforeach
<div class="flex gap-2"><button type="submit" class="admin-btn admin-btn-primary">ذخیره ویژگی‌های دسته</button><a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">بازگشت</a></div>
</form>
@endsection
