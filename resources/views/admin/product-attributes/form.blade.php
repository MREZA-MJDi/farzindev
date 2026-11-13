@extends('admin.layouts.app')
@section('title',$attribute->exists?'ویرایش ویژگی':'افزودن ویژگی')
@section('page_title','مدیریت ویژگی‌های فنی')
@section('content')
<div class="mb-6"><h1 class="admin-title">{{ $attribute->exists?'ویرایش ویژگی فنی':'افزودن ویژگی فنی' }}</h1><p class="admin-subtitle mt-2">شناسه لاتین باید ثابت و یکتا باشد؛ عنوان و گزینه‌ها در سایت فارسی نمایش داده می‌شوند.</p></div>
@if($errors->any())<div role="alert" class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pr-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="admin-card max-w-3xl space-y-5 p-6" method="POST" action="{{ $attribute->exists?route('admin.product-attributes.update',$attribute):route('admin.product-attributes.store') }}">@csrf @if($attribute->exists) @method('PUT') @endif
<div class="grid gap-4 sm:grid-cols-2">
<label class="block text-xs font-semibold">شناسه لاتین<input class="admin-input mt-2 w-full" name="key" dir="ltr" required value="{{ old('key',$attribute->key) }}" placeholder="basin_count"></label>
<label class="block text-xs font-semibold">عنوان فارسی<input class="admin-input mt-2 w-full" name="label" required value="{{ old('label',$attribute->label) }}"></label>
<label class="block text-xs font-semibold">نوع داده<select class="admin-select mt-2 w-full" name="type" required>@foreach(['text'=>'متن','textarea'=>'متن چندخطی','number'=>'عدد','select'=>'انتخاب تکی','multiselect'=>'انتخاب چندگانه','boolean'=>'بله / خیر'] as $value=>$label)<option value="{{ $value }}" @selected(old('type',$attribute->type)===$value)>{{ $label }}</option>@endforeach</select></label>
<label class="block text-xs font-semibold">واحد<input class="admin-input mt-2 w-full" name="unit" value="{{ old('unit',$attribute->unit) }}" placeholder="cm، mm، kg"></label>
<label class="block text-xs font-semibold sm:col-span-2">گروه<input class="admin-input mt-2 w-full" name="group" value="{{ old('group',$attribute->group) }}" placeholder="ابعاد و نصب"></label>
<label class="block text-xs font-semibold sm:col-span-2">راهنمای ورود مقدار<textarea class="admin-textarea mt-2 w-full" name="help_text" rows="2">{{ old('help_text',$attribute->help_text) }}</textarea></label>
<label class="block text-xs font-semibold sm:col-span-2">گزینه‌ها (هر گزینه در یک خط)<textarea class="admin-textarea mt-2 w-full" name="options" rows="6">{{ old('options',$attribute->options->pluck('label')->join("\n")) }}</textarea><span class="mt-1 block font-normal text-[var(--admin-muted)]">فقط برای انتخاب تکی و چندگانه؛ گزینه‌ای که در محصول استفاده شده باشد حذف‌پذیر نیست.</span></label>
</div>
<div class="flex gap-2"><button class="admin-btn admin-btn-primary" type="submit">ذخیره ویژگی</button><a class="admin-btn admin-btn-secondary" href="{{ route('admin.product-attributes.index') }}">بازگشت</a></div>
</form>
@endsection
