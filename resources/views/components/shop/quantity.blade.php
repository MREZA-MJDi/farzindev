@props([
'name' => 'quantity',
'value' => 1,
'min' => 1,
'max' => null,
])

@php
    $initialValue = max(
        (int) $min,
        (int) $value
    );

    if ($max !== null) {
        $initialValue = min(
            $initialValue,
            (int) $max
        );
    }

    $maxValue = $max !== null
        ? (int) $max
        : null;
@endphp

<div
    x-data="{
        value: {{ $initialValue }},
        min: {{ (int) $min }},
        max: {{ $maxValue !== null ? $maxValue : 'null' }},

        init() {
            this.onVariantAvailabilityChanged = (event) => {
                const nextMax = Number(event.detail?.max);

                if (!Number.isFinite(nextMax) || nextMax < this.min) {
                    return;
                }

                this.max = nextMax;
                this.value = Math.min(this.value, nextMax);
            };

            window.addEventListener(
                'product-variant-availability-changed',
                this.onVariantAvailabilityChanged
            );
        },

        destroy() {
            if (this.onVariantAvailabilityChanged) {
                window.removeEventListener(
                    'product-variant-availability-changed',
                    this.onVariantAvailabilityChanged
                );
            }
        },

        decrease() {
            if (this.value > this.min) {
                this.value--;
            }
        },

        increase() {
            if (this.max === null || this.value < this.max) {
                this.value++;
            }
        }
    }"
    class="inline-flex items-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-1"
    {{ $attributes }}
>

    {{-- Decrease --}}
    <button
        type="button"
        aria-label="کاهش تعداد"
        @click="decrease()"
        :disabled="value <= min"
        class="flex h-9 w-9 items-center justify-center rounded-xl text-[var(--livora-ink)] transition hover:bg-[var(--livora-surface)] disabled:cursor-not-allowed disabled:opacity-30"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.7"
            stroke="currentColor"
            class="h-4 w-4"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M5 12h14"
            />
        </svg>
    </button>

    {{-- Input --}}
    <input
        type="number"
        name="{{ $name }}"
        x-model.number="value"
        min="{{ $min }}"
        @if($max !== null)
        max="{{ $max }}"
        :max="max"
        @endif
        inputmode="numeric"
        autocomplete="off"
        aria-label="تعداد"
        class="w-12 border-0 bg-transparent p-0 text-center text-sm font-semibold text-[var(--livora-ink)] outline-none focus:ring-0"
    >

    {{-- Increase --}}
    <button
        type="button"
        aria-label="افزایش تعداد"
        @click="increase()"
        :disabled="max !== null && value >= max"
        class="flex h-9 w-9 items-center justify-center rounded-xl text-[var(--livora-ink)] transition hover:bg-[var(--livora-surface)] disabled:cursor-not-allowed disabled:opacity-30"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.7"
            stroke="currentColor"
            class="h-4 w-4"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 5v14M5 12h14"
            />
        </svg>
    </button>

</div>
