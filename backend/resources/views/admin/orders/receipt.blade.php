{{--
    80 mm thermal POS receipt — Invoice.dc.html ("80 mm thermal POS receipt", 72 mm print width).
    Opened after "Complete sale" on the POS (sample sale POS-2291) and from order details.
    Data: $order, $doc (OrderPrintData::document, incl. tenders), $store.
--}}
@extends('admin.layouts.print')

@use('App\Support\Demo\OrderPrintData')

@section('title', 'Receipt '.$order['number'])
@section('back_url', $doc['is_pos'] ? route('admin.pos') : route('admin.orders.show', $order['id']))
@section('sheet_class', 'print-sheet--receipt doc-receipt')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/order-print.css') }}">
@endpush

@section('toolbar_actions')
    <x-admin.print.doc-switch :order-id="$order['id']" current="receipt" />
@endsection

@php
    $amt = fn ($n) => OrderPrintData::receiptAmount($n);
@endphp

@section('content')
    <header class="rc-center">
        <h1 class="rc-brand">{{ $store['name'] }}</h1>
        <div>{{ $store['address'] }}</div>
        <div>Tel {{ $store['phone'] }}</div>
        <div>BIN {{ $store['bin'] }}</div>
    </header>

    <hr class="rc-rule">

    <dl class="rc-rows">
        <div class="rc-row"><dt>Receipt</dt><dd class="fw-700">{{ $order['id'] }}</dd></div>
        <div class="rc-row"><dt>Date</dt><dd>{{ $doc['receipt_date'] }}</dd></div>
        <div class="rc-row"><dt>Counter</dt><dd>{{ $doc['counter'] }}</dd></div>
        <div class="rc-row"><dt>Cashier</dt><dd>{{ $doc['cashier'] }}</dd></div>
        <div class="rc-row"><dt>Customer</dt><dd>{{ $order['customer']['id'] ? $order['customer']['phone_masked'] : 'Walk-in' }}</dd></div>
    </dl>

    <hr class="rc-rule">

    <div class="rc-row fw-700" aria-hidden="true"><span>ITEM</span><span>AMOUNT</span></div>
    <ul class="rc-items" aria-label="Items">
        @foreach ($order['items'] as $item)
            <li>
                <div class="fw-700">{{ $item['name'] }}</div>
                <div>{{ $item['size'] }} / {{ $item['colour'] }}&nbsp;&nbsp;{{ $item['sku'] }}</div>
                <div class="rc-row"><span>{{ $item['qty'] }} x {{ $amt($item['price']) }}</span><span>{{ $amt($item['total']) }}</span></div>
            </li>
        @endforeach
    </ul>

    <hr class="rc-rule">

    <dl class="rc-rows">
        <div class="rc-row"><dt>Items / pcs</dt><dd>{{ $doc['lines'] }} / {{ $doc['pieces'] }}</dd></div>
        <div class="rc-row"><dt>Subtotal</dt><dd>{{ $amt($order['subtotal']) }}</dd></div>
        @foreach ($doc['discounts'] as $d)
            <div class="rc-row"><dt>Discount {{ $d['code'] }}</dt><dd>{{ $amt(-$d['amount']) }}</dd></div>
        @endforeach
        @if ($order['delivery_fee'] > 0)
            <div class="rc-row"><dt>Delivery</dt><dd>{{ $amt($order['delivery_fee']) }}</dd></div>
        @endif
        <div class="rc-row"><dt>VAT (incl.)</dt><dd>0.00</dd></div>
    </dl>

    <div class="rc-total"><span>TOTAL BDT</span><span>{{ $amt($order['amount']) }}</span></div>

    {{-- Tenders. Cash paid is shown as "Cash received" + "Change". --}}
    <div class="rc-rows">
        @foreach ($doc['tenders']['rows'] as [$label, $value, $note])
            @if (! ($label === 'Cash' && $doc['tenders']['received'] !== null))
                <div class="rc-row"><span>{{ $label }}</span><span>{{ $amt($value) }}</span></div>
            @endif
            @if ($note)
                <div class="rc-note">{{ $note }}</div>
            @endif
        @endforeach
        @if ($doc['tenders']['received'] !== null)
            <div class="rc-row"><span>Cash received</span><span>{{ $amt($doc['tenders']['received']) }}</span></div>
            <div class="rc-row fw-800"><span>Change</span><span>{{ $amt($doc['tenders']['change']) }}</span></div>
        @endif
    </div>

    <hr class="rc-rule">

    <x-admin.print.barcode :value="$order['id']" size="md" class="rc-center" />

    <footer class="rc-center rc-thanks">
        <div class="rc-thanks__title">Thank you!</div>
        <div>Exchange within {{ $store['exchange_days'] }} days with this receipt. No cash refund on sale items.</div>
        <div>{{ $store['website'] }}</div>
    </footer>
@endsection
