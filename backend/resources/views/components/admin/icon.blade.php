{{--
    Inline stroke icon (currentColor). Usage: <x-admin.icon name="search" :size="18" />
    Decorative by default (aria-hidden). Pass label="..." to expose it to screen readers.
    Add new icons to the $paths map (24×24 viewBox, stroke paths).
--}}
@props(['name', 'size' => 20, 'stroke' => 1.8, 'label' => null])
@php
    $paths = [
        // Shell
        'menu' => 'M4 7h16M4 12h16M4 17h16',
        'close' => 'M6 6l12 12M18 6L6 18',
        'search' => 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM20 20l-3.5-3.5',
        'barcode' => 'M4 5v14M7 5v14M10 5v14M14 5v14M16 5v14M20 5v14',
        'bell' => 'M6 16V11a6 6 0 0 1 12 0v5l2 2H4l2-2zM10 20a2 2 0 0 0 4 0',
        'chevron-right' => 'M9 6l6 6-6 6',
        'chevron-left' => 'M15 6l-6 6 6 6',
        'chevron-down' => 'M6 9l6 6 6-6',
        'chevron-up' => 'M6 15l6-6 6 6',
        'arrow-right' => 'M5 12h14M13 6l6 6-6 6',
        'external' => 'M14 4h6v6M20 4l-9 9M18 14v6H4V6h6',
        'logout' => 'M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11',
        // Actions
        'plus' => 'M12 5v14M5 12h14',
        'minus' => 'M5 12h14',
        'check' => 'M5 12l5 5 9-10',
        'download' => 'M12 4v11M7 10l5 5 5-5M5 20h14',
        'upload' => 'M12 20V9M7 14l5-5 5 5M5 4h14',
        'print' => 'M7 9V3h10v6M3 9h18v8H3zM7 14h10v7H7z',
        'edit' => 'M4 20h4L19 9l-4-4L4 16v4zM13 7l4 4',
        'trash' => 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
        'copy' => 'M9 9h11v11H9zM5 15H4V4h11v1',
        'filter' => 'M4 6h16M7 12h10M10 18h4',
        'sort' => 'M7 4v16M3 16l4 4 4-4M17 20V4M13 8l4-4 4 4',
        'refresh' => 'M20 11a8 8 0 1 0-2.3 5.7M20 4v7h-7',
        'eye' => 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
        'lock' => 'M4 10h16v11H4zM8 10V7a4 4 0 0 1 8 0v3',
        'calendar' => 'M4 5h16v16H4zM4 10h16M8 3v4M16 3v4',
        'phone' => 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z',
        'cart' => 'M3 4h2l2.4 11h11L21 7H6.2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2z',
        'info' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 11v5M12 8h.01',
        'alert' => 'M12 3l10 18H2zM12 10v4M12 17h.01',
        'user' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21c0-4 3.5-6 8-6s8 2 8 6',
        'image-add' => 'M3 4h18v16H3zM9 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM21 17l-5-5-9 8',
        'grip' => 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01',
        // Navigation (from M-AdminMenu)
        'dashboard' => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
        'pos' => 'M3 4h18v12H3zM8 20h8M12 16v4M7 9h4',
        'orders' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
        'customers' => 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM2 21c0-4 3-6 7-6s7 2 7 6M16 3.5a4 4 0 0 1 0 7.5M18 15c2.5.5 4 2.5 4 6',
        'returns' => 'M9 14L4 9l5-5M4 9h11a5 5 0 0 1 0 10h-3',
        'products' => 'M8 4l-4 3 2 4 2-1v10h8V10l2 1 2-4-4-3c-.5 1.5-2 2.5-4 2.5S8.5 5.5 8 4z',
        'categories' => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM16.5 13v7M13 16.5h7',
        'star' => 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z',
        'stock' => 'M3 7l9-4 9 4v10l-9 4-9-4zM3 7l9 4 9-4M12 11v10',
        'stock-in' => 'M12 3v12M7 10l5 5 5-5M4 19h16',
        'truck' => 'M2 6h12v10H2zM14 10h4l3 3v3h-7M6 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM17 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
        'coupon' => 'M3 6h18v4a2 2 0 0 0 0 4v4H3v-4a2 2 0 0 0 0-4zM14 6v12',
        'flash' => 'M13 2L4 14h7l-1 8 9-12h-7z',
        'image' => 'M3 5h18v14H3zM3 16l5-5 5 5 3-3 5 5',
        'accounting' => 'M4 4h16v16H4zM8 8h8M8 12h3M13 12h3M8 16h3M13 16h3',
        'reports' => 'M4 20V10M10 20V4M16 20v-7M2 20h20',
        'mail' => 'M4 6h16v12H4zM4 6l8 7 8-7',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z',
        'clock' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2',
        'settings' => 'M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M14 4v4M8 10v4M16 16v4',
    ];
    $d = $paths[$name] ?? null; // 'more' (three dots) is drawn with filled circles below
@endphp
<svg {{ $attributes->merge(['class' => 'icon icon--'.$name]) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
    @if ($name === 'more') fill="currentColor" @else fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" @endif
    @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" focusable="false" @endif>
    @if ($name === 'more')
        <circle cx="5" cy="12" r="1.8"></circle><circle cx="12" cy="12" r="1.8"></circle><circle cx="19" cy="12" r="1.8"></circle>
    @elseif ($d)
        <path d="{{ $d }}"></path>
    @endif
</svg>
