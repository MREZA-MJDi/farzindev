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
                    <span class="farzin-site-footer__brand-name transition hover:text-[var(--livora-text)]">
                        فرزین
                    </span>
                </a>

                <p class="farzin-site-footer__brand-caption">تجهیزات تخصصی آشپزخانه</p>
                <p class="farzin-site-footer__intro">
                    هود و سینک آشپزخانه فرزین؛ برای انتخابی هماهنگ با فضای نصب، کاربری و نیاز روزمره.
                </p>

                <div class="mt-7 flex flex-wrap gap-2">

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/75">
                        HOODS
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/75">
                        KITCHEN
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/75">
                        انتخاب محصول
                    </span>

                </div>

            </div>


            {{-- Shop --}}
            <div>

                <h2 class="farzin-site-footer__heading">
                    محصولات و دسته‌بندی‌ها
                </h2>

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
                        href="{{ route('shop.index', ['sort' => 'popular']) }}"
                        class="text-sm text-white/75 transition hover:text-white"
                    >
                        پرفروش‌ترین محصولات
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

                <h2 class="farzin-site-footer__heading">
                    خدمات مشتریان
                </h2>

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

                <h2 class="farzin-site-footer__heading">
                    ارتباط با فرزین
                </h2>

                <p class="farzin-site-footer__intro">
                    برای مشاوره انتخاب هود و سینک، بررسی مشخصات یا پیگیری سفارش با فرزین در ارتباط باشید.
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

                <h2 class="farzin-site-footer__heading">
                    روش‌های خرید
                </h2>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    روش‌های پرداخت و شرایط خرید هر محصول را پیش از ثبت سفارش بررسی کنید.
                </p>

            </div>

            <div class="border-b border-white/10 py-5 md:border-b-0 md:px-6 md:py-6">

                <h2 class="farzin-site-footer__heading">
                    SINKS
                </h2>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    ابعاد، جنس و شیوه نصب را با فضای آشپزخانه و نیاز روزمره تطبیق دهید.
                </p>

            </div>

            <div class="py-5 md:px-6 md:py-6">

                <h2 class="farzin-site-footer__heading">
                    پشتیبانی خرید
                </h2>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    برای انتخاب محصول، دریافت راهنمایی یا پیگیری سفارش کنار شما هستیم.
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
