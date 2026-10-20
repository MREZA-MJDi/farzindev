@extends('admin.layouts.app')

@section('title', 'دسته‌بندی‌ها')
@section('page_title', 'دسته‌بندی‌ها')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <span class="text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / CATEGORIES
            </span>

            <h2 class="admin-title mt-2">
                دسته‌بندی‌ها
            </h2>

            <p class="admin-subtitle mt-2">
                مدیریت دسته‌بندی‌های فروشگاه فرزین
            </p>
        </div>

        <a
            href="{{ route('admin.categories.create') }}"
            class="admin-btn admin-btn-primary"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 5.25v13.5M5.25 12h13.5" />
            </svg>

            افزودن دسته‌بندی
        </a>

    </div>


    <div class="admin-table-wrap">

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>#</th>
                    <th>تصویر</th>
                    <th>نام</th>
                    <th>Slug</th>
                    <th>ترتیب</th>
                    <th>وضعیت</th>
                    <th class="text-left">عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($categories as $category)

                    <tr>

                        <td>
                            {{ $categories->firstItem() + $loop->index }}
                        </td>

                        <td>

                            <div class="admin-image-thumb">

                                @if($category->image)

                                    <img
                                        src="{{ str_starts_with($category->image, 'http://') || str_starts_with($category->image, 'https://') || str_starts_with($category->image, '//')
                                                    ? $category->image
                                                    : asset('storage/' . ltrim($category->image, '/')) }}"
                                        alt="{{ $category->name }}"
                                        class="admin-image"
                                    >

                                @else

                                    <div class="flex h-full w-full items-center justify-center text-[var(--admin-muted)]">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke-width="1.5"
                                             stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659m0 0 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                        </svg>
                                    </div>

                                @endif

                            </div>

                        </td>

                        <td>
                            <a
                                href="{{ route('admin.categories.show', $category) }}"
                                class="font-semibold text-[var(--admin-text)] transition hover:text-[var(--admin-accent)]"
                            >
                                {{ $category->name }}
                            </a>
                        </td>

                        <td class="font-mono text-xs">
                            {{ $category->slug ?? '—' }}
                        </td>

                        <td>
                            {{ $category->sort_order ?? 0 }}
                        </td>

                        <td>

                            @if(isset($category->is_active) ? $category->is_active : true)

                                <span class="admin-badge admin-badge-success">
                                        فعال
                                    </span>

                            @else

                                <span class="admin-badge admin-badge-danger">
                                        غیرفعال
                                    </span>

                            @endif

                        </td>

                        <td>

                            <div class="flex items-center justify-end gap-2">

                                <a
                                    href="{{ route('admin.categories.show', $category) }}"
                                    class="admin-btn admin-btn-ghost px-3"
                                    title="مشاهده"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                         viewBox="0 0 24 24" stroke-width="1.5"
                                         stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.384 4.5 12 4.5c4.616 0 8.577 3.01 9.964 7.178.06.18.06.372 0 .552C20.577 16.49 16.616 19.5 12 19.5c-4.616 0-8.577-3.01-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>

                                <a
                                    href="{{ route('admin.categories.edit', $category) }}"
                                    class="admin-btn admin-btn-secondary px-3"
                                    title="ویرایش"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                         viewBox="0 0 24 24" stroke-width="1.5"
                                         stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652l-9.193 9.193a4.5 4.5 0 0 1-1.897 1.13l-3.426.98.98-3.426a4.5 4.5 0 0 1 1.13-1.897l9.193-9.193Z" />
                                    </svg>
                                </a>

                                <form
                                    action="{{ route('admin.categories.destroy', $category) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="admin-btn admin-btn-danger px-3"
                                        title="حذف"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke-width="1.5"
                                             stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0C10.91 2.677 10 3.66 10 4.84v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7">

                            <div class="admin-empty">

                                <div class="admin-empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="currentColor"
                                         class="h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                                    </svg>
                                </div>

                                <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                    هنوز دسته‌بندی‌ای ثبت نشده است.
                                </h3>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    برای شروع، اولین دسته‌بندی را ایجاد کنید.
                                </p>

                                <a
                                    href="{{ route('admin.categories.create') }}"
                                    class="admin-btn admin-btn-primary mt-5"
                                >
                                    ایجاد دسته‌بندی
                                </a>

                            </div>

                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    @if($categories->hasPages())

        <div class="mt-6">
            {{ $categories->links() }}
        </div>

    @endif

@endsection
