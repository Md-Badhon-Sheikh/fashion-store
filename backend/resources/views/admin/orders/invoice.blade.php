{{--
    A4 order invoice — Invoice.dc.html ("A4 order invoice", 210 × 297 mm).
    Data: $order (DemoData::orders() row), $doc (OrderPrintData::document), $store (OrderPrintData::store).
    Print: the toolbar is hidden, the sheet prints on A4 (css/pages/order-print.css).
--}}
@extends('admin.layouts.print')

@use('App\Support\DemoData')

@section('title', 'Invoice '.$order['number'])
@section('back_url', route('admin.orders.show', $order['id']))
@section('sheet_class', 'doc-a4')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/order-print.css') }}">
@endpush

@section('toolbar_actions')
    <x-admin.print.doc-switch :order-id="$order['id']" current="invoice" />
@endsection

@section('content')
    <header class="doc-head">
        <div class="doc-seller">
            <div class="doc-logo" role="img" aria-label="{{ $store['name'] }} logo">{{ $store['initials'] }}</div>
            <div>
                <div class="doc-seller__name">{{ $store['name'] }}</div>
                <div class="doc-muted">{{ $store['address'] }}</div>
                <div class="doc-muted">{{ $store['phone'] }} · {{ $store['email'] }}</div>
                <div class="doc-muted">{{ $store['website'] }} · BIN {{ $store['bin'] }}</div>
            </div>
        </div>
        <div class="doc-head__right">
            <h1 class="doc-title">INVOICE</h1>
            <dl class="doc-meta">
                <dt>Invoice no.</dt><dd class="fw-700">{{ $doc['invoice_no'] }}</dd>
                <dt>Order no.</dt><dd class="fw-700">{{ $order['number'] }}</dd>
                <dt>Invoice date</dt><dd>{{ $doc['invoice_date'] }}</dd>
                <dt>Order placed</dt><dd>{{ $doc['placed'] }}</dd>
            </dl>
        </div>
    </header>

    <div class="doc-parties">
        <section class="doc-box" aria-labelledby="bill-to">
            <h2 id="bill-to" class="doc-label">Bill to</h2>
            <div class="fw-700">{{ $order['customer']['name'] }}</div>
            <div>{{ $order['customer']['phone_masked'] }}</div>
            @if ($doc['email'])<div>{{ $doc['email'] }}</div>@endif
        </section>
        <section class="doc-box" aria-labelledby="ship-to">
            <h2 id="ship-to" class="doc-label">Ship to</h2>
            <div class="fw-700">{{ $order['customer']['name'] }}</div>
            @foreach ($doc['address_lines'] as $line)
                <div>{{ $line }}</div>
            @endforeach
        </section>
        <section class="doc-box doc-box--narrow" aria-labelledby="delivery">
            <h2 id="delivery" class="doc-label">Delivery</h2>
            <div class="fw-700">{{ $order['courier'] }}</div>
            <div class="doc-mono">{{ $order['tracking'] }}</div>
            <div>{{ $order['delivery_zone'] }}</div>
        </section>
    </div>

    <div class="doc-table-wrap">
        <table class="doc-table">
            <caption class="visually-hidden">Items on invoice {{ $doc['invoice_no'] }}</caption>
            <thead>
                <tr>
                    <th scope="col" class="doc-table__n">#</th>
                    <th scope="col">Item &amp; variant</th>
                    <th scope="col">SKU</th>
                    <th scope="col" class="num">Qty</th>
                    <th scope="col" class="num">Unit price</th>
                    <th scope="col" class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order['items'] as $i => $item)
                    <tr>
                        <td class="doc-muted">{{ $i + 1 }}</td>
                        <td><div class="fw-700">{{ $item['name'] }}</div><div class="doc-muted">{{ $item['variant'] }}</div></td>
                        <td class="doc-mono">{{ $item['sku'] }}</td>
                        <td class="num">{{ $item['qty'] }}</td>
                        <td class="num">{{ DemoData::money($item['price']) }}</td>
                        <td class="num fw-700">{{ DemoData::money($item['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="doc-summary">
        <div class="doc-summary__code">
            <div class="doc-label">Order barcode</div>
            <x-admin.print.barcode :value="$order['id']" size="lg" />
        </div>
        <div class="doc-totals">
            <div class="doc-totals__row"><span class="doc-muted">Subtotal ({{ $doc['pieces'] }} {{ $doc['pieces'] === 1 ? 'pc' : 'pcs' }})</span><span>{{ DemoData::money($order['subtotal']) }}</span></div>
            @foreach ($doc['discounts'] as $d)
                <div class="doc-totals__row"><span class="doc-muted">Discount · {{ $d['label'] }}</span><span>{{ DemoData::money(-$d['amount']) }}</span></div>
            @endforeach
            <div class="doc-totals__row"><span class="doc-muted">Delivery charge</span><span>{{ DemoData::money($order['delivery_fee']) }}</span></div>
            <div class="doc-totals__grand"><span>Total</span><span>{{ DemoData::money($order['amount']) }}</span></div>
            <div class="doc-totals__row"><span class="doc-muted">Paid</span><span>{{ DemoData::money($doc['paid']) }}</span></div>
            <div class="doc-totals__due"><span>{{ $doc['balance_label'] }}</span><span>{{ DemoData::money($doc['balance']) }}</span></div>
        </div>
    </div>

    <footer class="doc-foot">
        <div class="doc-policy">
            <x-admin.icon name="refresh" :size="22" class="doc-policy__icon" />
            <div>
                <div class="doc-policy__title">{{ $store['exchange_days'] }}-day exchange with this invoice</div>
                <div>Unworn, unwashed, with tags attached. Size and colour exchange subject to stock.</div>
            </div>
        </div>
        <div class="doc-foot__line">
            <span>Thank you for shopping with {{ $store['name'] }}.</span>
            <span>Computer-generated invoice, no signature required.</span>
        </div>
    </footer>
@endsection
