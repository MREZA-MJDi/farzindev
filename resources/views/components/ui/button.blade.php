@props([
'variant' => 'primary',
'size' => 'md',
'type' => 'button',
'href' => null,
'disabled' => false,
'loading' => false,
])

@php

    /*
    |--------------------------------------------------------------------------
    | Button Variants
    |--------------------------------------------------------------------------
    */

    $variants = [

        'primary' => [
            'base' => '
                !bg-[var(--livora-ink)]
                !text-white
                !border-[var(--livora-ink)]
                hover:!bg-[var(--livora-accent)]
                hover:!text-white
                hover:!border-[var(--livora-accent)]
            ',
            'hover' => 'hover:-translate-y-0.5 hover:shadow-md',
        ],

        'secondary' => [
            'base' => '
                !bg-[var(--livora-white)]
                !text-[var(--livora-text)]
                !border-[var(--livora-border)]
                hover:!bg-[var(--livora-surface)]
                hover:!text-[var(--livora-text)]
                hover:!border-[var(--livora-ink)]
            ',
            'hover' => 'hover:-translate-y-0.5 hover:shadow-sm',
        ],

        'ghost' => [
            'base' => '
                !bg-transparent
                !text-[var(--livora-text)]
                !border-transparent
                hover:!bg-[var(--livora-surface)]
                hover:!text-[var(--livora-text)]
            ',
            'hover' => 'hover:-translate-y-0.5',
        ],

        'dark' => [
            'base' => '
                !bg-black
                !text-white
                !border-black
                hover:!bg-[var(--livora-accent)]
                hover:!text-white
                hover:!border-[var(--livora-accent)]
            ',
            'hover' => 'hover:-translate-y-0.5 hover:shadow-md',
        ],

        'danger' => [
            'base' => '
                !bg-red-600
                !text-white
                !border-red-600
                hover:!bg-red-700
                hover:!text-white
                hover:!border-red-700
            ',
            'hover' => 'hover:-translate-y-0.5 hover:shadow-md',
        ],

        'success' => [
            'base' => '
                !bg-emerald-600
                !text-white
                !border-emerald-600
                hover:!bg-emerald-700
                hover:!text-white
                hover:!border-emerald-700
            ',
            'hover' => 'hover:-translate-y-0.5 hover:shadow-md',
        ],

        'link' => [
            'base' => '
                !border-transparent
                !bg-transparent
                !text-[var(--livora-accent)]
                hover:!text-[var(--livora-text)]
            ',
            'hover' => 'hover:underline underline-offset-4',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | Button Sizes
    |--------------------------------------------------------------------------
    */

    $sizes = [

        'xs' => [
            'base' => 'min-h-9 rounded-xl px-3 text-[11px]',
            'icon' => 'h-4 w-4',
        ],

        'sm' => [
            'base' => 'min-h-10 rounded-xl px-4 text-xs',
            'icon' => 'h-4 w-4',
        ],

        'md' => [
            'base' => 'min-h-12 rounded-2xl px-5 text-sm',
            'icon' => 'h-4 w-4',
        ],

        'lg' => [
            'base' => 'min-h-14 rounded-2xl px-7 text-sm',
            'icon' => 'h-5 w-5',
        ],

        'xl' => [
            'base' => 'min-h-16 rounded-3xl px-8 text-base',
            'icon' => 'h-5 w-5',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | Resolve Variant / Size
    |--------------------------------------------------------------------------
    */

    $selectedVariant = $variants[$variant] ?? $variants['primary'];

    $selectedSize = $sizes[$size] ?? $sizes['md'];


    /*
    |--------------------------------------------------------------------------
    | Base Classes
    |--------------------------------------------------------------------------
    */

    $classes = implode(' ', [

        // Layout
        'inline-flex',
        'w-fit',
        'shrink-0',
        'items-center',
        'justify-center',
        'gap-2',

        // Typography
        'font-medium',
        'leading-5',
        'whitespace-nowrap',
        'max-w-full',
        'overflow-visible',
        'select-none',

        // Border
        'border',

        // Motion
        'transition-all',
        'duration-300',
        'ease-out',
        'transform-gpu',
        'motion-reduce:transition-none',
        'motion-reduce:transform-none',

        // Interaction
        'focus:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-[var(--livora-accent)]',
        'focus-visible:ring-offset-2',

        // Active
        'active:translate-y-0',
        'active:scale-[0.98]',

        // Disabled
        'disabled:cursor-not-allowed',
        'disabled:pointer-events-none',
        'disabled:opacity-50',

        // Variant
        $selectedVariant['base'],
        $selectedVariant['hover'],

        // Size
        $selectedSize['base'],
    ]);

@endphp


@if($href && ! $disabled)

    <a
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => $classes,
        ]) }}
    >

        @if($loading)

            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
                class="{{ $selectedSize['icon'] }} shrink-0 animate-spin"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3a9 9 0 1 0 9 9"
                />
            </svg>

        @endif


        <span class="inline-flex max-w-full items-center justify-center text-center">
            {{ $slot }}
        </span>

    </a>

@else

    <button
        type="{{ $type }}"
        @disabled($disabled || $loading)
        @if($loading)
        aria-busy="true"
        @endif
        {{ $attributes->merge([
            'class' => $classes,
        ]) }}
    >

        @if($loading)

            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
                class="{{ $selectedSize['icon'] }} shrink-0 animate-spin"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3a9 9 0 1 0 9 9"
                />
            </svg>

        @endif


        <span class="inline-flex items-center justify-center">
            {{ $slot }}
        </span>

    </button>

@endif
