@props([
'label' => null,
'hint' => null,
'error' => null,
'hintId' => null,
'icon' => null,
'type' => 'text',
])

@php
    $fieldName = $attributes->get('name');
    $resolvedError = $error ?: ($fieldName ? $errors->first($fieldName) : null);

    $inputClasses = implode(' ', [
        'w-full',
        'rounded-2xl',
        'border',
        'bg-[var(--livora-white)]',
        'px-4',
        'py-3.5',
        'text-sm',
        'text-[var(--livora-text)]',
        'outline-none',
        'transition-all',
        'duration-300',
        'placeholder:text-[var(--livora-stone)]',
        'focus:border-[var(--livora-ink)]',
        'focus:ring-2',
        'focus:ring-[var(--livora-ink)]/5',
        'disabled:cursor-not-allowed',
        'disabled:opacity-50',
         $resolvedError
            ? 'border-red-300 focus:border-red-500 focus:ring-red-500/5'
            : 'border-[var(--livora-border)]',
        $icon ? 'pr-11' : '',
    ]);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>

    @if($label)

        <label
            @if($attributes->has('id'))
            for="{{ $attributes->get('id') }}"
            @endif
            class="mb-2 block text-xs font-medium text-[var(--livora-text)]"
        >
            {{ $label }}
        </label>

    @endif

    <div class="relative">

        @if($icon)

            <span
                aria-hidden="true"
                class="pointer-events-none absolute right-4 top-1/2 flex -translate-y-1/2 items-center text-[var(--livora-stone)]"
            >
                {{ $icon }}
            </span>

        @endif

        <input
            type="{{ $type }}"
            {{ $attributes->except(['class', 'type']) }}
            class="{{ $inputClasses }}"
        >

    </div>

    @if($resolvedError)

        <p
            class="mt-2 flex items-start gap-2 text-[11px] leading-6 text-red-600"
            role="alert"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="mt-0.5 h-3.5 w-3.5 shrink-0"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM10.342 3.94 2.91 17.25A1.875 1.875 0 0 0 4.537 20h14.926a1.875 1.875 0 0 0 1.628-2.75L13.658 3.94a1.875 1.875 0 0 0-3.316 0Z"
                />
            </svg>

            <span>
                {{ $resolvedError }}
            </span>
        </p>

    @elseif($hint)

        <p @if($hintId) id="{{ $hintId }}" @endif class="mt-2 text-[11px] leading-6 text-[var(--livora-stone)]">
            {{ $hint }}
        </p>

    @endif

</div>
