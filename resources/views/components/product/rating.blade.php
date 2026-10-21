@props([
'product' => null,
'rating' => null,
'count' => null,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Resolve Rating Data
    |--------------------------------------------------------------------------
    |
    | The component is intentionally defensive so it works even if the
    | current Product model does not have a rating system yet.
    |
    */

    $resolvedRating = $rating;

    if ($resolvedRating === null && $product) {
        $resolvedRating = $product->rating
            ?? $product->average_rating
            ?? null;
    }

    $resolvedCount = $count;

    if ($resolvedCount === null && $product) {
        $resolvedCount = $product->reviews_count
            ?? $product->ratings_count
            ?? 0;
    }

    $resolvedRating = is_numeric($resolvedRating)
        ? round((float) $resolvedRating, 1)
        : 0;

    $resolvedRating = max(
        0,
        min(5, $resolvedRating)
    );

    $resolvedCount = max(
        0,
        (int) $resolvedCount
    );

    $hasRating = $resolvedRating > 0;

    $fullStars = (int) floor($resolvedRating);

    $hasHalfStar =
        ($resolvedRating - $fullStars) >= 0.5;

    $emptyStars =
        5
        - $fullStars
        - ($hasHalfStar ? 1 : 0);

    $productName =
        $product?->name
        ?? 'محصول';

    $ratingText =
        $hasRating
            ? number_format($resolvedRating, 1)
            : 'بدون امتیاز';

    $countText =
        $resolvedCount > 0
            ? number_format($resolvedCount) . ' نظر'
            : 'هنوز نظری ثبت نشده';
@endphp

<div
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-3'
    ]) }}
    @if($hasRating)
    itemprop="aggregateRating"
    itemscope
    itemtype="https://schema.org/AggregateRating"
    @endif
>

    @if($hasRating)

        {{-- Rating Number --}}
        <span
            class="text-sm font-semibold text-[var(--livora-text)]"
            @if($hasRating)
            itemprop="ratingValue"
            @endif
        >
            {{ $ratingText }}
        </span>

        {{-- Stars --}}
        <span
            class="flex items-center gap-0.5"
            aria-label="امتیاز {{ $ratingText }} از ۵"
        >

            {{-- Full --}}
            @for($i = 0; $i < $fullStars; $i++)

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="h-3.5 w-3.5 text-[var(--livora-accent)]"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10.788 3.21c.152-.462.637-.777 1.212-.777s1.06.315 1.212.777l1.558 4.732a1.25 1.25 0 0 0 1.185.86h4.982c.53 0 .998.342 1.161.846.164.505-.02 1.056-.456 1.362l-4.03 2.93a1.25 1.25 0 0 0-.454 1.397l1.554 4.73c.166.504-.012 1.056-.444 1.366-.432.31-1.012.31-1.444 0l-4.03-2.93a1.25 1.25 0 0 0-1.468 0l-4.03 2.93c-.432.31-1.012.31-1.444 0-.432-.31-.61-.862-.444-1.366l1.554-4.73a1.25 1.25 0 0 0-.454-1.397l-4.03-2.93a1.25 1.25 0 0 1-.456-1.362c.163-.504.631-.846 1.161-.846h4.982a1.25 1.25 0 0 0 1.185-.86l1.558-4.732Z"
                        clip-rule="evenodd"
                    />
                </svg>

            @endfor


            {{-- Half --}}
            @if($hasHalfStar)

                <span class="relative block h-3.5 w-3.5">

                    {{-- Empty Base --}}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        class="absolute inset-0 h-3.5 w-3.5 text-[var(--livora-border)]"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m11.48 3.499-2.18 6.695H2.25l5.728 4.162-2.18 6.695L11.48 16.89l5.682 4.161-2.18-6.695 5.728-4.162h-7.05L11.48 3.5Z"
                        />
                    </svg>

                    {{-- Half Fill --}}
                    <span class="absolute inset-y-0 right-0 w-1/2 overflow-hidden">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="absolute right-0 h-3.5 w-3.5 text-[var(--livora-accent)]"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10.788 3.21c.152-.462.637-.777 1.212-.777s1.06.315 1.212.777l1.558 4.732a1.25 1.25 0 0 0 1.185.86h4.982c.53 0 .998.342 1.161.846.164.505-.02 1.056-.456 1.362l-4.03 2.93a1.25 1.25 0 0 0-.454 1.397l1.554 4.73c.166.504-.012 1.056-.444 1.366-.432.31-1.012.31-1.444 0l-4.03-2.93a1.25 1.25 0 0 0-1.468 0l-4.03 2.93c-.432.31-1.012.31-1.444 0-.432-.31-.61-.862-.444-1.366l1.554-4.73a1.25 1.25 0 0 0-.454-1.397l-4.03-2.93a1.25 1.25 0 0 1-.456-1.362c.163-.504.631-.846 1.161-.846h4.982a1.25 1.25 0 0 0 1.185-.86l1.558-4.732Z"
                                clip-rule="evenodd"
                            />
                        </svg>

                    </span>

                </span>

            @endif


            {{-- Empty --}}
            @for($i = 0; $i < $emptyStars; $i++)

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    class="h-3.5 w-3.5 text-[var(--livora-border)]"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m11.48 3.499-2.18 6.695H2.25l5.728 4.162-2.18 6.695L11.48 16.89l5.682 4.161-2.18-6.695 5.728-4.162h-7.05L11.48 3.5Z"
                    />
                </svg>

            @endfor

        </span>

        {{-- Review Count --}}
        <span
            class="text-[11px] text-[var(--livora-stone)]"
            @if($hasRating)
            itemprop="reviewCount"
            @endif
        >
            {{ $countText }}
        </span>

        @if($hasRating)
            <meta
                itemprop="bestRating"
                content="5"
            >

            <meta
                itemprop="worstRating"
                content="1"
            >
        @endif

    @else

        {{-- Empty State --}}
        <span
            class="flex items-center gap-1.5 text-[11px] text-[var(--livora-stone)]"
        >

            <span class="flex items-center gap-0.5">

                @for($i = 0; $i < 5; $i++)

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        class="h-3 w-3 text-[var(--livora-border)]"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m11.48 3.499-2.18 6.695H2.25l5.728 4.162-2.18 6.695L11.48 16.89l5.682 4.161-2.18-6.695h-7.05L11.48 3.5Z"
                        />
                    </svg>

                @endfor

            </span>

            <span>
                {{ $countText }}
            </span>

        </span>

    @endif

</div>
