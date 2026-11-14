@extends('layouts.app')

@section('title', $pageTitle . ' | فرجی‌هوم')
@section('description', $pageLead)
@section('canonical', route($canonicalRoute))

@php
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqs)->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
        ])->all(),
    ];
@endphp

@section('content')
<main class="bg-[var(--livora-cream)]">
    <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
        <x-layout.container>
            <div class="py-10 sm:py-14">
                <nav aria-label="مسیر صفحه" class="flex items-center gap-2 text-xs text-[var(--livora-stone)]">
                    <a href="{{ route('home') }}" class="hover:text-[var(--livora-accent)]">خانه</a><span>/</span><span>{{ $pageTitle }}</span>
                </nav>
                <h1 class="mt-6 text-3xl font-black leading-relaxed text-[var(--livora-ink)] sm:text-4xl">{{ $pageTitle }}</h1>
                @if($pageLead)<p class="mt-4 max-w-3xl text-sm leading-8 text-[var(--livora-stone)] sm:text-base">{{ $pageLead }}</p>@endif
            </div>
        </x-layout.container>
    </section>

    <x-layout.container>
        <div class="mx-auto max-w-4xl py-10 sm:py-14">
            @if($sections)
                <div class="space-y-5">
                    @foreach($sections as $index => $section)
                        <section class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-7">
                            <p class="text-xs font-bold text-[var(--livora-accent)]">{{ \App\Helpers\PersianNumberHelper::format($index + 1) }}</p>
                            <h2 class="mt-2 text-lg font-bold text-[var(--livora-ink)]">{{ $section['title'] }}</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-8 text-[var(--livora-stone)]">{{ $section['body'] }}</p>
                        </section>
                    @endforeach
                </div>
            @elseif($faqs)
                <div class="divide-y divide-[var(--livora-border)] rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-5 sm:px-7">
                    @foreach($faqs as $faq)
                        <details class="py-5">
                            <summary class="cursor-pointer text-sm font-bold text-[var(--livora-ink)]">{{ $faq['question'] }}</summary>
                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-8 text-center">
                    <h2 class="text-lg font-bold">اطلاعات این صفحه هنوز ثبت نشده است.</h2>
                    <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">برای دریافت اطلاعات بیشتر، با فروشگاه ارتباط بگیرید.</p>
                </div>
            @endif

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-[var(--livora-surface)] p-5">
                <p class="text-sm leading-7 text-[var(--livora-text)]">اگر درباره این موضوع پرسشی دارید، از صفحه تماس با ما استفاده کنید.</p>
                <a href="{{ route('contact') }}" class="rounded-xl bg-[var(--livora-ink)] px-5 py-3 text-xs font-bold text-white transition hover:bg-[var(--livora-accent)]">تماس با فروشگاه</a>
            </div>
        </div>
    </x-layout.container>
</main>
@endsection

@if($faqs)
    @push('seo')
        <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endpush
@endif
