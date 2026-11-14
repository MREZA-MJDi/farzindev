@if(is_array($category->faqs) && count($category->faqs))
@php
    $faqItems = collect($category->faqs)->filter(fn($faq)=>is_array($faq)&&filled($faq['question']??null)&&filled($faq['answer']??null))->take(7)->values();
    $faqSchema = ['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$faqItems->map(fn($faq)=>['@type'=>'Question','name'=>$faq['question'],'acceptedAnswer'=>['@type'=>'Answer','text'=>$faq['answer']]])->all()];
@endphp
@if($faqItems->isNotEmpty())
<section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">
    <x-layout.container>
        <div class="py-12 sm:py-16">
            <p class="text-[10px] font-semibold tracking-[0.16em] text-[var(--livora-accent)]">پرسش‌های متداول</p>
            <h2 class="mt-2 text-2xl font-black text-[var(--livora-ink)]">پاسخ پرسش‌های رایج درباره {{ $category->name }}</h2>
            <div class="mt-6 divide-y divide-[var(--livora-border)] rounded-2xl border border-[var(--livora-border)] bg-white px-5">
                @foreach($faqItems as $faq)
                    <details class="py-4">
                        <summary class="cursor-pointer text-sm font-semibold text-[var(--livora-text)]">{{ $faq['question'] }}</summary>
                        <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">{{ $faq['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </x-layout.container>
</section>
@push('seo')
<script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
@endif
@endif
