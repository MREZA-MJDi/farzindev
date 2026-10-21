@props([
'name' => 'modal',
'title' => null,
'description' => null,
'size' => 'md',
'closeButton' => true,
'closeOnBackdrop' => true,
])

@php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-6xl',
    ];

    $panelSize = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-data="{
        open: false,
        openModal() {
            this.open = true;
            document.body.classList.add('overflow-hidden');

            this.$nextTick(() => {
                this.$refs.closeButton?.focus();
            });
        },
        closeModal() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    x-on:open-modal.window="
        if ($event.detail === '{{ $name }}') {
            openModal();
        }
    "
    x-on:close-modal.window="
        if ($event.detail === '{{ $name }}') {
            closeModal();
        }
    "
    x-on:keydown.escape.window="if (open) closeModal()"
    {{ $attributes }}
>

    {{ $trigger ?? '' }}

    {{-- Modal --}}
    <template x-teleport="body">

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $name }}-title"
        >

            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-black/45 backdrop-blur-sm"
                @if($closeOnBackdrop)
                @click="closeModal()"
                @endif
            ></div>


            {{-- Position --}}
            <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">

                {{-- Panel --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-y-4 scale-[0.98] opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                    x-transition:leave-end="translate-y-4 scale-[0.98] opacity-0"
                    @click.stop
                    class="relative flex max-h-[90vh] w-full {{ $panelSize }} flex-col overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] shadow-2xl"
                >

                    {{-- Header --}}
                    @if($title || $description || $closeButton)

                        <div class="flex items-start justify-between gap-5 border-b border-[var(--livora-border)] px-5 py-5 sm:px-7 sm:py-6">

                            <div class="min-w-0">

                                @if($title)

                                    <h2
                                        id="{{ $name }}-title"
                                        class="text-lg font-semibold tracking-tight text-[var(--livora-text)]"
                                    >
                                        {{ $title }}
                                    </h2>

                                @endif

                                @if($description)

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        {{ $description }}
                                    </p>

                                @endif

                            </div>

                            @if($closeButton)

                                <button
                                    x-ref="closeButton"
                                    type="button"
                                    aria-label="بستن"
                                    @click="closeModal()"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[var(--livora-stone)] transition hover:bg-[var(--livora-surface)] hover:text-[var(--livora-text)]"
                                >

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="h-5 w-5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12"
                                        />
                                    </svg>

                                </button>

                            @endif

                        </div>

                    @endif


                    {{-- Body --}}
                    <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-7">

                        {{ $slot }}

                    </div>


                    {{-- Optional Footer --}}
                    @isset($footer)

                        <div class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)] px-5 py-4 sm:px-7 sm:py-5">

                            {{ $footer }}

                        </div>

                    @endisset

                </div>

            </div>

        </div>

    </template>

</div>
