@props([
    'name' => 'quantity',
    'value' => 1,
    'min' => 1,
    'max' => null,
    'disabled' => false,
])

@php
    $minValue = max(1, (int) $min);
    $maxValue = $max !== null ? max($minValue, min(99, (int) $max)) : null;
    $initialValue = max($minValue, (int) $value);

    if ($maxValue !== null) {
        $initialValue = min($initialValue, $maxValue);
    }
@endphp

<div
    x-data="{
        value: {{ $initialValue }},
        min: {{ $minValue }},
        max: {{ $maxValue !== null ? $maxValue : 'null' }},
        disabled: @js((bool) $disabled),

        init() {
            this.onVariantAvailabilityChanged = (event) => {
                const nextMax = Number(event.detail?.max);

                if (!Number.isFinite(nextMax) || nextMax < this.min) {
                    return;
                }

                this.max = Math.min(99, nextMax);
                this.normalize();
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

        normalize() {
            const parsed = Number(this.value);
            const safeValue = Number.isFinite(parsed) ? Math.floor(parsed) : this.min;
            const upperBound = this.max === null ? 99 : this.max;

            this.value = Math.max(this.min, Math.min(upperBound, safeValue));
        },

        decrease() {
            if (!this.disabled && this.value > this.min) {
                this.value--;
            }
        },

        increase() {
            if (!this.disabled && (this.max === null || this.value < this.max)) {
                this.value++;
            }
        }
    }"
    class="farzin-quantity"
    data-quantity-control
    role="group"
    aria-label="انتخاب تعداد"
    @if($disabled) aria-disabled="true" @endif
    {{ $attributes }}
>
    <button
        type="button"
        aria-label="کاهش تعداد"
        @click="decrease()"
        :disabled="disabled || value <= min"
        class="farzin-quantity__button"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
        </svg>
    </button>

    <input
        type="number"
        name="{{ $name }}"
        x-model.number="value"
        min="{{ $minValue }}"
        @if($maxValue !== null) max="{{ $maxValue }}" @endif
        :max="max === null ? 99 : max"
        :aria-valuemin="min"
        :aria-valuemax="max === null ? 99 : max"
        :disabled="disabled"
        @change="normalize()"
        @blur="normalize()"
        inputmode="numeric"
        autocomplete="off"
        aria-label="تعداد"
        class="farzin-quantity__input"
    >

    <button
        type="button"
        aria-label="افزایش تعداد"
        @click="increase()"
        :disabled="disabled || (max !== null && value >= max)"
        class="farzin-quantity__button"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
        </svg>
    </button>
</div>
