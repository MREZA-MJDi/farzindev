@props([
'variant' => 'default',
'size' => 'sm',
'dot' => false,
])

@php
    $variants = [
        'default' => [
            'wrapper' => 'border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-text)]',
            'dot' => 'bg-[var(--livora-stone)]',
        ],

        'success' => [
            'wrapper' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
        ],

        'warning' => [
            'wrapper' => 'border-amber-200 bg-amber-50 text-amber-700',
            'dot' => 'bg-amber-500',
        ],

        'danger' => [
            'wrapper' => 'border-red-200 bg-red-50 text-red-700',
            'dot' => 'bg-red-500',
        ],

        'info' => [
            'wrapper' => 'border-sky-200 bg-sky-50 text-sky-700',
            'dot' => 'bg-sky-500',
        ],

        'dark' => [
            'wrapper' => 'border-transparent bg-[var(--livora-ink)] text-white',
            'dot' => 'bg-white/70',
        ],

        'accent' => [
            'wrapper' => 'border-transparent bg-[var(--livora-accent)] text-white',
            'dot' => 'bg-white/75',
        ],

        'installment' => [
            'wrapper' => 'border-transparent bg-[var(--livora-ink)] text-white',
            'dot' => 'bg-white',
        ],
    ];

    $sizes = [
        'xs' => [
            'wrapper' => 'gap-1.5 px-2 py-1 text-[9px]',
            'dot' => 'h-1.5 w-1.5',
        ],

        'sm' => [
            'wrapper' => 'gap-1.5 px-2.5 py-1.5 text-[10px]',
            'dot' => 'h-1.5 w-1.5',
        ],

        'md' => [
            'wrapper' => 'gap-2 px-3 py-1.5 text-xs',
            'dot' => 'h-2 w-2',
        ],
    ];

    $selectedVariant =
        $variants[$variant]
        ?? $variants['default'];

    $selectedSize =
        $sizes[$size]
        ?? $sizes['sm'];
@endphp

<span
    {{ $attributes->merge([
        'class' =>
            'inline-flex w-fit items-center rounded-full border font-medium whitespace-nowrap ' .
            $selectedVariant['wrapper'] . ' ' .
            $selectedSize['wrapper']
    ]) }}
>

    @if($dot)

        <span
            aria-hidden="true"
            class="shrink-0 rounded-full {{ $selectedVariant['dot'] }} {{ $selectedSize['dot'] }}"
        ></span>

    @endif

    {{ $slot }}

</span>
