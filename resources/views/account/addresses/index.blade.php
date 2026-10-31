@extends('layouts.app')

@section('title', 'آدرس‌های من | فرزین')
@section('description', 'مدیریت آدرس‌های تحویل سفارش در فرزین.')

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')
    <section class="bg-[var(--livora-cream)] py-10 sm:py-14">
        <x-layout.container>
            <nav aria-label="مسیر صفحه" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--livora-stone)]">
                <a href="{{ route('home') }}" class="hover:text-[var(--livora-text)]">خانه</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('account.index') }}" class="hover:text-[var(--livora-text)]">حساب کاربری</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" class="text-[var(--livora-text)]">آدرس‌ها</span>
            </nav>

            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">DELIVERY / ADDRESSES</p>
                    <h1 class="mt-3 text-3xl font-semibold">آدرس‌های تحویل</h1>
                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">آدرس‌های ذخیره‌شده را مدیریت کنید یا آدرس تازه‌ای اضافه کنید.</p>
                </div>
                <a href="{{ route('account.index') }}" class="text-sm font-medium text-[var(--livora-accent)]">بازگشت به حساب ←</a>
            </div>

            @if (session('success'))
                <p role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</p>
            @endif
            @if (session('error'))
                <p role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</p>
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

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <div class="space-y-5">
                    @forelse ($addresses as $address)
                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold">{{ $address->title ?: 'آدرس تحویل' }}</h2>
                                    <p class="mt-1 text-xs text-[var(--livora-stone)]">{{ $address->province }}، {{ $address->city }}</p>
                                </div>
                                @if ($address->is_default)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800">آدرس پیش‌فرض</span>
                                @endif
                            </div>

                            <form action="{{ route('account.addresses.update', $address) }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <x-ui.input name="title" label="عنوان آدرس" :value="old('title', $address->title)" placeholder="خانه، محل کار..." />
                                    <x-ui.input name="phone" label="شماره تماس" :value="old('phone', $address->phone)" inputmode="tel" required />
                                    <x-ui.input name="first_name" label="نام گیرنده" :value="old('first_name', $address->first_name)" required />
                                    <x-ui.input name="last_name" label="نام خانوادگی گیرنده" :value="old('last_name', $address->last_name)" required />
                                    <x-ui.input name="province" label="استان" :value="old('province', $address->province)" required />
                                    <x-ui.input name="city" label="شهر" :value="old('city', $address->city)" required />
                                    <div class="sm:col-span-2">
                                        <x-ui.input name="address" label="نشانی کامل" :value="old('address', $address->address)" required />
                                    </div>
                                    <x-ui.input name="postal_code" label="کد پستی ۱۰ رقمی" :value="old('postal_code', $address->postal_code)" inputmode="numeric" required />
                                    <x-ui.input name="unit" label="واحد (اختیاری)" :value="old('unit', $address->unit)" />
                                </div>

                                <label class="flex items-center gap-3 text-sm text-[var(--livora-stone)]">
                                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default)) class="h-4 w-4 accent-[var(--livora-accent)]">
                                    انتخاب به‌عنوان آدرس پیش‌فرض
                                </label>

                                <div class="flex flex-wrap gap-3 border-t border-[var(--livora-border)] pt-4">
                                    <x-ui.button type="submit" size="sm">ذخیره آدرس</x-ui.button>
                                </div>
                            </form>

                            <div class="mt-3 flex flex-wrap gap-3">
                                @unless ($address->is_default)
                                    <form action="{{ route('account.addresses.default', $address) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-xl border border-[var(--livora-border)] px-4 py-2 text-xs font-medium transition hover:bg-[var(--livora-surface)]">پیش‌فرض کردن</button>
                                    </form>
                                @endunless

                                <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('این آدرس حذف شود؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-red-200 px-4 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">حذف آدرس</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[2rem] border border-dashed border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-12 text-center">
                            <h2 class="text-lg font-semibold">هنوز آدرسی ثبت نشده است</h2>
                            <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">برای تسویه‌حساب سریع‌تر، اولین آدرس تحویل خود را اضافه کنید.</p>
                        </div>
                    @endforelse

                    @if($addresses->hasPages())
                        <div class="pt-2">
                            {{ $addresses->links() }}
                        </div>
                    @endif
                </div>

                <aside class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">
                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">NEW ADDRESS</p>
                    <h2 class="mt-2 text-xl font-semibold">افزودن آدرس</h2>
                    <form action="{{ route('account.addresses.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <x-ui.input name="title" label="عنوان آدرس" :value="old('title')" placeholder="خانه، محل کار..." />
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <x-ui.input name="first_name" label="نام گیرنده" :value="old('first_name')" required />
                            <x-ui.input name="last_name" label="نام خانوادگی گیرنده" :value="old('last_name')" required />
                        </div>
                        <x-ui.input name="phone" label="شماره موبایل" :value="old('phone')" inputmode="tel" required />
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <x-ui.input name="province" label="استان" :value="old('province')" required />
                            <x-ui.input name="city" label="شهر" :value="old('city')" required />
                        </div>
                        <x-ui.input name="address" label="نشانی کامل" :value="old('address')" required />
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <x-ui.input name="postal_code" label="کد پستی ۱۰ رقمی" :value="old('postal_code')" inputmode="numeric" required />
                            <x-ui.input name="unit" label="واحد (اختیاری)" :value="old('unit')" />
                        </div>
                        <label class="flex items-center gap-3 text-sm text-[var(--livora-stone)]">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="h-4 w-4 accent-[var(--livora-accent)]">
                            ثبت به‌عنوان آدرس پیش‌فرض
                        </label>
                        <x-ui.button type="submit" size="lg" class="w-full">ثبت آدرس</x-ui.button>
                    </form>
                </aside>
            </div>
        </x-layout.container>
    </section>
@endsection
