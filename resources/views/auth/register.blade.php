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

                        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-text)]">
                            ایجاد حساب
                        </h1>

                        <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                            برای ثبت سفارش و پیگیری خریدها، حساب کاربری فرزین بسازید.
                        </p>

                    </div>


                    @if($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4" role="alert" aria-live="polite">
                            <p class="text-xs font-semibold text-red-800">لطفاً موارد زیر را اصلاح کنید:</p>
                            <ul class="mt-2 space-y-1 text-xs leading-6 text-red-700">
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

                                <div>
                                    <x-ui.input
                                        type="password"
                                        name="password"
                                        id="password"
                                        aria-describedby="password-requirements password-live-error"
                                        hint-id="password-requirements"
                                        label="رمز عبور"
                                        placeholder="حداقل ۸ کاراکتر"
                                        autocomplete="new-password"
                                        hint="حداقل ۸ کاراکتر، شامل حرف بزرگ و کوچک انگلیسی و عدد؛ مثال: FarzinPass123"
                                        required
                                    />
                                    <p id="password-live-error" class="mt-2 text-[11px] leading-6 text-red-600" aria-live="polite"></p>
                                </div>

                                <div>
                                    <x-ui.input
                                        type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        aria-describedby="password-confirmation-live-error"
                                        label="تکرار رمز عبور"
                                        placeholder="رمز عبور را دوباره وارد کنید"
                                        autocomplete="new-password"
                                        required
                                    />
                                    <p id="password-confirmation-live-error" class="mt-2 text-[11px] leading-6 text-red-600" aria-live="polite"></p>
                                </div>

                            </div>


                            <p id="password-requirements" class="sr-only">حداقل ۸ کاراکتر، حداقل یک حرف بزرگ انگلیسی، یک حرف کوچک انگلیسی و یک عدد.</p>

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










@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        const passwordError = document.getElementById('password-live-error');
        const confirmationError = document.getElementById('password-confirmation-live-error');
        if (!password || !confirmation || !passwordError || !confirmationError) return;

        const validateConfirmation = () => {
            const mismatch = confirmation.value.length > 0 && confirmation.value !== password.value;
            confirmationError.textContent = mismatch ? 'تکرار رمز عبور با رمز عبور یکسان نیست.' : '';
            confirmation.setAttribute('aria-invalid', mismatch ? 'true' : 'false');
        };

        const validatePassword = () => {
            const value = password.value;
            const errors = [];
            if (value.length < 8) errors.push('حداقل ۸ کاراکتر وارد کنید.');
            if (!/[a-z]/.test(value)) errors.push('حداقل یک حرف کوچک انگلیسی لازم است.');
            if (!/[A-Z]/.test(value)) errors.push('حداقل یک حرف بزرگ انگلیسی لازم است.');
            if (!/[0-9]/.test(value)) errors.push('حداقل یک عدد لازم است.');
            passwordError.textContent = value.length ? errors.join(' ') : '';
            password.setAttribute('aria-invalid', errors.length ? 'true' : 'false');
            validateConfirmation();
        };

        password.addEventListener('input', validatePassword);
        confirmation.addEventListener('input', validateConfirmation);
    });
</script>
@endpush

@endsection
