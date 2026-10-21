@extends('layouts.app')

@section('title', 'ویرایش پروفایل | فرزین')
@section('description', 'مدیریت اطلاعات حساب کاربری در فرزین.')

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')
    <section class="min-h-[60vh] bg-[var(--livora-cream)] py-10 sm:py-14">
        <x-layout.container>
            <nav aria-label="مسیر صفحه" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--livora-stone)]">
                <a href="{{ route('home') }}" class="hover:text-[var(--livora-text)]">خانه</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('account.index') }}" class="hover:text-[var(--livora-text)]">حساب کاربری</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" class="text-[var(--livora-text)]">پروفایل</span>
            </nav>

            <div class="mx-auto max-w-2xl overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">
                <div class="border-b border-[var(--livora-border)] p-6 sm:p-8">
                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">ACCOUNT / PROFILE</p>
                    <h1 class="mt-3 text-2xl font-semibold">اطلاعات حساب</h1>
                    <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">نام و ایمیل خود را به‌روز نگه دارید تا اطلاعات سفارش‌ها دقیق باشد.</p>
                </div>

                <div class="p-6 sm:p-8">
                    @if (session('success'))
                        <p role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</p>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <x-ui.input
                            name="name"
                            label="نام و نام خانوادگی"
                            :value="old('name', $user->name)"
                            autocomplete="name"
                            required
                        />

                        <x-ui.input
                            type="email"
                            name="email"
                            label="ایمیل"
                            :value="old('email', $user->email)"
                            autocomplete="email"
                            required
                        />

                        <div class="flex flex-col-reverse gap-3 border-t border-[var(--livora-border)] pt-5 sm:flex-row sm:justify-between">
                            <a href="{{ route('account.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] px-5 py-3 text-sm font-medium text-[var(--livora-text)] transition hover:bg-[var(--livora-surface)]">بازگشت به حساب</a>
                            <x-ui.button type="submit" size="lg">ذخیره تغییرات</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
