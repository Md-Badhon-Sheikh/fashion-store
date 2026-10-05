{{--
    Reports — Reports.dc.html ("Sales — daily / weekly / monthly" report open).
    Alpine: x-data="{ period }" (daily | weekly | monthly). Switching swaps the title, KPI tiles,
    sales-trend chart, size-wise chart and the detailed table. All three variants are rendered on the
    server (App\Support\Demo\ReportsData::periods()) and toggled with x-show.
    Charts use admin.partials.bar-chart: online (series 1, blue) + POS (series 2, orange); size-wise = brand green.
    Phones: the report picker becomes a scrolling pill row; tables scroll horizontally.
--}}
@extends('admin.layouts.app')

@section('title', 'Reports')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/reports.css') }}">
@endpush

@php
    $pluck = fn (string $field) => array_map(fn ($p) => $p['tiles'][$field], $periods);
    $titles = array_map(fn ($p) => $p['title'], $periods);
    $texts = array_map(fn ($p) => $p['text'], $periods);
    $froms = array_map(fn ($p) => $p['from'], $periods);
    $heads = array_map(fn ($p) => $p['head'], $periods);
    $units = array_map(fn ($p) => $p['unit'], $periods);
    $counts = array_map(fn ($p) => count($p['rows']), $periods);
    $d = $periods[$defaultPeriod];
    $js = fn ($v) => \Illuminate\Support\Js::from($v)->toHtml();
@endphp

@section('content')
<div class="rep" x-data="{ period: @js($defaultPeriod) }">

    <x-admin.page-header title="Reports" subtitle="Sales, stock, finance and staff reports for the online store and POS">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 8l6 8M15 8l-6 8"/></svg>Excel
            </button>
            <button type="button" class="btn btn--outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#991B1B" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5"/><path d="M9 14h6M9 17h4"/></svg>PDF
            </button>
            <button type="button" class="btn btn--outline" onclick="window.print()">
                <x-admin.icon name="print" :size="18" />Print
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row-wrap rep-layout">
        {{-- Report picker --}}
        <nav class="rep-nav" aria-label="Report types">
            @foreach ($reportGroups as $group => $items)
                <div class="rep-nav__group">
                    <div class="rep-nav__label">{{ $group }}</div>
                    @foreach ($items as $name)
                        <a href="#report" class="rep-nav__link" @if ($name === $currentReport) aria-current="page" @endif>{{ $name }}</a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div id="report" class="rep-main">
            {{-- Title, grouping and filters --}}
            <section class="card rep-head" aria-labelledby="rep-title">
                <div class="split">
                    <div>
                        <h2 id="rep-title" class="rep-title" x-text="{{ $js($titles) }}[period]">{{ $d['title'] }}</h2>
                        <div class="fs-13 muted"><span x-text="{{ $js($texts) }}[period]">{{ $d['text'] }}</span> · generated {{ $generatedAt }}</div>
                    </div>
                    <div class="rep-seg" role="group" aria-label="Group by">
                        @foreach ($periodOptions as $key => $label)
                            <button type="button" class="rep-seg__btn" aria-pressed="{{ $key === $defaultPeriod ? 'true' : 'false' }}"
                                :aria-pressed="(period === '{{ $key }}').toString()" @click="period = '{{ $key }}'">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <form class="rep-filters" @submit.prevent>
                    <label class="field">From<input type="date" class="input input--sm" value="{{ $d['from'] }}" :value="{{ $js($froms) }}[period]"></label>
                    <label class="field">To<input type="date" class="input input--sm" value="2026-09-30"></label>
                    <label class="field">Channel
                        <select class="select select--sm"><option>Online + POS</option><option>Online store</option><option>POS (shop)</option></select>
                    </label>
                    <label class="field">Category
                        <select class="select select--sm"><option>All categories</option><option>Men › Panjabi</option><option>Men › Shirts</option><option>Women › Kurti</option><option>Women › Three-Piece</option><option>Kids</option></select>
                    </label>
                    <label class="field">Staff (POS)
                        <select class="select select--sm"><option>All staff</option><option>Sales Staff 01</option><option>Sales Staff 02</option><option>Store Manager</option></select>
                    </label>
                    <button type="submit" class="btn btn--brand btn--sm rep-apply">Apply filters</button>
                </form>
            </section>

            {{-- KPI tiles --}}
            <div class="kpi-grid rep-tiles" aria-live="polite">
                <x-admin.kpi label="Gross sales" :value="$pluck('gross')" :sub="$pluck('gross_sub')" range-var="period" />
                <x-admin.kpi label="Orders / POS sales" :value="$pluck('orders')" :sub="$pluck('orders_sub')" range-var="period" />
                <x-admin.kpi label="Average order value" :value="$pluck('aov')" sub="Across both channels" range-var="period" />
                <x-admin.kpi label="Discounts given" :value="$pluck('disc')" sub="Coupons + flash sale" range-var="period" />
                <x-admin.kpi label="Returns" :value="$pluck('ret')" :sub="$pluck('ret_sub')" range-var="period" />
                <x-admin.kpi label="Net sales" :value="$pluck('net')" sub="After discounts and returns" range-var="period" />
            </div>

            <div class="row-wrap rep-charts">
                {{-- Sales trend --}}
                <section class="card rep-trend" aria-labelledby="rep-trend-title">
                    <div class="card__head">
                        <div>
                            <h3 id="rep-trend-title" class="rep-h3">Sales trend</h3>
                            <div class="card__subtitle" x-text="{{ $js($units) }}[period]">{{ $d['unit'] }}</div>
                        </div>
                        <div class="legend">
                            <span class="legend__item"><span class="legend__swatch legend__swatch--online"></span>Online store</span>
                            <span class="legend__item"><span class="legend__swatch legend__swatch--pos"></span>POS (shop)</span>
                        </div>
                    </div>
                    @foreach ($periods as $key => $p)
                        <div x-show="period === '{{ $key }}'" @if ($key !== $defaultPeriod) x-cloak @endif>
                            @include('admin.partials.bar-chart', [
                                'bars' => $p['chart']['bars'], 'max' => $p['chart']['max'], 'ticks' => $p['chart']['ticks'],
                                'height' => 210, 'minWidth' => 460, 'barMax' => 28, 'gap' => 8,
                                'label' => $p['unit'].', '.$p['text'],
                            ])
                        </div>
                    @endforeach
                    <a href="#report-table" class="rep-table-link">View as table</a>
                </section>

                {{-- Size-wise sales --}}
                <section class="card rep-sizes" aria-labelledby="rep-sizes-title">
                    <div class="card__head">
                        <div>
                            <h3 id="rep-sizes-title" class="rep-h3">Size-wise sales</h3>
                            <div class="card__subtitle">Units sold, all categories with sizes</div>
                        </div>
                    </div>
                    @foreach ($periods as $key => $p)
                        <div x-show="period === '{{ $key }}'" @if ($key !== $defaultPeriod) x-cloak @endif>
                            <div class="rep-size-chart">
                                @include('admin.partials.bar-chart', [
                                    'bars' => $p['sizes']['bars'], 'max' => $p['sizes']['max'], 'ticks' => $p['sizes']['ticks'],
                                    'height' => 180, 'barMax' => 34, 'gap' => 14,
                                    'label' => 'Units sold by size, '.$p['text'],
                                ])
                            </div>
                            <details class="rep-details">
                                <summary>View as table</summary>
                                <table class="table table--compact">
                                    <caption class="visually-hidden">Units sold by size</caption>
                                    <thead><tr><th scope="col">Size</th><th scope="col" class="num">Units</th><th scope="col" class="num">Share</th></tr></thead>
                                    <tbody>
                                        @foreach ($p['sizes']['rows'] as $s)
                                            <tr><td class="fw-600">{{ $s['size'] }}</td><td class="num">{{ $s['units'] }}</td><td class="num">{{ $s['share'] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </details>
                        </div>
                    @endforeach
                </section>
            </div>

            {{-- Detailed breakdown --}}
            <section id="report-table" class="card rep-anchor" aria-labelledby="rep-table-title">
                <div class="card__head">
                    <h3 id="rep-table-title" class="rep-h3">Detailed breakdown</h3>
                    <span class="fs-13 muted"><span x-text="{{ $js($counts) }}[period]">{{ count($d['rows']) }}</span> rows · amounts in ৳</span>
                </div>
                <div class="table-wrap">
                    <table class="table rep-table" style="--table-min: 900px">
                        <thead>
                            <tr>
                                <th scope="col" x-text="{{ $js($heads) }}[period]">{{ $d['head'] }}</th>
                                <th scope="col" class="num">Online orders</th>
                                <th scope="col" class="num">POS sales</th>
                                <th scope="col" class="num">Online ৳</th>
                                <th scope="col" class="num">POS ৳</th>
                                <th scope="col" class="num">Gross sales</th>
                                <th scope="col" class="num">Discounts</th>
                                <th scope="col" class="num">Returns</th>
                                <th scope="col" class="num">Net sales</th>
                            </tr>
                        </thead>
                        @foreach ($periods as $key => $p)
                            <tbody x-show="period === '{{ $key }}'" @if ($key !== $defaultPeriod) x-cloak @endif>
                                @foreach ($p['rows'] as $r)
                                    <tr>
                                        <th scope="row" class="rep-period">{{ $r['period'] }}</th>
                                        <td class="num">{{ $r['on_orders'] }}</td>
                                        <td class="num">{{ $r['pos_orders'] }}</td>
                                        <td class="num">{{ $r['online'] }}</td>
                                        <td class="num">{{ $r['pos'] }}</td>
                                        <td class="num fw-600">{{ $r['gross'] }}</td>
                                        <td class="num muted">{{ $r['disc'] }}</td>
                                        <td class="num muted">{{ $r['ret'] }}</td>
                                        <td class="num fw-700">{{ $r['net'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="rep-total">
                                    <th scope="row">Total</th>
                                    <td class="num">{{ $p['total']['on_orders'] }}</td>
                                    <td class="num">{{ $p['total']['pos_orders'] }}</td>
                                    <td class="num">{{ $p['total']['online'] }}</td>
                                    <td class="num">{{ $p['total']['pos'] }}</td>
                                    <td class="num">{{ $p['total']['gross'] }}</td>
                                    <td class="num">{{ $p['total']['disc'] }}</td>
                                    <td class="num">{{ $p['total']['ret'] }}</td>
                                    <td class="num">{{ $p['total']['net'] }}</td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
                <p class="fs-12 muted rep-foot">Net sales = gross sales − coupon and flash-sale discounts − returns. Cancelled orders are excluded. Delivery charges are not counted as sales.</p>
            </section>
        </div>
    </div>
</div>
@endsection
