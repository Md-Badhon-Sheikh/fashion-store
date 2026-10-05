{{--
    Packing slip — Invoice.dc.html ("Packing slip", A6 105 × 148 mm, goes inside the parcel).
    Data: $order, $doc (OrderPrintData::document), $store.
    The tick boxes are printed empty for the packer to mark by hand.
--}}
@extends('admin.layouts.print')

@use('App\Support\DemoData')

@section('title', 'Packing slip '.$order['number'])
@section('back_url', route('admin.orders.show', $order['id']))
@section('sheet_class', 'print-sheet--slip doc-slip')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/order-print.css') }}">
@endpush

@section('toolbar_actions')
    <x-admin.print.doc-switch :order-id="$order['id']" current="packing-slip" />
@endsection

@section('content')
    <header class="slip-head">
        <div>
            <h1 class="slip-title">PACKING SLIP</h1>
            <div class="doc-muted">{{ $store['name'] }} · {{ $store['phone'] }}</div>
        </div>
        <x-admin.print.barcode :value="$order['id']" size="sm" class="slip-head__code" />
    </header>

    <div class="slip-ship">
        <section class="slip-ship__to" aria-labelledby="slip-ship-to">
            <h2 id="slip-ship-to" class="doc-label doc-label--sm">Ship to</h2>
            <div class="slip-ship__name">{{ $order['customer']['name'] }}</div>
            <div class="fs-13">{{ $order['customer']['phone_masked'] }}</div>
            <div>{{ $doc['address_inline'] }}</div>
        </section>
        <div class="slip-collect">
            @if ($doc['due'] > 0)
                <div class="doc-label doc-label--sm doc-label--ink">{{ $doc['is_cod'] ? 'COD collect' : 'Collect' }}</div>
                <div class="slip-collect__amount">{{ DemoData::money($doc['due']) }}</div>
            @else
                <div class="doc-label doc-label--sm doc-label--ink">Prepaid</div>
                <div class="slip-collect__amount">{{ DemoData::money(0) }}</div>
                <div class="fs-12">Do not collect</div>
            @endif
        </div>
    </div>

    <div class="slip-meta">
        <span>Order <b>{{ $order['number'] }}</b></span>
        <span>{{ $order['date'] }}</span>
        <span>{{ $order['courier'] }} · <span class="doc-mono">{{ $order['tracking'] }}</span></span>
    </div>

    <table class="slip-table">
        <caption class="visually-hidden">Items to pack for {{ $order['number'] }}</caption>
        <thead>
            <tr>
                <th scope="col" class="slip-table__check"><span class="visually-hidden">Packed</span></th>
                <th scope="col">Item</th>
                <th scope="col">SKU</th>
                <th scope="col" class="num">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order['items'] as $item)
                <tr>
                    <td><span class="slip-box" aria-hidden="true"></span></td>
                    <td><div class="fw-700">{{ $item['name'] }}</div><div class="doc-muted">{{ $item['variant'] }}</div></td>
                    <td class="doc-mono doc-mono--sm">{{ $item['sku'] }}</td>
                    <td class="num slip-table__qty">{{ $item['qty'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="slip-total"><span>Total pieces</span><span>{{ $doc['pieces'] }}</span></div>

    @if ($order['note'])
        <div class="slip-note">Note: {{ $order['note']['text'] }}</div>
    @endif

    <div class="slip-sign">
        <div>Packed by</div>
        <div>Checked by</div>
    </div>
    <p class="slip-foot">{{ $store['exchange_days'] }}-day exchange with the invoice in this parcel.</p>
@endsection
