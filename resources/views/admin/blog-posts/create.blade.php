@extends('admin.layouts.app')

@section('title', 'مقاله جدید')
@section('page_title', 'مقاله جدید')

@section('content')
    <div class="mb-8">
        <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">CONTENT / BLOG / CREATE</p>
        <h1 class="admin-title mt-2">ایجاد مقاله</h1>
        <p class="admin-subtitle mt-2">محتوا را بنویسید، SEO را تنظیم کنید و انتشار را کنترل کنید.</p>
    </div>

    @if($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.blog-posts.store') }}" enctype="multipart/form-data" class="space-y-6">
        @include('admin.blog-posts.form', ['isEditing' => false])
    </form>
@endsection
