@extends('layouts.app')

@section('title', 'ورود | فرزین')

@section('description', 'ورود به حساب کاربری فرزین')

@section('content')

    <section class="min-h-[calc(100vh-80px)]">

        <x-layout.container>

            <div class="flex min-h-[calc(100vh-80px)] items-center justify-center py-12">

                <div class="w-full max-w-md">

                    <div class="text-center">

                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            WELCOME BACK
                        </p>

                        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)]">
                            ورود به حساب
                        </h1>

                        <p class="mt-3 text-sm text-[var(--livora-stone)]">
                            برای ادامه وارد حساب کاربری فرزین شوید.
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
                            action="{{ route('login.store') }}"
                            method="POST"
                            class="space-y-5"
                        >

                            @csrf

                            <x-ui.input
                                type="email"
                                name="email"
                                label="ایمیل"
                                placeholder="example@email.com"
                                :value="old('email')"
                                required
                            />

                            <div>

                                <div class="mb-2 flex items-center justify-between gap-4">

                                    <label
                                        for="password"
                                        class="text-sm font-medium text-[var(--livora-ink)]"
                                    >
                                        رمز عبور
                                    </label>

                                    <a
                                        href="#"
                                        class="text-xs text-[var(--livora-accent)]"
                                    >
                                        رمز عبور را فراموش کرده‌اید؟
                                    </a>

                                </div>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="رمز عبور"
                                    required
                                    class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm text-[var(--livora-ink)] outline-none transition-all duration-300 placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-accent)] focus:ring-1 focus:ring-[var(--livora-accent)] @error('password') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                >

                                @error('password')
                                    <p
                                        class="mt-2 flex items-start gap-2 text-[11px] leading-6 text-red-600"
                                        role="alert"
                                    >
                                        <span aria-hidden="true">!</span>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror

                            </div>


                            <label class="flex cursor-pointer items-center gap-3">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                class="h-4 w-4 rounded border-[var(--livora-border)] accent-[var(--livora-accent)]"
                                >

                                <span class="text-sm text-[var(--livora-stone)]">
                                    مرا به خاطر بسپار
                                </span>

                            </label>


                            <x-ui.button
                                type="submit"
                                size="lg"
                                class="w-full"
                            >
                                ورود به حساب
                            </x-ui.button>

                        </form>


                        <div class="mt-7 border-t border-[var(--livora-border)] pt-6 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                هنوز حساب کاربری ندارید؟
                            </p>

                            <a
                                href="{{ route('register') }}"
                                class="mt-2 inline-block text-sm font-medium text-[var(--livora-accent)]"
                            >
                                ایجاد حساب جدید
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>

    </section>

@endsection
