{{--
    Suppliers — Suppliers.dc.html.
    KPIs, supplier list (search + "show" filter), ledger panel that switches with the selected
    supplier (Pay / View), and the record-payment form with live "due after payment".
    Phones (< 640px): the wide supplier table becomes cards. Alpine component: `suppliersPage`.
    Data: $kpis, $suppliers (keyed by id, each with ledger), $selected, $refs (method => reference).
--}}
@extends('admin.layouts.app')

@section('title', 'Suppliers')

@use('App\Support\DemoData')

@php
    $cur = $suppliers[$selected];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/suppliers.css') }}">
@endpush

@section('content')
<div class="page-stack page-stack--lg" x-data="suppliersPage">
    <x-admin.page-header title="Suppliers" subtitle="Who you buy from, what you owe them, and every purchase and payment in one ledger">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">Export</button>
            <a class="btn btn--outline" href="{{ route('admin.stock-in') }}">New purchase</a>
            <button type="button" class="btn btn--brand">+ Add supplier</button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="kpi-grid">
        @foreach ($kpis as $k)
            <x-admin.kpi :label="$k['label']" :value="$k['value']" :sub="$k['sub']" :tone="$k['tone']" />
        @endforeach
    </div>

    {{-- Supplier list --}}
    <section class="card card--flush" aria-labelledby="h-list">
        <div class="panel-head">
            <h2 id="h-list" class="card__title">All suppliers</h2>
            <div class="tools">
                <div class="input-group">
                    <x-admin.icon name="search" :size="16" :stroke="2" />
                    <label for="supq" class="visually-hidden">Search suppliers</label>
                    <input id="supq" type="search" placeholder="Name or phone" x-model="q">
                </div>
                <label class="inline-field">Show
                    <select class="select" x-model="show" style="height: 40px; font-size: 14px">
                        <option value="all">All suppliers</option>
                        <option value="due">With due only</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="table-wrap hide-sm">
            <table class="table sup-table" style="--table-min: 1080px">
                <caption class="visually-hidden">Suppliers with purchases, payments and current due</caption>
                <thead>
                    <tr>
                        <th scope="col">Supplier</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Phone</th>
                        <th scope="col" class="num">Products</th>
                        <th scope="col" class="num">Total purchased</th>
                        <th scope="col" class="num">Paid</th>
                        <th scope="col" class="num">Due</th>
                        <th scope="col">Last purchase</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $id => $s)
                        <tr @class(['is-selected' => $id === $selected]) :class="{ 'is-selected': sel === '{{ $id }}' }" x-show="visible('{{ $id }}')">
                            <td>
                                <div class="media-row">
                                    <span class="sup-avatar">{{ $s['initials'] }}</span>
                                    <span class="media-row__text"><span class="media-row__name">{{ $s['name'] }}</span><span class="media-row__meta">{{ $s['type'] }} · {{ $s['area'] }}</span></span>
                                </div>
                            </td>
                            <td class="text-2">[Contact person]</td>
                            <td class="nowrap">{{ $s['phone'] }}</td>
                            <td class="num">{{ $s['products'] }}</td>
                            <td class="num">{{ DemoData::money($s['purchased']) }}</td>
                            <td class="num text-2">{{ DemoData::money($s['paid']) }}</td>
                            <td class="num">
                                <span @class(['fw-700', 'text-warn' => $s['due'] > 0, 'text-success' => $s['due'] === 0])>{{ DemoData::money($s['due']) }}</span>
                                <span @class(['sup-due-tag', 'text-warn' => $s['due'] > 0, 'text-success' => $s['due'] === 0])>{{ $s['due'] > 0 ? 'Due' : 'Settled' }}</span>
                            </td>
                            <td class="nowrap">{{ $s['last'] }}<span class="cell-sub">{{ $s['last_po'] }}</span></td>
                            <td class="cell-actions text-right">
                                <button type="button" class="btn btn--xs btn--brand" aria-label="Pay {{ $s['name'] }}" @disabled($s['due'] === 0) @click="pay('{{ $id }}')">Pay</button>
                                <button type="button" class="btn btn--xs btn--outline-strong text-brand" aria-label="View ledger of {{ $s['name'] }}" @click="view('{{ $id }}')">View</button>
                            </td>
                        </tr>
                    @endforeach
                    <tr x-show="shown === 0" x-cloak><td colspan="9" class="text-center muted">No suppliers match.</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Phones: supplier cards --}}
        <div class="sup-cards show-sm">
            @foreach ($suppliers as $id => $s)
                <article class="sup-card" :class="{ 'is-selected': sel === '{{ $id }}' }" x-show="visible('{{ $id }}')">
                    <div class="media-row">
                        <span class="sup-avatar">{{ $s['initials'] }}</span>
                        <span class="media-row__text"><span class="media-row__name">{{ $s['name'] }}</span><span class="media-row__meta">{{ $s['type'] }} · {{ $s['phone'] }}</span></span>
                        <span class="ml-auto text-right">
                            <span @class(['fw-700 tabular', 'text-warn' => $s['due'] > 0, 'text-success' => $s['due'] === 0])>{{ DemoData::money($s['due']) }}</span>
                            <span @class(['sup-due-tag', 'text-warn' => $s['due'] > 0, 'text-success' => $s['due'] === 0])>{{ $s['due'] > 0 ? 'Due' : 'Settled' }}</span>
                        </span>
                    </div>
                    <div class="fs-13 muted">Purchased {{ DemoData::money($s['purchased']) }} · last {{ $s['last'] }} ({{ $s['last_po'] }})</div>
                    <div class="sup-card__actions">
                        <button type="button" class="btn btn--sm btn--brand" @disabled($s['due'] === 0) @click="pay('{{ $id }}')">Pay</button>
                        <button type="button" class="btn btn--sm btn--outline-strong" @click="view('{{ $id }}')">View ledger</button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="sup-cols">
        {{-- Ledger --}}
        <section id="supplier-ledger" class="card card--stack sup-ledger" aria-labelledby="h-ledger">
            <div class="card-title-row card-title-row--top">
                <div>
                    <h2 id="h-ledger" class="card__title" x-text="'Ledger · ' + cur.name">Ledger · {{ $cur['name'] }}</h2>
                    <div class="card__subtitle" x-text="cur.type + ' · ' + cur.area + ' · ' + cur.phone + ' · [Contact person]'">{{ $cur['type'] }} · {{ $cur['area'] }} · {{ $cur['phone'] }} · [Contact person]</div>
                </div>
                <div class="cluster">
                    <label class="inline-field">Period
                        <select class="select">
                            <option>Last 60 days</option><option>This year</option><option>All time</option>
                        </select>
                    </label>
                    <button type="button" class="btn btn--sm btn--outline-strong" style="height: 38px" @click="window.print()">Print statement</button>
                </div>
            </div>
            <div class="stat-pills">
                <span class="stat-pill">Purchases <strong x-text="money(cur.ledger_purchases)">{{ DemoData::money($cur['ledger_purchases']) }}</strong></span>
                <span class="stat-pill stat-pill--in">Payments <strong x-text="money(cur.ledger_payments)">{{ DemoData::money($cur['ledger_payments']) }}</strong></span>
                <span @class(['stat-pill', 'stat-pill--due' => $cur['due'] > 0, 'stat-pill--ok' => $cur['due'] === 0])
                    :class="{ 'stat-pill--due': cur.due > 0, 'stat-pill--ok': cur.due === 0 }">Current due <strong x-text="money(cur.due)">{{ DemoData::money($cur['due']) }}</strong></span>
            </div>
            <div class="table-wrap">
                <table class="table sup-ledger-table" style="--table-min: 640px">
                    <caption class="visually-hidden">Purchases and payments with running due</caption>
                    <thead>
                        <tr>
                            <th scope="col">Date</th><th scope="col">Entry</th><th scope="col">Ref no.</th>
                            <th scope="col" class="num">Purchase</th><th scope="col" class="num">Payment</th><th scope="col" class="num">Running due</th>
                        </tr>
                    </thead>
                    @foreach ($suppliers as $id => $s)
                        <tbody x-show="sel === '{{ $id }}'" @if ($id !== $selected) x-cloak @endif>
                            @foreach ($s['ledger'] as $e)
                                <tr @class(['is-opening' => $e['opening']])>
                                    <td class="nowrap">{{ $e['date'] }}</td>
                                    <td>
                                        <span class="chip chip--sm chip--{{ $e['tone'] }}">{{ $e['kind'] }}</span>
                                        <span class="cell-sub">{{ $e['note'] }}</span>
                                    </td>
                                    <td>@if ($e['href'])<a class="fw-600" href="{{ $e['href'] }}">{{ $e['ref'] }}</a>@else<span class="fw-600">{{ $e['ref'] }}</span>@endif</td>
                                    <td class="num fw-600">{{ $e['purchase'] }}</td>
                                    <td class="num fw-600 text-success">{{ $e['payment'] }}</td>
                                    <td class="num fw-700">{{ $e['bal'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
        </section>

        {{-- Record payment --}}
        <section id="record-payment" class="card card--stack form-lg sup-pay" aria-labelledby="h-pay" style="gap: 12px">
            <h2 id="h-pay" class="card__title">Record payment</h2>
            <div class="alert alert--success" role="status" x-show="flash" x-cloak x-text="flash"></div>
            <label class="field">Supplier
                <select class="select" x-model="sel" @change="amountText = group(cur.due)">
                    @foreach ($suppliers as $id => $s)
                        <option value="{{ $id }}" @selected($id === $selected)>{{ $s['name'] }} · due {{ DemoData::money($s['due']) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="field">
                <label for="amt">Amount</label>
                <div class="cluster" style="--gap: 6px; flex-wrap: nowrap">
                    <span class="input-group input-group--44" style="flex: 1">
                        <span class="input-group__prefix" aria-hidden="true">৳</span>
                        <input id="amt" x-ref="amount" class="input-group__num fw-700" type="text" inputmode="numeric" x-model="amountText" @blur="amountText = group(amount)" value="{{ DemoData::number($cur['due']) }}">
                    </span>
                    <button type="button" class="btn btn--sm btn--outline-strong" style="height: 44px" @click="amountText = group(cur.due)">Full due</button>
                </div>
            </div>
            <div class="field-row">
                <label class="field" style="--basis: 140px">Date
                    <input class="input" type="date" value="2026-10-04">
                </label>
                <label class="field" style="--basis: 140px">Against purchase
                    <select class="select">
                        <option>Oldest due first</option>
                        <option x-text="cur.last_po">{{ $cur['last_po'] }}</option>
                    </select>
                </label>
            </div>
            <div class="field">
                <span id="lbl-pm">Method</span>
                <div class="option-grid" role="radiogroup" aria-labelledby="lbl-pm" style="--min: 110px">
                    @foreach (array_keys($refs) as $m)
                        <button type="button" role="radio" class="option-tile option-tile--center option-tile--solid"
                            aria-checked="{{ $m === 'Cash' ? 'true' : 'false' }}" :aria-checked="(method === '{{ $m }}').toString()" @click="pickMethod('{{ $m }}')">{{ $m }}</button>
                    @endforeach
                </div>
            </div>
            <label class="field">Reference
                <input class="input" type="text" x-model="reference" value="{{ $refs['Cash'] }}">
            </label>
            <label class="field">Note
                <textarea class="textarea" rows="2">Paid by Store Manager at supplier's shop.</textarea>
            </label>
            <div @class(['due-box', 'due-box--ok' => true]) :class="{ 'due-box--ok': after <= 0 }" aria-live="polite">
                <span class="due-box__label">Due after payment</span>
                <span class="due-box__value" x-text="money(after)">{{ DemoData::money(0) }}</span>
            </div>
            <button type="button" class="btn btn--brand btn--lg btn--block" :disabled="cur.due === 0 || amount === 0" @disabled($cur['due'] === 0) @click="save()">
                <span x-text="'Save payment · ৳' + group(amount)">Save payment · {{ DemoData::money($cur['due']) }}</span>
            </button>
            <p class="fs-12 muted">Saved payments post to Accounting as a supplier payment and can be printed as a voucher.</p>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const group = (n) => Number(n).toLocaleString('en-IN');

        Alpine.data('suppliersPage', () => ({
            suppliers: @js(collect($suppliers)->map(fn ($s) => collect($s)->except('ledger'))->all()),
            sel: @js($selected),
            q: '',
            show: 'all',
            amountText: @js(DemoData::number($cur['due'])),
            method: 'Cash',
            refs: @js($refs),
            reference: @js($refs['Cash']),
            flash: '',

            group,
            money(v) { return (v < 0 ? '−' : '') + '৳' + group(Math.abs(v)); },
            n(v) { return parseInt(String(v ?? '').replace(/[^\d]/g, ''), 10) || 0; },

            get cur() { return this.suppliers[this.sel]; },
            get amount() { return Math.min(this.n(this.amountText), this.cur.due); },
            get after() { return this.cur.due - this.amount; },
            visible(id) {
                const s = this.suppliers[id], q = this.q.trim().toLowerCase();
                const digits = q.replace(/\D/g, '');
                const match = !q || s.name.toLowerCase().includes(q) || s.type.toLowerCase().includes(q) || (digits && s.phone.replace(/\D/g, '').includes(digits));
                if (this.show === 'due') return match && s.due > 0;
                if (this.show === 'inactive') return false; // all six suppliers are active in the sample data
                return match;
            },
            get shown() { return Object.keys(this.suppliers).filter((id) => this.visible(id)).length; },
            select(id) { this.sel = id; this.amountText = group(this.suppliers[id].due); this.flash = ''; },
            pay(id) {
                this.select(id);
                this.$nextTick(() => {
                    document.getElementById('record-payment').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    this.$refs.amount.focus({ preventScroll: true });
                });
            },
            view(id) {
                this.select(id);
                this.$nextTick(() => document.getElementById('supplier-ledger').scrollIntoView({ behavior: 'smooth', block: 'start' }));
            },
            pickMethod(m) { this.method = m; this.reference = this.refs[m]; },
            save() {
                if (!this.amount) return;
                this.flash = this.money(this.amount) + ' paid to ' + this.cur.name + ' by ' + this.method + '. Due after payment ' + this.money(this.after) + '. This demo does not store changes yet.';
            },
        }));
    });
</script>
@endpush
