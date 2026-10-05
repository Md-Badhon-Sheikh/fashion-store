<!doctype html>
{{--
    Print layout (invoice, POS receipt, packing slip).
    Screen: grey backdrop + sticky toolbar (Back / Print) + white sheet.  Print: sheet only.
    @extends('admin.layouts.print')
    @section('title', 'Invoice #WB-10482')
    @section('back_url', route('admin.orders.show', 'WB-10482'))   (optional, defaults to orders list)
    @section('sheet_class', 'print-sheet--receipt')                 (optional: --receipt | --slip)
    @section('toolbar_actions') extra toolbar buttons @endsection    (optional)
    @section('content') … @endsection
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'YOUR BRAND Admin') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
<body class="print-body">
    <div class="print-toolbar">
        <a class="btn btn--outline btn--sm" href="@hasSection('back_url')@yield('back_url')@else{{ route('admin.orders.index') }}@endif">
            <x-admin.icon name="chevron-left" :size="16" />Back
        </a>
        <div class="cluster">
            @yield('toolbar_actions')
            <button type="button" class="btn btn--brand btn--sm" onclick="window.print()">
                <x-admin.icon name="print" :size="16" />Print
            </button>
        </div>
    </div>
    <main id="main" class="print-sheet @yield('sheet_class')">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
