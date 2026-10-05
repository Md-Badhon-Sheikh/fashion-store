{{--
    Toolbar links between the three printable documents of one order (print layout toolbar).
    <x-admin.print.doc-switch :order-id="$order['id']" current="invoice" />
    current: invoice | receipt | packing-slip
--}}
@props(['orderId', 'current' => 'invoice'])
@php
    $docs = [
        'invoice' => ['A4 invoice', 'admin.orders.invoice'],
        'receipt' => ['POS receipt', 'admin.orders.receipt'],
        'packing-slip' => ['Packing slip', 'admin.orders.packing-slip'],
    ];
@endphp
<nav class="doc-switch" aria-label="Print document">
    @foreach ($docs as $key => [$label, $route])
        <a href="{{ route($route, $orderId) }}" @class(['doc-switch__link', 'is-active' => $key === $current])
            @if ($key === $current) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
