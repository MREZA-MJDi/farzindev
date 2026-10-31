@extends('admin.layouts.app')
@section('title', 'ویرایش Taxonomy بلاگ')
@section('page_title', 'ویرایش دسته یا برچسب')

@section('content')
    <div class="mb-7">
        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-accent)]">BLOG / TAXONOMY / EDIT</p>
        <h1 class="admin-title mt-2">{{ $term->name }}</h1>
        <p class="admin-subtitle mt-2">تعداد مقاله‌های مرتبط: {{ $term->posts()->count() }}</p>
    </div>
    @if(session('success'))
        <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.blog-terms.update', $term) }}" class="admin-card max-w-3xl space-y-5 p-5 sm:p-7">
        @method('PUT')
        @include('admin.blog-terms.form')
        <button type="submit" class="admin-btn admin-btn-primary">ذخیره تغییرات</button>
        <a href="{{ route('admin.blog-terms.index') }}" class="admin-btn admin-btn-ghost">بازگشت</a>
    </form>
@endsection
