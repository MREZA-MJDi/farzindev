<footer class="farzin-site-footer border-t border-white/10 bg-[var(--livora-ink)] text-[var(--livora-text)]">

    <x-layout.container>

        {{-- =========================================================
             MAIN FOOTER
        ========================================================== --}}
        <div class="farzin-site-footer__main grid min-w-0 grid-cols-1 gap-8 py-10 sm:gap-10 sm:py-14 md:grid-cols-2 lg:grid-cols-[1.25fr_0.75fr_0.75fr_1fr] lg:gap-12 lg:py-20">

            {{-- Brand --}}
            <div class="max-w-md">

                <a
                    href="{{ route('home') }}"
                    aria-label="فرزین"
                    class="inline-flex items-center"
                >
                    <span class="text-2xl font-semibold tracking-[0.24em] text-white transition hover:text-[var(--livora-text)]">
                        فرزین
                    </span>
                </a>

                <p class="mt-6 max-w-sm text-sm leading-8 text-white/75">
                    هود و سینک آشپزخانه؛ انتخابی دقیق برای کارایی بهتر و هماهنگی با فضای شما.
                </p>

                <div class="mt-7 flex flex-wrap gap-2">

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/75">
                        HOODS
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/75">
                        Living
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/75">
                        SINKS
                    </span>

                </div>

            </div>


            {{-- Shop --}}
            <div>

                <p class="farzin-site-footer__heading">
                    SHOP
                </p>

                <nav
                    aria-label="لینک‌های فروشگاه"
                    class="mt-5 flex flex-col gap-3"
                >

                    <a
                        href="{{ route('shop.index') }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        همه محصولات
                    </a>

                    <a
                        href="{{ route('categories.index') }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        دسته‌بندی‌ها
                    </a>

                    <a
                        href="{{ route('blog.index') }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        بلاگ و راهنمای خرید
                    </a>

                    <a
                        href="{{ route('shop.index', ['sort' => 'newest']) }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        تازه‌ها
                    </a>

                    <a
                        href="{{ route('shop.index') }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        محصولات منتخب
                    </a>

                    <a
                        href="{{ route('cart.index') }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        سبد خرید
                    </a>

                </nav>

            </div>


            {{-- Customer --}}
            <div>

                <p class="farzin-site-footer__heading">
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
                                class="text-sm text-white/75 transition hover:text-white"
                            >
                                حساب کاربری
                            </a>

                            <a
                                href="{{ route('account.orders.index') }}"
                                class="text-sm text-white/75 transition hover:text-white"
                            >
                                سفارش‌های من
                            </a>

                            <a
                                href="{{ route('account.wishlist.index') }}"
                                class="text-sm text-white/75 transition hover:text-white"
                            >
                                علاقه‌مندی‌ها
                            </a>

                            <a
                                href="{{ route('account.addresses.index') }}"
                                class="text-sm text-white/75 transition hover:text-white"
                            >
                                آدرس‌ها
                            </a>

                        @elseif(auth()->user()->isAdmin())

                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="text-sm text-white/75 transition hover:text-white"
                            >
                                پنل مدیریت
                            </a>

                        @endif

                    @else

                        <a
                            href="{{ route('login') }}"
                            class="text-sm text-white/75 transition hover:text-white"
                        >
                            ورود
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="text-sm text-white/75 transition hover:text-white"
                        >
                            ثبت‌نام
                        </a>

                    @endauth

                    <a
                        href="{{ route('contact') }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        تماس با ما
                    </a>

                </nav>

            </div>


            {{-- Contact / Newsletter --}}
            <div>

                <p class="farzin-site-footer__heading">
                    CONTACT
                </p>

                <p class="mt-5 max-w-xs text-sm leading-7 text-white/75">
                    برای مشاوره‌ی انتخاب هود و سینک یا پیگیری سفارش، از صفحه‌ی تماس با فرزین پیام بفرستید.
                </p>


                {{-- Contact CTA --}}
                <a
                    href="{{ route('contact') }}"
                    class="mt-6 inline-flex items-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-white transition hover:border-white/20 hover:bg-white/10"
                >
                    ارتباط با فرزین
                    <span class="mr-2">←</span>
                </a>

            </div>

        </div>


        {{-- =========================================================
             TRUST STRIP
        ========================================================== --}}
        <div class="farzin-site-footer__trust grid border-t border-white/10 md:grid-cols-3">

            <div class="border-b border-white/10 py-5 md:border-b-0 md:border-l md:px-6 md:py-6">

                <p class="farzin-site-footer__heading">
                    PAYMENT
                </p>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    مسیر پرداخت متناسب با سفارش و روش انتخابی مدیریت می‌شود.
                </p>

            </div>

            <div class="border-b border-white/10 py-5 md:border-b-0 md:px-6 md:py-6">

                <p class="farzin-site-footer__heading">
                    SINKS
                </p>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    مشخصات، ابعاد و نوع نصب هر محصول را پیش از ثبت سفارش بررسی کنید.
                </p>

            </div>

            <div class="py-5 md:px-6 md:py-6">

                <p class="farzin-site-footer__heading">
                    SUPPORT
                </p>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    برای راهنمایی انتخاب محصول یا پیگیری سفارش با تیم فرزین در تماس باشید.
                </p>

            </div>

        </div>


        {{-- =========================================================
             BOTTOM
        ========================================================== --}}
        <div class="farzin-site-footer__bottom flex flex-col gap-4 border-t border-white/10 py-5 md:flex-row md:items-center md:justify-between md:gap-5 md:py-6">

            <div>

                <p class="text-xs text-white/70">
                    © {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} فرزین.
                    تمامی حقوق محفوظ است.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-4 sm:gap-6">

                <a
                    href="{{ route('about') }}"
                    class="text-xs text-white/70 transition hover:text-white"
                >
                    درباره ما
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="text-xs text-white/70 transition hover:text-white"
                >
                    تماس
                </a>

                @auth

                    @if(auth()->user()->isCustomer())

                        <a
                            href="{{ route('account.index') }}"
                            class="text-xs text-white/70 transition hover:text-white"
                        >
                            حساب من
                        </a>

                    @endif

                @endauth

                {{-- Social --}}
                <a
                    href="#"
                    aria-label="Instagram"
                    class="text-xs text-white/70 transition hover:text-white"
                >
                    Instagram
                </a>

                <a
                    href="#"
                    aria-label="Telegram"
                    class="text-xs text-white/70 transition hover:text-white"
                >
                    Telegram
                </a>

            </div>

        </div>

    </x-layout.container>

</footer>
