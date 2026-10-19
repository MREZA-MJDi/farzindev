@extends('layouts.app')

@section(
    'title',
    $category->name . ' | خرید هود و سینک و محصولات خانه | فرزین'
)

@section(
    'description',
    $category->meta_description
        ?? ('مشاهده و خرید محصولات دسته‌بندی ' . $category->name . ' در فرزین؛ بررسی قیمت، مشخصات و شرایط خرید اقساطی.')
)

@section(
    'canonical',
    route('categories.show', $category->slug)
)

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="{{ $category->name }} | فرزین"
    >

    <meta
        property="og:description"
        content="{{ $category->meta_description ?? ('محصولات ' . $category->name . ' در فرزین') }}"
    >

    <meta
        property="og:url"
        content="{{ route('categories.show', $category->slug) }}"
    >

    @if($category->image)
        <meta
            property="og:image"
            content="{{ $category->image_url }}"
        >
    @endif

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="{{ $category->name }} | فرزین"
    >

    <meta
        name="twitter:description"
        content="{{ $category->meta_description ?? ('محصولات ' . $category->name . ' در فرزین') }}"
    >

    @if($category->image)
        <meta
            name="twitter:image"
            content="{{ $category->image_url }}"
        >
    @endif

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "فرزین",
    "url": @json(url('/')),
    "potentialAction": {
        "@@type": "SearchAction",
        "target": @json(url('/shop') . '?search={search_term_string}'),
        "query-input": "required name=search_term_string"
    }
}
</script>

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "فرزین",
    "url": @json(url('/'))
        }
</script>

@endpush

@section('content')

    <div class="bg-[var(--livora-cream)]">

        {{-- =========================================================
             CATEGORY HERO
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

                    {{-- Breadcrumb --}}
                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('categories.index') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            دسته‌بندی‌ها
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        {{ $category->name }}
                    </span>

                    </nav>

                    <div class="mt-8 grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">

                        <div class="max-w-3xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                COLLECTION
                            </p>

                            <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                                {{ $category->name }}
                            </h1>

                            @if($category->description)

                                <div class="mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                                    {{ $category->description }}
                                </div>

                            @else

                                <p class="mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                                    مجموعه محصولات {{ $category->name }} را بررسی کنید؛
                                    قیمت، مشخصات، موجودی و شرایط خرید اقساطی محصولات را مشاهده کنید.
                                </p>

                            @endif

                            <div class="mt-7 flex flex-wrap items-center gap-3">

                            <span class="rounded-full border border-[var(--livora-border)] px-4 py-2 text-[11px] text-[var(--livora-stone)]">
                                {{ number_format($products->total()) }}
                                محصول
                            </span>

                                <a
                                    href="{{ route('shop.index', ['category' => $category->id]) }}"
                                    class="rounded-full bg-[var(--livora-ink)] px-4 py-2 text-[11px] font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                >
                                    مشاهده همه محصولات
                                </a>

                            </div>

                        </div>

                        {{-- Category image --}}
                        <div class="overflow-hidden rounded-[2rem] bg-[var(--livora-surface)]">

                            @if($category->image)

                                <img
                                    src="{{ $category->image_url }}"
                                    alt="{{ $category->name }}"
                                    class="aspect-[4/3] h-full w-full object-cover transition duration-700 hover:scale-[1.03]"
                                >

                            @else

                                <div class="flex aspect-[4/3] items-center justify-center">
                                <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                                    فرزین
                                </span>
                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             PRODUCTS
        ========================================================== --}}
        <section>

            <x-layout.container>

                <div class="py-10 sm:py-14">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                PRODUCTS
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">
                                محصولات این مجموعه
                            </h2>

                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            class="text-sm font-medium text-[var(--livora-accent)]"
                        >
                            بازگشت به فروشگاه
                            <span class="mr-1">←</span>
                        </a>

                    </div>


                    @if($products->isNotEmpty())

                        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 xl:grid-cols-4">

                            @foreach($products as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                        <div class="mt-14">
                            {{ $products->withQueryString()->links() }}
                        </div>

                    @else

                        <div class="mt-8 rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-24 text-center">

                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-sm text-[var(--livora-stone)]">
                                —
                            </div>

                            <h2 class="mt-6 text-2xl font-semibold">
                                محصولی در این دسته پیدا نشد
                            </h2>

                            <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--livora-stone)]">
                                در حال حاضر محصولی در این مجموعه قرار نگرفته است.
                                می‌توانید سایر دسته‌بندی‌ها را بررسی کنید.
                            </p>

                            <a
                                href="{{ route('categories.index') }}"
                                class="mt-7 inline-flex rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                مشاهده دسته‌بندی‌ها
                            </a>

                        </div>

                    @endif

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             CATEGORY SEO CONTENT
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="max-w-4xl py-14 sm:py-18">

                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                        ABOUT THIS COLLECTION
                    </p>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight sm:text-3xl">
                        راهنمای انتخاب {{ $category->name }}
                    </h2>

                    <div class="mt-5 space-y-4 text-sm leading-8 text-[var(--livora-stone)]">

                        @if($category->description)

                            <p>
                                {{ $category->description }}
                            </p>

                        @endif

                        <p>
                            هنگام انتخاب محصول از این مجموعه، علاوه بر ظاهر،
                            به ابعاد فضا، متریال، رنگ، کاربرد و هماهنگی آن با سایر عناصر خانه توجه کنید.
                            فرزین تلاش می‌کند اطلاعات موردنیاز برای یک انتخاب آگاهانه را
                            در صفحه هر محصول در اختیار شما قرار دهد.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            فرزین
                        </p>

                        <h2 class="mt-2 text-2xl font-semibold">
                            هنوز انتخابت را پیدا نکردی؟
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                            مجموعه کامل فروشگاه را بررسی کن.
                        </p>

                    </div>

                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex w-fit rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                    >
                        مشاهده فروشگاه
                    </a>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
