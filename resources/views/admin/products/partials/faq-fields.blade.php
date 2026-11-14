@php($productFaqs = old('faqs', $product->faqs ?? []))

<section class="sm:col-span-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5">
    <div>
        <h3 class="text-sm font-bold text-[var(--admin-text)]">پرسش‌های متداول همین محصول</h3>
        <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">حداکثر ۷ پرسش و پاسخ اختصاصی ثبت کنید. فقط اطلاعاتی را بنویسید که برای همین مدل تأیید شده است؛ موارد خالی در صفحه محصول نمایش داده نمی‌شوند.</p>
    </div>

    <div class="mt-4 space-y-3">
        @for($faqIndex = 0; $faqIndex < 7; $faqIndex++)
            @php($faq = $productFaqs[$faqIndex] ?? [])
            <div class="grid gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-card)] p-3 sm:grid-cols-2">
                <label class="block text-xs font-semibold">
                    پرسش {{ \App\Helpers\PersianNumberHelper::format($faqIndex + 1) }}
                    <input
                        name="faqs[{{ $faqIndex }}][question]"
                        maxlength="180"
                        value="{{ old("faqs.{$faqIndex}.question", $faq['question'] ?? '') }}"
                        class="admin-input mt-2 w-full"
                        placeholder="مثلاً ابعاد برش نصب این مدل چیست؟"
                    >
                    @error("faqs.{$faqIndex}.question")
                        <span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-xs font-semibold">
                    پاسخ
                    <textarea
                        name="faqs[{{ $faqIndex }}][answer]"
                        maxlength="1200"
                        rows="2"
                        class="admin-textarea mt-2 w-full"
                        placeholder="پاسخ دقیق و مربوط به همین مدل"
                    >{{ old("faqs.{$faqIndex}.answer", $faq['answer'] ?? '') }}</textarea>
                    @error("faqs.{$faqIndex}.answer")
                        <span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>
                    @enderror
                </label>
            </div>
        @endfor
    </div>

    @error('faqs')
        <p class="mt-2 text-xs text-[var(--admin-danger)]" role="alert">{{ $message }}</p>
    @enderror
</section>
