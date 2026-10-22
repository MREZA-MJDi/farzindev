@extends('admin.layouts.app')

@section('title', 'ویرایش مقاله')
@section('page_title', 'ویرایش مقاله')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">CONTENT / BLOG / EDIT</p>
            <h1 class="admin-title mt-2">ویرایش مقاله</h1>
            <p class="admin-subtitle mt-2">{{ $post->title }}</p>
        </div>
        @if($post->status === 'published' && $post->published_at && $post->published_at->isPast())
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">مشاهده صفحه عمومی</a>
        @endif
    </div>

    @if(session('success'))
        <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.blog-posts.update', $post) }}" enctype="multipart/form-data" class="space-y-6">
        @method('PUT')
        @include('admin.blog-posts.form', ['isEditing' => true])
    </form>
@endsection
