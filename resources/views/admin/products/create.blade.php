@extends('admin.layouts.app')

@section('title', 'افزودن محصول')
@section('page_title', 'افزودن محصول')

@section('content')

    <div class="mb-8">
        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCTS / CREATE
        </div>

        <h2 class="admin-title">
            افزودن محصول
        </h2>

        <p class="admin-subtitle mt-2">
            یک محصول جدید برای فروشگاه فرزین ایجاد کنید.
        </p>
    </div>

    <form
        action="{{ route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @include('admin.products.partials.form')
    </form>

@endsection
