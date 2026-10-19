<footer class="border-t border-white/10 bg-[var(--livora-ink)] text-[var(--livora-cream)]">

    <x-layout.container>

        {{-- =========================================================
             MAIN FOOTER
        ========================================================== --}}
        <div class="grid gap-12 py-14 sm:py-16 lg:grid-cols-[1.25fr_0.75fr_0.75fr_1fr] lg:py-20">

            {{-- Brand --}}
            <div class="max-w-md">

                <a
                    href="{{ route('home') }}"
                    aria-label="فرزین"
                    class="inline-flex items-center"
                >
                    <span class="text-2xl font-semibold tracking-[0.24em] text-white transition hover:text-[var(--livora-cream)]">
                        فرزین
                    </span>
                </a>

                <p class="mt-6 max-w-sm text-sm leading-8 text-white/50">
                    هود و سینک آشپزخانه؛ انتخابی دقیق برای کارایی بهتر و هماهنگی با فضای شما.
                </p>

                <div class="mt-7 flex flex-wrap gap-2">

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] text-white/50">
                        HOODS
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] text-white/50">
                        Living
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] text-white/50">
                        SINKS
                    </span>

                </div>

            </div>


            {{-- Shop --}}
            <div>

                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-white/40">
                    SHOP
                </p>

                <nav
                    aria-label="لینک‌های فروشگاه"
                    class="mt-5 flex flex-col gap-3"
                >

                    <a
                        href="{{ route('shop.index') }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        همه محصولات
                    </a>

                    <a
                        href="{{ route('categories.index') }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        دسته‌بندی‌ها
                    </a>

                    <a
                        href="{{ route('blog.index') }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        بلاگ و راهنمای خرید
                    </a>

                    <a
                        href="{{ route('shop.index', ['sort' => 'newest']) }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        تازه‌ها
                    </a>

                    <a
                        href="{{ route('shop.index') }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        محصولات منتخب
                    </a>

                    <a
                        href="{{ route('cart.index') }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        سبد خرید
                    </a>

                </nav>

            </div>


            {{-- Customer --}}
            <div>

                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-white/40">
                    CUSTOMER
                </p>

                <nav
                    aria-label="لینک‌های مشتریان"
                    class="mt-5 flex flex-col gap-3"
                >

                    @auth

                        @if(auth()->user()->isCustomer())

                            <a
                                href="{{ route('account.index') }}"
                                class="text-sm text-white/55 transition hover:text-white"
                            >
                                حساب کاربری
                            </a>

                            <a
                                href="{{ route('account.orders.index') }}"
                                class="text-sm text-white/55 transition hover:text-white"
                            >
                                سفارش‌های من
                            </a>

                            <a
                                href="{{ route('account.wishlist.index') }}"
                                class="text-sm text-white/55 transition hover:text-white"
                            >
                                علاقه‌مندی‌ها
                            </a>

                            <a
                                href="{{ route('account.addresses.index') }}"
                                class="text-sm text-white/55 transition hover:text-white"
                            >
                                آدرس‌ها
                            </a>

                        @elseif(auth()->user()->isAdmin())

                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="text-sm text-white/55 transition hover:text-white"
                            >
                                پنل مدیریت
                            </a>

                        @endif

                    @else

                        <a
                            href="{{ route('login') }}"
                            class="text-sm text-white/55 transition hover:text-white"
                        >
                            ورود
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="text-sm text-white/55 transition hover:text-white"
                        >
                            ثبت‌نام
                        </a>

                    @endauth

                    <a
                        href="{{ route('contact') }}"
                        class="text-sm text-white/55 transition hover:text-white"
                    >
                        تماس با ما
                    </a>

                </nav>

            </div>


            {{-- Contact / Newsletter --}}
            <div>

                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-white/40">
                    CONTACT
                </p>

                <div class="mt-5 space-y-4">

                    <a
                        href="tel:{{ config('app.contact.phone') }}"
                        class="block break-all text-sm text-white/55 transition hover:text-white"
                    >
                        {{ config('app.contact.phone', '+98 00 000 0000') }}
                    </a>

                    <a
                        href="mailto:{{ config('app.contact.email') }}"
                        class="block break-all text-sm text-white/55 transition hover:text-white"
                    >
                        {{ config('app.contact.email', 'info@farzin.example') }}
                    </a>

                    <p class="text-sm leading-7 text-white/45">
                        {{ config('app.contact.address', 'تهران، ایران') }}
                    </p>

                </div>


                {{-- Contact CTA --}}
                <a
                    href="{{ route('contact') }}"
                    class="mt-6 inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-xs font-medium text-white transition hover:border-white/20 hover:bg-white/10"
                >
                    ارتباط با فرزین
                    <span class="mr-2">←</span>
                </a>

            </div>

        </div>


        {{-- =========================================================
             TRUST STRIP
        ========================================================== --}}
        <div class="grid border-t border-white/10 sm:grid-cols-3">

            <div class="border-b border-white/10 py-5 sm:border-b-0 sm:border-l sm:px-6 sm:py-6">

                <p class="text-[10px] uppercase tracking-[0.18em] text-white/35">
                    PAYMENT
                </p>

                <p class="mt-2 text-xs leading-6 text-white/50">
                    مسیر پرداخت متناسب با سفارش و روش انتخابی مدیریت می‌شود.
                </p>

            </div>

            <div class="border-b border-white/10 py-5 sm:border-b-0 sm:px-6 sm:py-6">

                <p class="text-[10px] uppercase tracking-[0.18em] text-white/35">
                    SINKS
                </p>

                <p class="mt-2 text-xs leading-6 text-white/50">
                    مشخصات، ابعاد و نوع نصب هر محصول را پیش از ثبت سفارش بررسی کنید.
                </p>

            </div>

            <div class="py-5 sm:px-6 sm:py-6">

                <p class="text-[10px] uppercase tracking-[0.18em] text-white/35">
                    SUPPORT
                </p>

                <p class="mt-2 text-xs leading-6 text-white/50">
                    برای راهنمایی انتخاب محصول یا پیگیری سفارش با تیم فرزین در تماس باشید.
                </p>

            </div>

        </div>


        {{-- =========================================================
             BOTTOM
        ========================================================== --}}
        <div class="flex flex-col gap-5 border-t border-white/10 py-6 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <p class="text-xs text-white/40">
                    © {{ date('Y') }} فرزین.
                    تمامی حقوق محفوظ است.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-4 sm:gap-6">

                <a
                    href="{{ route('about') }}"
                    class="text-xs text-white/40 transition hover:text-white"
                >
                    درباره ما
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="text-xs text-white/40 transition hover:text-white"
                >
                    تماس
                </a>

                @auth

                    @if(auth()->user()->isCustomer())

                        <a
                            href="{{ route('account.index') }}"
                            class="text-xs text-white/40 transition hover:text-white"
                        >
                            حساب من
                        </a>

                    @endif

                @endauth

                {{-- Social --}}
                <a
                    href="#"
                    aria-label="Instagram"
                    class="text-xs text-white/40 transition hover:text-white"
                >
                    Instagram
                </a>

                <a
                    href="#"
                    aria-label="Telegram"
                    class="text-xs text-white/40 transition hover:text-white"
                >
                    Telegram
                </a>

            </div>

        </div>

    </x-layout.container>

</footer>
