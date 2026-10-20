@extends('admin.layouts.app')

@section('title', 'مشتریان')
@section('page_title', 'مشتریان')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <span class="text-xs font-medium text-[var(--admin-accent)]">
                USERS / CUSTOMERS
            </span>

            <h2 class="admin-title mt-2">
                مشتریان
            </h2>

            <p class="admin-subtitle mt-2">
                مدیریت مشتریان ثبت‌نام‌شده در فروشگاه فرزین
            </p>
        </div>

    </div>


    {{-- Search --}}
    <div class="admin-card mb-6 p-5">

        <form
            action="{{ route('admin.customers.index') }}"
            method="GET"
            class="flex flex-col gap-3 sm:flex-row"
        >

            <div class="flex-1">

                <label for="search" class="admin-label">
                    جستجوی مشتری
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ request('search') }}"
                    class="admin-input"
                    placeholder="نام یا ایمیل مشتری..."
                >

            </div>

            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="admin-btn admin-btn-secondary"
                >
                    جستجو
                </button>

                @if(request()->filled('search'))

                    <a
                        href="{{ route('admin.customers.index') }}"
                        class="admin-btn admin-btn-ghost"
                    >
                        پاک کردن
                    </a>

                @endif

            </div>

        </form>

    </div>


    {{-- Table --}}
    <div class="admin-table-wrap">

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>#</th>
                    <th>مشتری</th>
                    <th>ایمیل</th>
                    <th>سفارش‌ها</th>
                    <th>تاریخ عضویت</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($customers as $customer)

                    <tr>

                        <td>
                            {{ $customers->firstItem() + $loop->index }}
                        </td>


                        <td>

                            <div class="flex items-center gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--admin-accent)] text-sm font-bold text-white">
                                    {{ mb_substr($customer->name, 0, 1) }}
                                </div>

                                <div class="min-w-0">

                                    <a
                                        href="{{ route('admin.customers.show', $customer) }}"
                                        class="block truncate text-sm font-semibold text-[var(--admin-text)] hover:text-[var(--admin-accent)]"
                                    >
                                        {{ $customer->name }}
                                    </a>

                                    <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                        مشتری
                                    </p>

                                </div>

                            </div>

                        </td>


                        <td>
                                <span class="text-sm text-[var(--admin-text-soft)]">
                                    {{ $customer->email }}
                                </span>
                        </td>


                        <td>
                                <span class="admin-badge admin-badge-info">
                                    {{ $customer->orders_count ?? $customer->orders->count() }}
                                    سفارش
                                </span>
                        </td>


                        <td>
                                <span class="whitespace-nowrap text-xs text-[var(--admin-muted)]">
                                    {{ $customer->created_at?->format('Y/m/d') ?? '—' }}
                                </span>
                        </td>


                        <td>

                            <div class="flex items-center justify-end gap-2">

                                <a
                                    href="{{ route('admin.customers.show', $customer) }}"
                                    class="admin-btn admin-btn-ghost px-3"
                                >
                                    مشاهده
                                </a>

                                <a
                                    href="{{ route('admin.customers.edit', $customer) }}"
                                    class="admin-btn admin-btn-secondary px-3"
                                >
                                    ویرایش
                                </a>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6">

                            <div class="admin-empty">

                                <div class="admin-empty-icon">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor"
                                         class="h-6 w-6">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                                        />
                                    </svg>

                                </div>

                                <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                    مشتری‌ای پیدا نشد.
                                </h3>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    عبارت جستجو را تغییر دهید و دوباره امتحان کنید.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    @if($customers->hasPages())

        <div class="mt-6">
            {{ $customers->links() }}
        </div>

    @endif

@endsection
