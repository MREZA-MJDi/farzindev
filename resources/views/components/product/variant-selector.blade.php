@props([
'title',
'options' => collect(),
'name',
'type' => 'default',
])

@php
    $options = collect($options);

    $isColor =
        in_array(
            strtolower((string) $type),
            ['color', 'colour', 'رنگ'],
            true
        );

    $typeLabel = match (strtolower((string) $type)) {
        'color', 'colour', 'رنگ' => 'رنگ',
        'size', 'سایز', 'اندازه' => 'سایز',
        default => $title,
    };
@endphp

<div
    x-data="{
        selected: null
    }"
    class="space-y-4"
>

    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                {{ strtoupper($typeLabel) }}
            </p>

            <h3 class="mt-1 text-sm font-semibold text-[var(--livora-ink)]">
                {{ $title }}
            </h3>
        </div>

        <div
            x-show="selected !== null"
            x-cloak
            class="rounded-full bg-[var(--livora-surface)] px-3 py-1.5 text-[11px] text-[var(--livora-stone)]"
        >
            <span>انتخاب شده:</span>
            <span
                x-text="
                    [...$refs.options.querySelectorAll('input')]
                    .find(input => input.value == selected)
                    ?.dataset.label || ''
                "
                class="font-medium text-[var(--livora-ink)]"
            ></span>
        </div>

    </div>


    {{-- Options --}}
    <div
        x-ref="options"
        class="flex flex-wrap gap-2.5"
        role="radiogroup"
        aria-label="{{ $title }}"
    >

        @foreach($options as $option)

            @php
                $optionId = $option->id;

                $optionLabel =
                    $option->value
                    ?? $option->name
                    ?? ('گزینه ' . $optionId);

                $optionStock =
                    isset($option->stock)
                        ? (int) $option->stock
                        : null;

                $isUnavailable =
                    $optionStock !== null
                    && $optionStock < 1;

                /*
                 * Optional color value support.
                 * If later the variant model gets color_hex/color,
                 * this component can immediately use it.
                 */
                $colorValue =
                    $option->color_hex
                    ?? $option->color
                    ?? null;
            @endphp

            <label
                class="group relative cursor-pointer"
                @if($isUnavailable)
                aria-disabled="true"
                @endif
            >

                <input
                    type="radio"
                    name="variants[{{ $name }}]"
                    value="{{ $optionId }}"
                    data-label="{{ $optionLabel }}"
                    data-variant-type="{{ $name }}"
                    data-stock="{{ $optionStock ?? 0 }}"
                    x-model="selected"
                    @change="window.dispatchEvent(new CustomEvent('product-variant-selected', { detail: { id: {{ $optionId }}, type: @js($name), label: @js($optionLabel), stock: {{ $optionStock ?? 0 }} } }))"
                    class="sr-only"
                    @disabled($isUnavailable)
                    @if($isUnavailable)
                    tabindex="-1"
                    @endif
                >

                @if($isColor && $colorValue)

                    {{-- Color option --}}
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-full border-2 transition-all duration-300"
                        :class="
                            selected == {{ $optionId }}
                            ? 'border-[var(--livora-ink)] scale-105 shadow-sm'
                            : 'border-[var(--livora-border)] group-hover:border-[var(--livora-accent)]'
"
                        title="{{ $optionLabel }}"
                    >

                        <span
                            class="h-7 w-7 rounded-full border border-black/10"
                            style="background-color: {{ $colorValue }};"
                        ></span>

                    </span>

                @else

                    {{-- Standard option --}}
                    <span
                        class="inline-flex min-w-[84px] flex-col items-center justify-center rounded-2xl border px-4 py-3 text-center transition-all duration-300"
                        :class="
                            selected == {{ $optionId }}
                            ? 'border-[var(--livora-ink)] bg-[var(--livora-ink)] text-white shadow-sm'
                            : 'border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-ink)] hover:border-[var(--livora-accent)] hover:-translate-y-0.5'
"
                    >

                        <span class="text-sm font-medium">
                            {{ $optionLabel }}
                        </span>

                        @if($optionStock !== null)

                            <span
                                class="mt-1 text-[10px]"
                                :class="
                                    selected == {{ $optionId }}
                                    ? 'text-white/60'
                                    : 'text-[var(--livora-stone)]'
"
                            >
                                @if($isUnavailable)
                                    ناموجود
                                @elseif($optionStock <= 3)
                                    فقط {{ number_format($optionStock) }} عدد
                                @else
                                    موجود
                                @endif
                            </span>

                        @endif

                    </span>

                @endif


                {{-- Unavailable Overlay --}}
                @if($isUnavailable)

                    <span class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <span class="h-px w-[110%] rotate-[-35deg] bg-red-400/70"></span>
                    </span>

                @endif

            </label>

        @endforeach

    </div>


    {{-- Empty State --}}
    @if($options->isEmpty())

        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-4 py-3">

            <p class="text-xs leading-6 text-[var(--livora-stone)]">
                در حال حاضر گزینه‌ای برای {{ $title }} ثبت نشده است.
            </p>

        </div>

    @endif


    {{-- Selected Hint --}}
    <div
        x-show="selected !== null"
        x-cloak
        class="flex items-center gap-2 text-[11px] text-[var(--livora-stone)]"
    >

        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[var(--livora-ink)] text-[9px] text-white">
            ✓
        </span>

        <span>
            انتخاب شما ثبت شده است.
        </span>

    </div>

</div>
