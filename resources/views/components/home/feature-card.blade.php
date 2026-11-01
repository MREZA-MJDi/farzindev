@props([
    'number',
    'title',
    'description',
    'href',
    'action',
    'icon' => 'ruler',
])

<a class="fz-guide-card fz-reveal" href="{{ $href }}">
    <span class="fz-guide-card__top">
        <span class="fz-guide-card__icon" aria-hidden="true">
            @switch($icon)
                @case('layers')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg>
                    @break
                @case('messages')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H7l-4 2 1.4-4A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8 11h8M8 14h5"/></svg>
                    @break
                @default
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m4 20 16-16M7 4h13v13M4 7v13h13"/></svg>
            @endswitch
        </span>
        <span class="fz-guide-card__number">{{ $number }}</span>
    </span>
    <span class="fz-guide-card__title">{{ $title }}</span>
    <span class="fz-guide-card__description">{{ $description }}</span>
    <span class="fz-guide-card__action">{{ $action }} <b aria-hidden="true">←</b></span>
</a>
