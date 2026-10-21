@props([
'categories' => collect(),
'selectedCategory' => null,
'mobile' => false,
])

<div class="space-y-5">

    {{-- =========================================================
         CATEGORY
    ========================================================== --}}

    <section>

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                CATEGORY
            </p>

            <h3 class="mt-2 text-sm font-semibold">
                دسته‌بندی
            </h3>

        </div>


        <div class="mt-5">

            @if($categories->isNotEmpty())

                <div class="space-y-2">

                    @foreach($categories as $category)

                        <label
                            class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3 transition hover:border-[var(--livora-border)]"
                        >

                            <span class="flex min-w-0 items-center gap-3">

                                <input
                                    type="radio"
                                    name="category"
                                    value="{{ $category->slug }}"
                                    @checked((string) $selectedCategory === (string) $category->slug)
                                    @change="$el.form.submit()"
                                    class="h-4 w-4 border-[var(--livora-border)] text-[var(--livora-text)] focus:ring-[var(--livora-ink)]"
                                >

                                <span class="truncate text-xs text-[var(--livora-text)]">
                                    {{ $category->name }}
                                </span>

                            </span>


                            @if(isset($category->products_count))

                                <span class="shrink-0 text-[10px] text-[var(--livora-stone)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($category->products_count) }}
                                </span>

                            @endif

                        </label>

                    @endforeach

                </div>

            @else

                <p class="text-xs leading-6 text-[var(--livora-stone)]">
                    دسته‌بندی‌ای برای نمایش وجود ندارد.
                </p>

            @endif

        </div>

    </section>


    {{-- =========================================================
         SEARCH
    ========================================================== --}}

    <section>

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                SEARCH
            </p>

            <h3 class="mt-2 text-sm font-semibold">
                جستجو
            </h3>

        </div>


        <div class="mt-5">

            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="نام یا مدل محصول..."
                @keydown.enter.prevent="$el.form.submit()"
                class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-4 py-3.5 text-sm outline-none transition placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-ink)]"
            >

        </div>

    </section>


    {{-- =========================================================
         INSTALLMENT
    ========================================================== --}}

    <section>

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                PAYMENT
            </p>

            <h3 class="mt-2 text-sm font-semibold">
                روش خرید
            </h3>

        </div>


        <div class="mt-5 space-y-2">

            <label
                class="flex cursor-pointer items-center gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3"
            >

                <input
                    type="checkbox"
                    name="installment"
                    value="1"
                    @checked(request()->boolean('installment'))
                @change="$el.form.submit()"
                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-text)] focus:ring-[var(--livora-ink)]"
                >

                <span class="text-xs">
                    فقط محصولات قابل خرید اقساطی
                </span>

            </label>

        </div>

    </section>


    {{-- =========================================================
         PRICE
    ========================================================== --}}

    <section>

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                PRICE
            </p>

            <h3 class="mt-2 text-sm font-semibold">
                محدوده قیمت
            </h3>

        </div>


        <div class="mt-5 grid grid-cols-2 gap-3">

            <div>

                <label
                    for="{{ $mobile ? 'min_price_mobile' : 'min_price_desktop' }}"
                    class="mb-2 block text-[10px] text-[var(--livora-stone)]"
                >
                    حداقل
                </label>

                <input
                    id="{{ $mobile ? 'min_price_mobile' : 'min_price_desktop' }}"
                    type="number"
                    name="min_price"
                    min="0"
                    value="{{ request('min_price') }}"
                    placeholder="0"
                    @keydown.enter.prevent="$el.form.submit()"
                    class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs outline-none focus:border-[var(--livora-ink)]"
                >

            </div>


            <div>

                <label
                    for="{{ $mobile ? 'max_price_mobile' : 'max_price_desktop' }}"
                    class="mb-2 block text-[10px] text-[var(--livora-stone)]"
                >
                    حداکثر
                </label>

                <input
                    id="{{ $mobile ? 'max_price_mobile' : 'max_price_desktop' }}"
                    type="number"
                    name="max_price"
                    min="0"
                    value="{{ request('max_price') }}"
                    placeholder="مثلاً 100000000"
                    @keydown.enter.prevent="$el.form.submit()"
                    class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs outline-none focus:border-[var(--livora-ink)]"
                >

            </div>

        </div>

    </section>


    {{-- =========================================================
         STATUS
    ========================================================== --}}

    <section>

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                DISCOVERY
            </p>

            <h3 class="mt-2 text-sm font-semibold">
                محصولات ویژه
            </h3>

        </div>


        <div class="mt-5 space-y-2">

            {{-- FEATURED --}}

            <label
                class="flex cursor-pointer items-center gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3"
            >

                <input
                    type="checkbox"
                    name="featured"
                    value="1"
                    @checked(request()->boolean('featured'))
                @change="$el.form.submit()"
                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-text)] focus:ring-[var(--livora-ink)]"
                >

                <span class="text-xs">
                    فقط محصولات ویژه
                </span>

            </label>


            {{-- NEW --}}

            <label
                class="flex cursor-pointer items-center gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3"
            >

                <input
                    type="checkbox"
                    name="new"
                    value="1"
                    @checked(request()->boolean('new'))
                @change="$el.form.submit()"
                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-text)] focus:ring-[var(--livora-ink)]"
                >

                <span class="text-xs">
                    فقط محصولات جدید
                </span>

            </label>

        </div>

    </section>

</div>
