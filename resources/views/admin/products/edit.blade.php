@extends('admin.layouts.app')

@section('title', 'ویرایش محصول')
@section('page_title', 'ویرایش محصول')

@section('content')

    <div class="mb-8">
        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCTS / EDIT
        </div>

        <h2 class="admin-title">
            ویرایش محصول
        </h2>

        <p class="admin-subtitle mt-2">
            اطلاعات محصول «{{ $product->name }}» را ویرایش کنید.
        </p>
    </div>

    @php
        $mediaTabHasErrors = collect($errors->keys())->contains(
            fn (string $key): bool => str_starts_with($key, 'images')
                || str_starts_with($key, 'media_ids')
                || str_starts_with($key, 'image_ids')
        );
    @endphp

    <div
        x-data="{ activeTab: @js($mediaTabHasErrors ? 'media' : 'details') }"
        class="space-y-6"
    >
        <div
            role="tablist"
            aria-label="بخش‌های ویرایش محصول"
            class="flex flex-wrap gap-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-2"
        >
            <button
                type="button"
                role="tab"
                id="product-details-tab"
                aria-controls="product-details-panel"
                :aria-selected="(activeTab === 'details').toString()"
                @click="activeTab = 'details'"
                :class="activeTab === 'details'
                    ? 'bg-[var(--admin-text)] text-white shadow-sm'
                    : 'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)]'"
                class="rounded-xl px-4 py-3 text-xs font-semibold transition"
            >
                اطلاعات و تنظیمات محصول
            </button>
            <button
                type="button"
                role="tab"
                id="product-media-tab"
                aria-controls="product-media-panel"
                :aria-selected="(activeTab === 'media').toString()"
                @click="activeTab = 'media'"
                :class="activeTab === 'media'
                    ? 'bg-[var(--admin-text)] text-white shadow-sm'
                    : 'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)]'"
                class="rounded-xl px-4 py-3 text-xs font-semibold transition"
            >
                گالری و تنوع‌ها
            </button>
        </div>

        <section
            id="product-details-panel"
            role="tabpanel"
            aria-labelledby="product-details-tab"
            x-show="activeTab === 'details'"
            x-cloak
        >
            <form
                action="{{ route('admin.products.update', $product) }}"
                method="POST"
                enctype="multipart/form-data"
                data-admin-product-form
            >
                @method('PUT')
                @include('admin.products.partials.form')
            </form>

            @if(session('success'))
                <div role="status" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
        </section>

        <section
            id="product-media-panel"
            role="tabpanel"
            aria-labelledby="product-media-tab"
            x-show="activeTab === 'media'"
            x-cloak
        >
            @include('admin.products.partials.variants', ['product' => $product])
        </section>
    </div>

@endsection
