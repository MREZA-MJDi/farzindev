<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    class="h-full"
>
<head>

    @include(
        'admin.layouts.partials.head'
    )

</head>
<body
    class="min-h-full bg-[var(--admin-bg)] text-[var(--admin-text)] antialiased"
>
<div
    x-data="{
        sidebarOpen: false,
        userMenuOpen: false,
        notificationOpen: false,
        mobileSearchOpen: false
    }"
    class="admin-shell min-h-screen"
>

    {{-- =========================================================
         MOBILE SIDEBAR BACKDROP
    ========================================================== --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-40 bg-black/35 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
    ></div>


    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}
    @include('admin.layouts.partials.sidebar')


    {{-- =========================================================
         MAIN AREA
    ========================================================== --}}
    <div class="flex min-h-screen flex-col lg:mr-[260px]">

        {{-- =====================================================
             NAVBAR
        ====================================================== --}}
        @include('admin.layouts.partials.navbar')


        {{-- =====================================================
             CONTENT
        ====================================================== --}}
        <main
            class="admin-main flex-1 px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8"
        >

            <div
                class="admin-content admin-page-enter"
            >

                {{-- Breadcrumbs --}}
                @includeIf(
                    'admin.layouts.partials.breadcrumbs'
                )


                {{-- Flash Messages --}}
                @includeIf(
                    'admin.layouts.partials.flash-messages'
                )


                {{-- Page Content --}}
                @yield('content')

            </div>

        </main>


        {{-- =====================================================
             FOOTER
        ====================================================== --}}
        @includeIf(
            'admin.layouts.partials.footer'
        )

    </div>


    {{-- =========================================================
         GLOBAL LOADING
    ========================================================== --}}
    <div
        x-data="{ loading: false }"
        x-on:livewire:navigating.window="loading = true"
        x-on:livewire:navigated.window="loading = false"
    >

        <div
            x-show="loading"
            x-cloak
            class="fixed inset-x-0 top-0 z-[200] h-0.5 bg-[var(--admin-accent)]"
        ></div>

    </div>

</div>


{{-- =========================================================
     SCRIPTS
========================================================== --}}
@stack('scripts')

@includeIf(
    'admin.layouts.partials.scripts'
)

</body>
</html>
