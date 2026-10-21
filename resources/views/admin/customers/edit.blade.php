@extends('admin.layouts.app')

@section('title', 'ویرایش مشتری')
@section('page_title', 'ویرایش مشتری')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            USERS / CUSTOMERS / EDIT
        </div>

        <h2 class="admin-title">
            ویرایش مشتری
        </h2>

        <p class="admin-subtitle mt-2">
            اطلاعات مشتری «{{ $customer->name }}» را ویرایش کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.customers.update', $customer) }}"
        method="POST"
    >

        @csrf
        @method('PUT')


        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            <div class="space-y-6 xl:col-span-2">

                <div class="admin-card p-6">

                    <div class="mb-6">

                        <h3 class="text-base font-bold text-[var(--admin-text)]">
                            اطلاعات مشتری
                        </h3>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            نام و ایمیل مشتری را بروزرسانی کنید.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">

                            <label
                                for="name"
                                class="admin-label"
                            >
                                نام و نام خانوادگی
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $customer->name) }}"
                                class="admin-input"
                                required
                            >

                            @error('name')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>


                        <div class="sm:col-span-2">

                            <label
                                for="email"
                                class="admin-label"
                            >
                                ایمیل
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $customer->email) }}"
                                dir="ltr"
                                class="admin-input text-left"
                                required
                            >

                            @error('email')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>


            <div class="space-y-6">

                <div class="admin-card p-6">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        اطلاعات حساب
                    </h3>


                    <dl class="mt-6 space-y-5">

                        <div>

                            <dt class="admin-stat-label">
                                نقش
                            </dt>

                            <dd class="mt-2">
                                <span class="admin-badge admin-badge-info">
                                    مشتری
                                </span>
                            </dd>

                        </div>


                        <div>

                            <dt class="admin-stat-label">
                                تاریخ عضویت
                            </dt>

                            <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                                {{ $customer->created_at ? \App\Helpers\JalaliHelper::format($customer->created_at, 'Y/m/d') : '—' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="admin-stat-label">
                                آخرین بروزرسانی
                            </dt>

                            <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                                {{ $customer->updated_at ? \App\Helpers\JalaliHelper::format($customer->updated_at, 'Y/m/d H:i') : '—' }}
                            </dd>

                        </div>

                    </dl>

                </div>


                <div class="admin-card p-6">

                    <div class="flex flex-col gap-3">

                        <button
                            type="submit"
                            class="admin-btn admin-btn-primary w-full"
                        >
                            ذخیره تغییرات
                        </button>

                        <a
                            href="{{ route('admin.customers.show', $customer) }}"
                            class="admin-btn admin-btn-secondary w-full"
                        >
                            انصراف
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

@endsection
