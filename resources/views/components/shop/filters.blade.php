@props([
'categories' => collect(),
'selectedCategory' => null,
'mobile' => false,
'filterableAttributes' => collect(),
])

<div class="space-y-5">
    @if(request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif

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
                                    @if(!$mobile) @change="$el.form.submit()" @endif
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
                inputmode="search"
                enterkeyhint="search"
                autocomplete="off"
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
                @if(!$mobile) @change="$el.form.submit()" @endif
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


    @if($filterableAttributes->isNotEmpty())
    <section class="border-t border-[var(--livora-border)] pt-5">
        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">SPECIFICATIONS</p>
        <h3 class="mt-2 text-sm font-semibold">فیلتر بر اساس مشخصات</h3>
        <div class="mt-4 space-y-4">
            @foreach($filterableAttributes as $attribute)
                <div>
                    <label class="mb-2 block text-xs font-semibold text-[var(--livora-text)]" for="attribute-filter-{{ $attribute->id }}">{{ $attribute->label }} @if($attribute->unit) ({{ $attribute->unit }}) @endif</label>
                    @if(in_array($attribute->type,['select','multiselect'],true))
                        <select id="attribute-filter-{{ $attribute->id }}" name="attributes[{{ $attribute->id }}]" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs">
                            <option value="">همه</option>
                            @foreach($attribute->options as $option)
                                <option value="{{ $option->id }}" @selected((string) request("attributes.{$attribute->id}") === (string) $option->id)>{{ $option->label }}</option>
                            @endforeach
                        </select>
                    @elseif($attribute->type === 'number')
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" inputmode="decimal" name="attributes[{{ $attribute->id }}][min]" value="{{ request("attributes.{$attribute->id}.min") }}" placeholder="از" aria-label="{{ $attribute->label }} از" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs">
                            <input type="text" inputmode="decimal" name="attributes[{{ $attribute->id }}][max]" value="{{ request("attributes.{$attribute->id}.max") }}" placeholder="تا" aria-label="{{ $attribute->label }} تا" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs">
                        </div>
                    @elseif($attribute->type === 'boolean')
                        <select id="attribute-filter-{{ $attribute->id }}" name="attributes[{{ $attribute->id }}]" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs">
                            <option value="">همه</option><option value="1" @selected(request("attributes.{$attribute->id}") === '1')>بله</option><option value="0" @selected(request("attributes.{$attribute->id}") === '0')>خیر</option>
                        </select>
                    @else
                        <input id="attribute-filter-{{ $attribute->id }}" type="search" name="attributes[{{ $attribute->id }}]" value="{{ request("attributes.{$attribute->id}") }}" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs" placeholder="جستجو در مقدار">
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- =========================================================
         STATUS
    ========================================================== --}}

    <section>

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                فیلتر و جستجو
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
                @if(!$mobile) @change="$el.form.submit()" @endif
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
                @if(!$mobile) @change="$el.form.submit()" @endif
                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-text)] focus:ring-[var(--livora-ink)]"
                >

                <span class="text-xs">
                    فقط محصولات جدید
                </span>

            </label>

        </div>

    </section>

</div>
