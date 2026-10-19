@extends('layouts.app')

@section('title', 'ثبت نام | فرزین')

@section('description', 'ایجاد حساب کاربری در فرزین')

@section('content')

    <section class="min-h-[calc(100vh-80px)]">

        <x-layout.container>

            <div class="flex min-h-[calc(100vh-80px)] items-center justify-center py-12">

                <div class="w-full max-w-lg">

                    <div class="text-center">

                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            عضویت در فرزین
                        </p>

                        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)]">
                            ایجاد حساب
                        </h1>

                        <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                            برای ثبت سفارش و پیگیری خریدها، حساب کاربری فرزین بسازید.
                        </p>

                    </div>


                    @if($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                            <ul class="space-y-1 text-xs leading-6 text-red-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <div class="mt-8 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                        <form
                            action="{{ route('register.store') }}"
                            method="POST"
                            class="space-y-5"
                        >

                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    name="first_name"
                                    label="نام"
                                    placeholder="نام شما"
                                    :value="old('first_name')"
                                    required
                                />

                                <x-ui.input
                                    name="last_name"
                                    label="نام خانوادگی"
                                    placeholder="نام خانوادگی"
                                    :value="old('last_name')"
                                    required
                                />

                            </div>


                            <x-ui.input
                                type="tel"
                                name="phone"
                                label="شماره موبایل"
                                placeholder="09123456789"
                                :value="old('phone')"
                                autocomplete="tel"
                                inputmode="tel"
                                required
                            />


                            <x-ui.input
                                type="email"
                                name="email"
                                label="ایمیل (اختیاری)"
                                placeholder="example@email.com"
                                :value="old('email')"
                                autocomplete="email"
                            />


                            <div class="grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    type="password"
                                    name="password"
                                    label="رمز عبور"
                                    placeholder="حداقل ۸ کاراکتر"
                                    required
                                />

                                <x-ui.input
                                    type="password"
                                    name="password_confirmation"
                                    label="تکرار رمز عبور"
                                    placeholder="تکرار رمز عبور"
                                    required
                                />

                            </div>


                            <label class="flex cursor-pointer items-start gap-3">

                                <input
                                    type="checkbox"
                                    name="terms"
                                    value="1"
                                    @checked(old('terms'))
                                required
                                class="mt-1 h-4 w-4 rounded border-[var(--livora-border)] accent-[var(--livora-accent)]"
                                >

                                <span class="text-xs leading-6 text-[var(--livora-stone)]">
                                    با ایجاد حساب، با
                                    <a
                                        href="#"
                                        class="font-medium text-[var(--livora-accent)]"
                                    >
                                        قوانین و شرایط
                                    </a>
                                    فرزین موافقم.
                                </span>

                            </label>

                            @if($errors->first('terms'))
                                <p
                                    class="mt-2 flex items-start gap-2 text-[11px] leading-6 text-red-600"
                                    role="alert"
                                >
                                    <span aria-hidden="true">!</span>
                                    <span>{{ $errors->first('terms') }}</span>
                                </p>
                            @endif


                            <x-ui.button
                                type="submit"
                                size="lg"
                                class="w-full"
                            >
                                ایجاد حساب
                            </x-ui.button>

                        </form>


                        <div class="mt-7 border-t border-[var(--livora-border)] pt-6 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                قبلاً حساب ساخته‌اید؟
                            </p>

                            <a
                                href="{{ route('login') }}"
                                class="mt-2 inline-block text-sm font-medium text-[var(--livora-accent)]"
                            >
                                ورود به حساب
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>

    </section>

@endsection
