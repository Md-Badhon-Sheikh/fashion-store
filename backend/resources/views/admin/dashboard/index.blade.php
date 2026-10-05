{{--
    Dashboard — Dashboard.dc.html (desktop) + M-AdminDashboard.dc.html (phone, < 640px).
    Desktop-only blocks use .hide-sm, phone-only blocks use .show-sm.
    Alpine state: range (today|d7|d30) swaps range-aware KPI tiles; customOpen; showTable.
--}}
@extends('admin.layouts.app')

@section('title', 'Dashboard')

@use('App\Support\DemoData')
@use('App\Support\Status')

@php
    $k =fn ($v) => rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');

    // 14-day chart (desktop): label every second day, value label on the last bar.
    $bars14 = [];
    foreach ($sales14 as $i => $d) {
        $last = $i === array_key_last($sales14);
        $bars14[] = [
            'x' => $i % 2 === 1 ? $d['short'] : '',
            'tip' => $d['short'].' · Online ৳'.$k($d['online']).'k · POS ৳'.$k($d['pos']).'k',
            'value' => $last ? '৳'.number_format($d['total'], 1).'k' : '',
            'segments' => [['series' => 'online', 'value' => $d['online']], ['series' => 'pos', 'value' => $d['pos']]],
        ];
    }

    // 7-day chart (phone): every day labelled, "Today" bold.
    $bars7 = [];
    foreach ($sales7 as $i => $d) {
        $last = $i === array_key_last($sales7);
        $bars7[] = [
            'x' => $last ? 'Today' : ($d['day'] === '1' ? '1 Oct' : $d['day']),
            'current' => $last,
            'tip' => $d['short'].' · Online ৳'.$k($d['online']).'k · POS ৳'.$k($d['pos']).'k',
            'value' => $last ? '৳'.number_format($d['total'], 1).'k' : '',
            'segments' => [['series' => 'online', 'value' => $d['online']], ['series' => 'pos', 'value' => $d['pos']]],
        ];
    }

    $ticks = ['60k', '40k', '20k', '0'];
    $showClass = fn (string $show) => match ($show) { 'desktop' => 'hide-sm', 'mobile' => 'show-sm', default => '' };
@endphp

@section('content')
<div class="dash" x-data="{ range: 'today', customOpen: false, showTable: false }">

    {{-- Header: title, date, range control, export --}}
    <x-admin.page-header title="Dashboard">
        <div class="page-header__subtitle"><span class="hide-sm">Saturday, </span>{{ $todayShort }} · Online store + POS</div>
        <x-slot:actions>
            <div class="dash-range">
                <div class="segmented" role="group" aria-label="Date range">
                    @foreach ($ranges as $key => $label)
                        <button type="button" class="segmented__btn"
                            aria-pressed="{{ $key === 'today' ? 'true' : 'false' }}"
                            :aria-pressed="(range === '{{ $key }}' && !customOpen).toString()"
                            @click="range = '{{ $key }}'; customOpen = false">
                            @if ($key === 'today')
                                {{ $label }}
                            @else
                                <span class="hide-sm">{{ $label }}</span><span class="show-sm">{{ str_replace(' days', 'd', $label) }}</span>
                            @endif
                        </button>
                    @endforeach
                    <button type="button" class="segmented__btn hide-sm" aria-pressed="false"
                        :aria-pressed="customOpen.toString()" @click="customOpen = !customOpen" aria-controls="dash-custom-range">Custom</button>
                </div>
                <button type="button" class="btn btn--outline hide-sm">
                    <x-admin.icon name="download" :size="18" />Export
                </button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="dash-custom-range" class="card dash-custom" x-show="customOpen" x-cloak @submit.prevent="customOpen = false">
        <label class="field">From
            <input class="input" type="date" name="from" value="2026-09-21">
        </label>
        <label class="field">To
            <input class="input" type="date" name="to" value="2026-10-04">
        </label>
        <button type="submit" class="btn btn--primary btn--sm">Apply</button>
        <button type="button" class="btn btn--ghost btn--sm" @click="customOpen = false">Cancel</button>
    </form>

    {{-- KPI tiles: 6 on desktop (auto-fill), 6 different ones 2-up on phone --}}
    <div class="kpi-grid">
        @foreach ($kpis as $kpi)
            <x-admin.kpi :label="$kpi['label']" :value="$kpi['value']" :sub="$kpi['sub']" :tone="$kpi['tone']" class="{{ $showClass($kpi['show']) }}" />
        @endforeach
    </div>

    {{-- Sales chart + orders by status --}}
    <div class="row-wrap">
        <x-admin.card class="col-main hide-sm" title="Sales, last 14 days" subtitle="Daily revenue by channel, ৳ thousand">
            <x-slot:action>
                <div class="legend">
                    <span class="legend__item"><span class="legend__swatch legend__swatch--online"></span>Online store</span>
                    <span class="legend__item"><span class="legend__swatch legend__swatch--pos"></span>POS (shop)</span>
                </div>
            </x-slot:action>
            @include('admin.partials.bar-chart', [
                'bars' => $bars14, 'max' => 60, 'ticks' => $ticks, 'height' => 210, 'minWidth' => 520,
                'label' => 'Daily revenue by channel for the last 14 days, in thousand Taka',
            ])
            <button type="button" class="link-btn fs-13 card__foot" @click="showTable = !showTable"
                :aria-expanded="showTable.toString()" aria-expanded="false" aria-controls="sales14-table"
                x-text="showTable ? 'Hide table' : 'View as table'">View as table</button>
            <div id="sales14-table" class="table-wrap dash-chart-table" x-show="showTable" x-cloak>
                <table class="table table--compact">
                    <caption class="visually-hidden">Daily revenue by channel, ৳ thousand</caption>
                    <thead><tr><th scope="col">Day</th><th scope="col" class="num">Online</th><th scope="col" class="num">POS</th><th scope="col" class="num">Total</th></tr></thead>
                    <tbody>
                        @foreach ($sales14 as $d)
                            <tr><td>{{ $d['short'] }}</td><td class="num">৳{{ $k($d['online']) }}k</td><td class="num">৳{{ $k($d['pos']) }}k</td><td class="num fw-700">৳{{ $k($d['total']) }}k</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        {{-- Phone: 7-day chart --}}
        <section class="card show-sm w-full" aria-labelledby="sales7-title">
            <div class="split split--baseline" style="--gap: 8px">
                <h2 id="sales7-title" class="card__title">Sales, last 7 days</h2>
                <span class="fs-12 muted">৳ thousand</span>
            </div>
            <div class="legend dash-legend-sm">
                <span class="legend__item"><span class="legend__swatch legend__swatch--online"></span>Online {{ DemoData::money($sales7Totals['online']) }}</span>
                <span class="legend__item"><span class="legend__swatch legend__swatch--pos"></span>POS {{ DemoData::money($sales7Totals['pos']) }}</span>
            </div>
            @include('admin.partials.bar-chart', [
                'bars' => $bars7, 'max' => 60, 'ticks' => $ticks, 'height' => 156, 'barMax' => 26, 'gap' => 10,
                'label' => 'Daily revenue by channel for the last 7 days, in thousand Taka',
            ])
            <button type="button" class="link-btn fs-13 dash-table-link" @click="showTable = !showTable"
                :aria-expanded="showTable.toString()" aria-expanded="false" aria-controls="sales7-table"
                x-text="showTable ? 'Hide table' : 'View as table'">View as table</button>
            <div id="sales7-table" class="table-wrap" x-show="showTable" x-cloak>
                <table class="table table--compact">
                    <caption class="visually-hidden">Daily revenue by channel, last 7 days, ৳ thousand</caption>
                    <thead><tr><th scope="col">Day</th><th scope="col" class="num">Online</th><th scope="col" class="num">POS</th><th scope="col" class="num">Total</th></tr></thead>
                    <tbody>
                        @foreach ($sales7 as $d)
                            <tr><td>{{ $d['short'] }}</td><td class="num">৳{{ $k($d['online']) }}k</td><td class="num">৳{{ $k($d['pos']) }}k</td><td class="num fw-700">৳{{ $k($d['total']) }}k</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <x-admin.card class="col-side card--stack hide-sm" title="Orders by status" :link="route('admin.orders.index')" link-label="All orders →">
            <div class="list-rows">
                @foreach ($statusCounts as $status => $n)
                    <div class="list-row">
                        <x-admin.status-chip :status="$status" />
                        <span class="list-row__value">{{ $n }}</span>
                    </div>
                @endforeach
            </div>
        </x-admin.card>

        {{-- Phone: status pills row --}}
        <section class="show-sm w-full stack stack--sm" aria-labelledby="status-title-sm">
            <div class="split split--baseline">
                <h2 id="status-title-sm" class="card__title">Orders by status</h2>
                <a class="card__link" href="{{ route('admin.orders.index') }}">All orders</a>
            </div>
            <div class="scroll-row">
                @foreach ($statusCounts as $status => $n)
                    <a class="status-pill chip--{{ Status::tone($status) }}" href="{{ route('admin.orders.index', ['status' => $status]) }}">
                        {{ Status::label($status) }}<span class="status-pill__count">{{ $n }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Recent orders + stock alerts --}}
    <div class="row-wrap">
        <x-admin.card class="col-main hide-sm" title="Recent sales & orders" :link="route('admin.orders.index')" link-label="View all →">
            <div class="table-wrap">
                <table class="table" style="--table-min: 620px">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Source</th>
                            <th scope="col">Payment</th>
                            <th scope="col" class="num">Amount</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $o)
                            <tr>
                                <td>
                                    <a class="fw-700" href="{{ route('admin.orders.show', $o['id']) }}">{{ $o['number'] }}</a>
                                    <span class="cell-sub">{{ $o['time'] }}</span>
                                </td>
                                <td>{{ $o['customer_label'] }}</td>
                                <td>{{ $o['source'] }}</td>
                                <td>{{ $o['payment_method'] }}</td>
                                <td class="num cell-strong">{{ DemoData::money($o['amount']) }}</td>
                                <td><x-admin.status-chip :status="$o['display_status']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card class="col-side" title="Stock alerts" :link="route('admin.stock-in')" link-label="Stock-in →">
            <div class="list-rows">
                @foreach ($alerts as $i => $a)
                    <div @class(['list-row', 'hide-sm' => $i >= 5])>
                        <div class="list-row__main">
                            <div class="list-row__title">{{ $a['name'] }}</div>
                            <div class="list-row__meta">{{ $a['variant'] }} · <span class="mono">{{ $a['sku'] }}</span></div>
                        </div>
                        <x-admin.status-chip :status="$a['status']" :label="$a['label']" />
                    </div>
                @endforeach
            </div>
            <a class="btn btn--ghost btn--block show-sm" href="{{ route('admin.inventory') }}">View all {{ $alertTotal }} alerts</a>
        </x-admin.card>

        {{-- Phone: recent orders as cards --}}
        <section class="show-sm w-full stack stack--sm" aria-labelledby="recent-title-sm">
            <div class="split split--baseline">
                <h2 id="recent-title-sm" class="card__title">Recent sales &amp; orders</h2>
                <a class="card__link" href="{{ route('admin.orders.index') }}">View all</a>
            </div>
            @foreach (array_slice($recentOrders, 0, 5) as $o)
                <a class="list-card" href="{{ route('admin.orders.show', $o['id']) }}">
                    <span class="list-card__row">
                        <span class="list-card__id">{{ $o['number'] }}</span>
                        <x-admin.status-chip :status="$o['display_status']" />
                    </span>
                    <span class="list-card__meta">{{ $o['time'] }} · {{ $o['source'] }} · {{ $o['customer_label'] }}</span>
                    <span class="list-card__foot">
                        <span class="fs-13 text-2">{{ $o['payment_method'] }} · {{ $o['items_count'] }} {{ $o['items_count'] === 1 ? 'item' : 'items' }}</span>
                        <span class="list-card__amount">{{ DemoData::money($o['amount']) }}</span>
                    </span>
                </a>
            @endforeach
        </section>
    </div>

    {{-- Top products --}}
    <x-admin.card title="Top selling products, this month" :link="route('admin.reports')" link-label="Product-wise report →">
        <div class="hbars">
            @foreach ($topProducts as $t)
                <div class="hbar">
                    <span class="hbar__label">{{ $t['name'] }}</span>
                    <div class="hbar__track" role="presentation"><div class="hbar__fill" style="width: {{ $t['pct'] }}%"></div></div>
                    <span class="hbar__value">{{ $t['units'] }} pcs</span>
                </div>
            @endforeach
        </div>
    </x-admin.card>

    {{-- Phone: floating Open POS button --}}
    <a class="fab" href="{{ route('admin.pos') }}">
        <x-admin.icon name="barcode" :size="22" />Open POS
    </a>
</div>
@endsection
