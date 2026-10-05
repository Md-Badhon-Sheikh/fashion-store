<!doctype html>
{{--
    Admin shell: sidebar (fixed >= 1024px, off-canvas drawer below) + topbar + content + phone tab bar.
    Page contract:
        @extends('admin.layouts.app')
        @section('title', 'Orders')                 page name (browser tab + phone topbar)
        @section('content') … @endsection
    Optional:
        @section('mobile_actions') … @endsection    replaces bell + avatar in the phone topbar
        @section('content_class', 'admin-content--tight')
        @push('styles') <style>…</style> @endpush    (prefer admin.css page sections)
        @push('scripts') <script>…</script> @endpush
    Drawer state lives on <body x-data="adminShell">: `drawer` (bool), openDrawer(), closeDrawer().
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'YOUR BRAND Admin') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
<body
    x-data="{ drawer: false, navq: '', openDrawer() { this.drawer = true; this.$nextTick(() => this.$refs.drawerClose && this.$refs.drawerClose.focus()); }, closeDrawer() { this.drawer = false; } }"
    :class="{ 'drawer-open': drawer, 'is-locked': drawer }"
    @keydown.escape.window="closeDrawer()"
    @resize.window.debounce.150ms="if (window.innerWidth >= 1024) closeDrawer()">
    <a class="skip-link" href="#main">Skip to content</a>

    <div class="admin-shell">
        @include('admin.partials.sidebar')
        <div class="sidebar-overlay" @click="closeDrawer()" aria-hidden="true"></div>

        <div class="admin-main">
            @include('admin.partials.topbar')

            <main id="main" tabindex="-1" class="admin-content @yield('content_class')">
                @yield('content')
            </main>
        </div>
    </div>

    @include('admin.partials.tabbar')

    @stack('scripts')
</body>
</html>
