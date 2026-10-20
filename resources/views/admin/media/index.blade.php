@extends('admin.layouts.app')

@section('title', 'رسانه‌ها')

@section('content')

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-xl font-semibold text-[var(--admin-text)]">
                    رسانه‌ها
                </h1>

                <p class="mt-1 text-sm text-[var(--admin-text-soft)]">
                    تصاویر و فایل‌های عمومی سایت را مدیریت کنید.
                </p>
            </div>

            <a
                href="{{ route('admin.media.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[var(--admin-accent)] px-5 py-3 text-sm font-medium text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.7"
                    stroke="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4.5v15m7.5-7.5h-15"
                    />
                </svg>

                افزودن رسانه

            </a>

        </div>


        {{-- Flash Success --}}
        @if(session('success'))

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>

        @endif


        {{-- Flash Error --}}
        @if(session('error'))

            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ session('error') }}
            </div>

        @endif


        {{-- Media Library Header --}}
        <div class="flex items-end justify-between">

            <div>
                <h2 class="text-sm font-semibold text-[var(--admin-text)]">
                    کتابخانه رسانه
                </h2>

                <p class="mt-1 text-xs text-[var(--admin-text-soft)]">
                    {{ $media->total() }} فایل
                </p>
            </div>

        </div>


        {{-- Media --}}
        @if($media->count())

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">

                @foreach($media as $item)

                    <div
                        class="group overflow-hidden rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-surface)] transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >

                        {{-- Preview --}}
                        <div class="aspect-square overflow-hidden bg-[var(--admin-bg)]">

                            @if($item->is_image)

                                <img
                                    src="{{ $item->url }}"
                                    alt="{{ $item->alt ?: $item->original_name }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                >

                            @else

                                <div class="flex h-full flex-col items-center justify-center px-4 text-center">

                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--admin-surface)] text-[var(--admin-muted)]">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-7 w-7"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H6.375A2.625 2.625 0 0 0 3.75 4.875v14.25a2.625 2.625 0 0 0 2.625 2.625h11.25a2.625 2.625 0 0 0 2.625-2.625V14.25M10.5 2.25V5.625A2.625 2.625 0 0 0 13.125 8.25H16.5"
                                            />
                                        </svg>

                                    </div>

                                    <span class="mt-3 rounded-lg bg-[var(--admin-surface)] px-2 py-1 text-[10px] font-medium text-[var(--admin-text-soft)]">
                                        {{ strtoupper($item->extension ?? 'FILE') }}
                                    </span>

                                </div>

                            @endif

                        </div>


                        {{-- Information --}}
                        <div class="space-y-3 p-3">

                            <div>

                                <p
                                    class="truncate text-xs font-medium text-[var(--admin-text)]"
                                    title="{{ $item->original_name }}"
                                >
                                    {{ $item->original_name }}
                                </p>

                                @if($item->alt)

                                    <p
                                        class="mt-1 truncate text-[10px] text-[var(--admin-text-soft)]"
                                        title="{{ $item->alt }}"
                                    >
                                        {{ $item->alt }}
                                    </p>

                                @endif

                            </div>


                            <div class="flex items-center justify-between text-[10px] text-[var(--admin-text-soft)]">

                                <span>
                                    {{ $item->human_size }}
                                </span>

                                <span>
                                    {{ \App\Helpers\JalaliHelper::format($item->created_at, 'Y/m/d') }}
                                </span>

                            </div>


                            {{-- Actions --}}
                            <div class="flex items-center gap-2">

                                <a
                                    href="{{ $item->url }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex-1 rounded-xl border border-[var(--admin-border)] px-3 py-2 text-center text-[10px] font-medium text-[var(--admin-text)] transition hover:bg-[var(--admin-bg)]"
                                >
                                    مشاهده
                                </a>


                                <form
                                    action="{{ route('admin.media.destroy', $item) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از حذف این رسانه مطمئن هستید؟')"
                                    class="shrink-0"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="flex h-8 w-8 items-center justify-center rounded-xl border border-red-200 text-red-500 transition hover:bg-red-50"
                                        title="حذف"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-4 w-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0C9.91 2.78 9 3.764 9 4.944v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                            />
                                        </svg>

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- Pagination --}}
            @if($media->hasPages())

                <div class="pt-4">
                    {{ $media->links() }}
                </div>

            @endif

        @else

            {{-- Empty State --}}
            <div class="rounded-3xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface)] px-6 py-16 text-center">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--admin-bg)] text-[var(--admin-muted)]">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-8 w-8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.159 2.159m0 0 1.409-1.409a2.25 2.25 0 0 1 3.182 0l4.909 4.909M21.75 12V6.75a2.25 2.25 0 0 0-2.25-2.25H4.5a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25h10.5"
                        />
                    </svg>

                </div>

                <h3 class="mt-4 text-sm font-semibold text-[var(--admin-text)]">
                    هنوز رسانه‌ای اضافه نشده
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-text-soft)]">
                    اولین تصویر یا فایل خود را به کتابخانه رسانه اضافه کنید.
                </p>

                <a
                    href="{{ route('admin.media.create') }}"
                    class="mt-5 inline-flex items-center justify-center rounded-2xl bg-[var(--admin-accent)] px-5 py-3 text-xs font-medium text-white transition hover:shadow-lg"
                >
                    افزودن اولین رسانه
                </a>

            </div>

        @endif

    </div>

@endsection
