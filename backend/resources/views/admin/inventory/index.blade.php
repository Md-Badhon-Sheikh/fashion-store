{{--
    Stock overview — Inventory.dc.html.
    KPIs, view tabs, filterable size-matrix stock table (cells say "Low" / "Out" in text, not
    colour alone), new stock adjustment with live "stock after", and the movement ledger.
    Alpine component: `inventoryPage`.
    Data: $kpis, $tabs, $rows, $filters, $sizes, $ledger, $adjustments, $history, $variants
    (barcode => variant, for the adjustment look-up), $adjustSku, $adjustVariant.
--}}
@extends('admin.layouts.app')

@section('title', 'Stock overview')

@use('App\Support\DemoData')

@php
    $types = ['Damaged', 'Lost', 'Correction', 'Return to supplier'];
    $totalPcs = array_sum(array_column($rows, 'total'));
    $totalValue = array_sum(array_column($rows, 'value'));
    $cur = $adjustVariant['stock'] ?? 0;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/inventory.css') }}">
@endpush

@section('content')
<div class="page-stack" x-data="inventoryPage" style="gap: 18px">
    <x-admin.page-header title="Stock overview" subtitle="Main shop + online store share one stock · updated live from POS and online orders">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">Export stock report</button>
            <a class="btn btn--outline" href="#adjust" @click="focusAdjust()">New adjustment</a>
            <a class="btn btn--brand" href="{{ route('admin.stock-in') }}">+ Stock-in</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="kpi-grid">
        @foreach ($kpis as $k)
            <x-admin.kpi :label="$k['label']" :value="$k['value']" :sub="$k['sub']" :tone="$k['tone']" />
        @endforeach
    </div>

    <div id="inv-tabs">
        <x-admin.tabs class="hide-sm" model="tab" label="Inventory views" :tabs="$tabs" />
        <x-admin.tabs class="show-sm" variant="pill" model="tab" label="Inventory views" :tabs="$tabs" />
    </div>

    {{-- ===== Stock levels ===== --}}
    <form class="filter-bar" role="search" aria-label="Filter stock" x-show="tab === 'levels'" @submit.prevent>
        <label class="field field--wide">Search
            <input class="input input--sm" type="search" placeholder="Product name or SKU" x-model="f.q">
        </label>
        @foreach ($filters as $key => $filter)
            <label class="field">{{ $filter['label'] }}
                <select class="select select--sm" x-model="f.{{ $key }}">
                    @foreach ($filter['options'] as $value => $text)
                        <option value="{{ $value }}">{{ $text }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
    </form>

    <section class="card card--flush" aria-labelledby="h-levels" x-show="tab === 'levels'">
        <div class="panel-head">
            <h2 id="h-levels" class="card__title">Stock by size</h2>
            <div class="stock-legend">
                <span class="stock-legend__item"><span class="stock-key stock-key--low">3 Low</span>at or below alert level</span>
                <span class="stock-legend__item"><span class="stock-key stock-key--out">0 Out</span>no stock</span>
                <span class="stock-legend__item"><span class="stock-key">–</span>size not offered</span>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table stock-table" style="--table-min: 980px">
                <caption class="visually-hidden">Stock per size for each product colour, with total, value at cost and status</caption>
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Colour</th>
                        @foreach ($sizes as $s)
                            <th scope="col" class="text-center stock-table__size">{{ $s }}</th>
                        @endforeach
                        <th scope="col" class="num">Total</th>
                        <th scope="col" class="num">Value (cost)</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $r)
                        <tr x-show="visible({{ $i }})">
                            <td>
                                <div class="media-row">
                                    <span class="photo photo--36" style="--tone: {{ $r['tone'] }}"><img src="{{ $r['img'] }}" alt="{{ $r['name'] }}" loading="lazy"></span>
                                    <span class="media-row__text"><span class="media-row__name">{{ $r['name'] }}</span><span class="media-row__meta">{{ $r['sku'] }} · {{ $r['cat'] }}</span></span>
                                </div>
                            </td>
                            <td><span class="swatch-label"><span class="swatch" style="--swatch: {{ $r['hex'] }}"></span>{{ $r['colour'] }}</span></td>
                            @foreach ($r['cells'] as $c)
                                <td class="text-center stock-table__size">
                                    <span class="stock-cell stock-cell--{{ $c['tone'] }}" title="{{ $c['title'] }}">
                                        <span class="stock-cell__n">{{ $c['n'] }}</span>
                                        @if ($c['tag'])<span class="stock-cell__tag">{{ $c['tag'] }}</span>@endif
                                        @if ($c['tone'] === 'none')<span class="visually-hidden">not offered</span>@endif
                                    </span>
                                </td>
                            @endforeach
                            <td class="num cell-strong">{{ $r['total'] }}</td>
                            <td class="num text-2">{{ DemoData::money($r['value']) }}</td>
                            <td><x-admin.status-chip :status="$r['status']" :label="$r['status_label']" /></td>
                        </tr>
                    @endforeach
                    <tr x-show="shown === 0" x-cloak><td colspan="10" class="text-center muted">No products match these filters.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="panel-foot">
            <span>Showing <span x-text="shown">{{ count($rows) }}</span> of 412 products · totals for filtered rows:
                <strong x-text="number(totals.pcs) + ' pcs · ' + money(totals.value)">{{ DemoData::number($totalPcs) }} pcs · {{ DemoData::money($totalValue) }}</strong></span>
            <nav class="pagination" aria-label="Stock pages">
                <button type="button" class="pagination__btn" aria-label="Previous page" disabled>‹</button>
                <button type="button" class="pagination__btn" aria-current="page">1</button>
                <button type="button" class="pagination__btn">2</button>
                <button type="button" class="pagination__btn" aria-label="Next page">›</button>
            </nav>
        </div>
    </section>

    {{-- ===== Adjustments ===== --}}
    <section class="card card--flush" aria-labelledby="h-adjlist" x-show="tab === 'adjustments'" x-cloak>
        <div class="panel-head">
            <h2 id="h-adjlist" class="card__title">Recent adjustments</h2>
            <a class="btn btn--sm btn--outline-strong" href="#adjust" @click="focusAdjust()">New adjustment</a>
        </div>
        <div class="table-wrap">
            <table class="table" style="--table-min: 760px">
                <thead>
                    <tr><th scope="col">Ref no.</th><th scope="col">Variant</th><th scope="col">Type</th><th scope="col" class="num">Qty</th><th scope="col">Reason</th><th scope="col">By</th></tr>
                </thead>
                <tbody>
                    @foreach ($adjustments as $a)
                        <tr>
                            <td class="nowrap"><span class="fw-600">{{ $a['ref'] }}</span><span class="cell-sub">{{ $a['date'] }}</span></td>
                            <td>{{ $a['name'] }}<span class="cell-sub mono">{{ $a['sku'] }}</span></td>
                            <td><x-admin.status-chip :status="$a['type'] === 'Correction' ? 'scheduled' : 'pending'" :label="$a['type']" size="sm" /></td>
                            <td @class(['num fw-700', 'text-success' => $a['positive'], 'text-danger' => ! $a['positive']])>{{ $a['qty'] }}</td>
                            <td class="text-2">{{ $a['note'] }}</td>
                            <td class="nowrap">{{ $a['by'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-foot">Showing 6 of 14 adjustments this month</div>
    </section>

    {{-- ===== Movement history ===== --}}
    <section class="card card--flush" aria-labelledby="h-hist" x-show="tab === 'history'" x-cloak>
        <div class="panel-head">
            <h2 id="h-hist" class="card__title">Movement history · all products</h2>
            <button type="button" class="btn btn--sm btn--outline-strong">Export</button>
        </div>
        <div class="table-wrap">
            <table class="table" style="--table-min: 720px">
                <thead>
                    <tr><th scope="col">When</th><th scope="col">Type</th><th scope="col">Ref no.</th><th scope="col">Item</th><th scope="col" class="num">Qty</th><th scope="col">By</th></tr>
                </thead>
                <tbody>
                    @foreach ($history as $h)
                        <tr>
                            <td class="nowrap">{{ $h['when'] }}</td>
                            <td><span class="chip chip--sm chip--{{ $h['tone'] }}">{{ $h['type'] }}</span></td>
                            <td>@if ($h['href'])<a class="fw-600" href="{{ $h['href'] }}">{{ $h['ref'] }}</a>@else<span class="fw-600">{{ $h['ref'] }}</span>@endif</td>
                            <td>{{ $h['item'] }}</td>
                            <td @class(['num fw-700', 'text-success' => $h['positive'], 'text-danger' => ! $h['positive']])>{{ $h['qty'] }}</td>
                            <td class="nowrap text-2">{{ $h['by'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ===== Adjustment form + ledger ===== --}}
    <div class="inv-cols">
        <section id="adjust" class="card card--stack form-lg inv-adjust" aria-labelledby="h-adj" style="gap: 14px">
            <h2 id="h-adj" class="card__title">New stock adjustment</h2>
            <div class="alert alert--success" role="status" x-show="flash" x-cloak x-text="flash"></div>

            <div class="field">
                <label for="adjv">Variant</label>
                <div class="suggest" @click.outside="suggestOpen = false">
                    <div class="input-group">
                        <x-admin.icon name="search" :size="16" :stroke="2" />
                        <input id="adjv" type="search" autocomplete="off" placeholder="SKU or barcode" value="{{ $adjustSku }}" x-ref="adjv"
                            x-model="adj.query" @input="resolve()" @focus="suggestOpen = true" @keydown.escape="suggestOpen = false"
                            aria-describedby="adj-variant">
                    </div>
                    <ul class="suggest__list" x-show="suggestOpen && !adj.variant && adj.query.trim().length > 2" x-cloak>
                        <template x-for="v in suggestions" :key="v.code">
                            <li><button type="button" class="suggest__item" @click="pick(v)">
                                <span><span class="fw-600" x-text="v.name"></span>
                                    <span class="media-row__meta" x-text="v.size + ' · ' + v.colour + ' · ' + v.sku + ' · ' + v.stock + ' pcs'"></span></span>
                            </button></li>
                        </template>
                        <li class="suggest__empty" x-show="!suggestions.length">No variant found.</li>
                    </ul>
                </div>
                <div id="adj-variant" class="variant-card" x-show="adj.variant" @if (! $adjustVariant) x-cloak @endif>
                    <span>
                        <span class="variant-card__name" x-text="adj.variant ? adj.variant.name : ''">{{ $adjustVariant['name'] ?? '' }}</span>
                        <span class="muted fw-500" x-text="adj.variant ? adj.variant.size + ' · ' + adj.variant.colour + ' · barcode ' + adj.variant.code : ''">{{ $adjustVariant ? $adjustVariant['size'].' · '.$adjustVariant['colour'].' · barcode '.$adjustVariant['code'] : '' }}</span>
                    </span>
                    <span class="variant-card__stock">
                        <span class="variant-card__label">Current</span>
                        <span class="variant-card__value" x-text="current + ' pcs'">{{ $cur }} pcs</span>
                    </span>
                </div>
            </div>

            <div class="field">
                <span id="lbl-type">Adjustment type</span>
                <div class="option-row" role="radiogroup" aria-labelledby="lbl-type" style="--basis: 130px">
                    @foreach ($types as $t)
                        <button type="button" role="radio" class="option-tile option-tile--center option-tile--solid" style="font-size: 14px"
                            aria-checked="{{ $t === 'Damaged' ? 'true' : 'false' }}" :aria-checked="(adj.type === '{{ $t }}').toString()" @click="setType('{{ $t }}')">{{ $t }}</button>
                    @endforeach
                </div>
            </div>

            <div class="adj-row">
                <div class="field">
                    <span id="lbl-dir">Direction</span>
                    <div class="seg seg--inline" role="radiogroup" aria-labelledby="lbl-dir">
                        <button type="button" role="radio" class="seg__btn" aria-checked="false" disabled
                            :aria-checked="(dir === 'in').toString()" :disabled="forcedOut" @click="adj.dir = 'in'">Add (+)</button>
                        <button type="button" role="radio" class="seg__btn" aria-checked="true"
                            :aria-checked="(dir === 'out').toString()" @click="adj.dir = 'out'">Remove (−)</button>
                    </div>
                </div>
                <div class="field">
                    <label for="adjq">Quantity</label>
                    <div class="stepper stepper--lg">
                        <button type="button" class="stepper__btn" aria-label="Decrease quantity" @click="adj.qty = Math.max(1, adj.qty - 1)">−</button>
                        <input id="adjq" class="stepper__input" type="text" inputmode="numeric" value="−1"
                            :value="(dir === 'out' ? '−' : '+') + adj.qty" :class="dir === 'out' ? 'text-danger' : 'text-success'"
                            @change="adj.qty = Math.max(1, parseInt($event.target.value.replace(/\D/g, ''), 10) || 1); $event.target.value = (dir === 'out' ? '−' : '+') + adj.qty">
                        <button type="button" class="stepper__btn" aria-label="Increase quantity" @click="adj.qty++">+</button>
                    </div>
                </div>
                <div class="adj-after" aria-live="polite">Stock after:
                    <strong :class="afterClass" class="text-warn" x-text="current + ' → ' + (after < 0 ? '−' + Math.abs(after) : after) + ' pcs'">{{ $cur }} → {{ $cur - 1 }} pcs</strong>
                </div>
            </div>
            <div class="alert alert--danger" role="alert" x-show="after < 0" x-cloak>
                Cannot remove more than the current stock (<span x-text="current"></span> pcs).
            </div>

            <label class="field">Reason / note
                <textarea class="textarea" rows="3" x-model="adj.note">Stain on front placket found during display change. Moved to damaged rack.</textarea>
            </label>
            <div class="cluster" style="justify-content: flex-end">
                <button type="button" class="btn btn--outline-strong" @click="clear()">Clear</button>
                <button type="button" class="btn btn--brand" @click="saveAdjustment()" :disabled="!adj.variant || after < 0">Save adjustment</button>
            </div>
        </section>

        <section class="card card--stack inv-ledger" aria-labelledby="h-led">
            <div class="card-title-row card-title-row--top">
                <div>
                    <h2 id="h-led" class="card__title">Stock movement ledger</h2>
                    <div class="card__subtitle">Embroidered Cotton Panjabi · M · Off-white · <span class="mono">PNJ-1024-OW-M</span></div>
                </div>
                <div class="cluster">
                    <label class="inline-field">Period
                        <select class="select">
                            <option>1 Sep – 4 Oct 2026</option><option>Last 7 days</option><option>This month</option>
                        </select>
                    </label>
                    <button type="button" class="btn btn--sm btn--outline-strong" style="height: 38px">Export</button>
                </div>
            </div>
            <div class="stat-pills">
                <span class="stat-pill">Opening <strong>0</strong></span>
                <span class="stat-pill stat-pill--in">In <strong>+21</strong></span>
                <span class="stat-pill stat-pill--out">Out <strong>−17</strong></span>
                <span class="stat-pill">Closing <strong>4</strong></span>
            </div>
            <div class="table-wrap">
                <table class="table ledger-table" style="--table-min: 720px">
                    <caption class="visually-hidden">Stock movements for PNJ-1024-OW-M, newest first</caption>
                    <thead>
                        <tr>
                            <th scope="col">Date</th><th scope="col">Type</th><th scope="col">Ref no.</th>
                            <th scope="col" class="num">In</th><th scope="col" class="num">Out</th><th scope="col" class="num">Balance</th><th scope="col">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ledger as $m)
                            <tr>
                                <td class="nowrap">{{ $m['date'] }}<span class="cell-sub">{{ $m['time'] }}</span></td>
                                <td><span class="chip chip--sm chip--{{ $m['tone'] }}">{{ $m['type'] }}</span></td>
                                <td>
                                    @if ($m['href'])
                                        <a class="fw-600" href="{{ $m['href'] }}">{{ $m['ref'] }}</a>
                                    @else
                                        <button type="button" class="link-btn" @click="tab = 'adjustments'; document.getElementById('inv-tabs').scrollIntoView({ behavior: 'smooth' })">{{ $m['ref'] }}</button>
                                    @endif
                                    <span class="cell-sub">{{ $m['note'] }}</span>
                                </td>
                                <td class="num fw-700 text-success">{{ $m['in'] }}</td>
                                <td class="num fw-700 text-danger">{{ $m['out'] }}</td>
                                <td class="num fw-700">{{ $m['bal'] }}</td>
                                <td class="nowrap text-2">{{ $m['by'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const group = (n) => Number(n).toLocaleString('en-IN');

        Alpine.data('inventoryPage', () => ({
            tab: 'levels',
            f: { q: '', cat: '', size: '', colour: '', status: '' },
            rows: @js(array_map(fn ($r) => ['name' => $r['name'], 'sku' => $r['sku'], 'cat' => $r['cat'], 'colour' => $r['colour'], 'sizes' => $r['sizes'], 'status' => $r['status'], 'low' => $r['low'], 'out' => $r['out'], 'total' => $r['total'], 'value' => $r['value']], $rows)),
            variants: @js($variants),
            adj: { query: @js($adjustSku), variant: @js($adjustVariant), type: 'Damaged', dir: 'out', qty: 1, note: 'Stain on front placket found during display change. Moved to damaged rack.' },
            suggestOpen: false,
            flash: '',

            number: group,
            money(n) { return '৳' + group(n); },

            /* Stock table filters */
            visible(i) {
                const r = this.rows[i], f = this.f, q = f.q.trim().toLowerCase();
                return (!q || r.name.toLowerCase().includes(q) || r.sku.toLowerCase().includes(q))
                    && (!f.cat || r.cat === f.cat)
                    && (!f.size || r.sizes.includes(f.size))
                    && (!f.colour || r.colour === f.colour)
                    && (!f.status || (f.status === 'available' && r.status === 'available') || (f.status === 'low' && r.low > 0) || (f.status === 'out' && r.out > 0));
            },
            get shown() { return this.rows.filter((r, i) => this.visible(i)).length; },
            get totals() {
                return this.rows.reduce((t, r, i) => (this.visible(i) ? { pcs: t.pcs + r.total, value: t.value + r.value } : t), { pcs: 0, value: 0 });
            },

            /* Adjustment */
            get forcedOut() { return this.adj.type !== 'Correction'; },
            get dir() { return this.forcedOut ? 'out' : this.adj.dir; },
            get current() { return this.adj.variant ? this.adj.variant.stock : 0; },
            get after() { return this.dir === 'out' ? this.current - this.adj.qty : this.current + this.adj.qty; },
            get afterClass() { return { 'text-danger': this.after < 0, 'text-warn': this.after >= 0 && this.after <= 5, 'text-success': this.after > 5 }; },
            setType(t) { this.adj.type = t; if (t !== 'Correction') this.adj.dir = 'out'; },
            resolve() {
                const q = this.adj.query.trim().toLowerCase();
                this.adj.variant = this.variants[q] || Object.values(this.variants).find((v) => v.sku.toLowerCase() === q) || null;
                this.suggestOpen = true;
            },
            get suggestions() {
                const q = this.adj.query.trim().toLowerCase();
                return Object.values(this.variants).filter((v) => v.sku.toLowerCase().includes(q) || v.code.includes(q) || v.name.toLowerCase().includes(q)).slice(0, 8);
            },
            pick(v) { this.adj.variant = v; this.adj.query = v.sku; this.suggestOpen = false; },
            focusAdjust() { this.$nextTick(() => this.$refs.adjv && this.$refs.adjv.focus({ preventScroll: true })); },
            clear() {
                Object.assign(this.adj, { query: '', variant: null, type: 'Damaged', dir: 'out', qty: 1, note: '' });
                this.flash = '';
            },
            saveAdjustment() {
                if (!this.adj.variant || this.after < 0) return;
                this.flash = this.adj.type + ' adjustment for ' + this.adj.variant.sku + ' saved (' + this.current + ' → ' + this.after + ' pcs). This demo does not store changes yet.';
            },
        }));
    });
</script>
@endpush
