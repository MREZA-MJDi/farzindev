<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'فرزین')
    </title>

    <meta
        name="description"
        content="@yield('description', 'فرزین | هود و سینک آشپزخانه')"
    >

    @hasSection('keywords')
        <meta
            name="keywords"
            content="@yield('keywords')"
        >
    @endif

    @hasSection('canonical')
        <link
            rel="canonical"
            href="@yield('canonical')"
        >
    @endif

    @stack('seo')

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    @stack('styles')
    @vite(['resources/css/responsive.css'])
</head>

<body class="@yield('body_class', 'min-h-screen antialiased')">

<div
    id="app"
    x-data="{
        mobileOpen: false,
        searchOpen: false,
        filterOpen: false
    }"
    class="min-h-screen pb-24 lg:pb-0"
>

    <x-layout.navbar />

    <x-layout.mobile-menu />

    <x-layout.search-overlay />

    <x-shop.filter-drawer />

    <main id="main-content" class="storefront-content">
        @yield('content')
    </main>

    <x-layout.footer />

    <x-layout.mobile-bottom-bar />

</div>

@stack('scripts')

</body>
</html>
