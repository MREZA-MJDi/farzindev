@php
    $productFaqs = collect($product->faqs ?? [])
        ->filter(fn ($faq) => is_array($faq)
            && filled($faq['question'] ?? null)
            && filled($faq['answer'] ?? null))
        ->take(7)
        ->values();

    $productFaqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $productFaqs->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => trim((string) $faq['question']),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => trim((string) $faq['answer']),
            ],
        ])->all(),
    ];
@endphp

@if($productFaqs->isNotEmpty())
    <section class="border-t border-[var(--livora-border)] py-8 sm:py-12" aria-labelledby="product-faq-title">
        <div class="mx-auto max-w-4xl">
            <div class="mb-5">
                <p class="text-[10px] font-semibold tracking-[0.16em] text-[var(--livora-accent)]">راهنمای همین مدل</p>
                <h2 id="product-faq-title" class="mt-2 text-xl font-bold text-[var(--livora-text)]">پرسش‌های متداول {{ $product->name }}</h2>
            </div>

            <div class="divide-y divide-[var(--livora-border)] rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 sm:px-6">
                @foreach($productFaqs as $faq)
                    <details class="py-4">
                        <summary class="cursor-pointer text-sm font-semibold leading-7 text-[var(--livora-text)]">
                            {{ trim((string) $faq['question']) }}
                        </summary>
                        <p class="mt-3 whitespace-pre-line text-sm leading-8 text-[var(--livora-stone)]">{{ trim((string) $faq['answer']) }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    @push('seo')
        <script type="application/ld+json">{!! json_encode($productFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endpush
@endif
