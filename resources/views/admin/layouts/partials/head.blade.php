{{-- =========================================================
     META
========================================================= --}}

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
/>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

<meta
    name="theme-color"
    content="#1c1b19"
/>


{{-- =========================================================
     SEO
========================================================= --}}

<title>
    @yield('title', 'داشبورد مدیریت') | فرزین
</title>

<meta
    name="description"
    content="@yield(
        'meta_description',
        'پنل مدیریت فروشگاه فرزین'
    )"
/>

<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex" />


{{-- =========================================================
     VITE
========================================================= --}}

@vite([
'resources/css/admin.css',
])


{{-- =========================================================
     PAGE SPECIFIC
========================================================= --}}

@stack('styles')

@stack('head')
