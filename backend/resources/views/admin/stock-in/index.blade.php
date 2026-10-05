{{--
    Stock-in / purchases — StockIn.dc.html.
    Recent purchase cards + new purchase form: supplier, scan / search to add lines, qty and unit
    cost per line with stock now → after, live totals, payment with due and supplier balance,
    "print barcode labels after saving". Alpine component: `stockIn`.
    Data: $recent, $lines, $suppliers, $defaults, $refs (method => reference), $variants (barcode => variant).
--}}
@extends('admin.layouts.app')

@section('title', 'Stock-in / purchases')

@use('App\Support\DemoData')

@php
    $sub = array_sum(array_map(fn ($l) => $l['qty'] * $l['cost'], $lines));
    $pcs = array_sum(array_column($lines, 'qty'));
    $grand = $sub - $defaults['discount'] + $defaults['transport'];
    $paid = min($defaults['paid'], $grand);
    $due = $grand - $paid;
    $supplierDue = collect($suppliers)->firstWhere('id', $defaults['supplier'])['due'] ?? 0;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/stock-in.css') }}">
@endpush

@section('content')
<div class="page-stack page-stack--lg" x-data="stockIn">
    <x-admin.page-header title="Stock-in / purchases" subtitle="Record goods received from suppliers. Stock and supplier due update when you save.">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">All purchases (42)</button>
            <a class="btn btn--outline" href="{{ route('admin.suppliers') }}">Suppliers</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Recent purchases --}}
    <section class="stack stack--sm" aria-labelledby="h-recent" style="--gap: 10px">
        <div class="card-title-row">
            <h2 id="h-recent" class="si-section-title">Recent purchases</h2>
            <span class="card-meta">October so far: {{ DemoData::money(110940) }} purchased · open dues on these POs {{ DemoData::money(60490) }}</span>
        </div>
        <div class="grid-auto" style="--min: 220px; --gap: 12px">
            @foreach ($recent as $po)
                <a class="po-card" href="{{ route('admin.suppliers', ['supplier' => $po['supplier_id']]) }}" aria-label="{{ $po['id'] }}, {{ $po['supplier'] }}, {{ $po['status_label'] }}. Open supplier ledger">
                    <span class="po-card__row">
                        <span class="po-card__id">{{ $po['id'] }}</span>
                        <x-admin.status-chip :status="$po['status']" :label="$po['status_label']" size="sm" />
                    </span>
                    <span class="fs-13 text-2">{{ $po['supplier'] }} · {{ $po['date'] }} · {{ $po['pcs'] }} pcs</span>
                    <span class="po-card__row po-card__money">
                        <span class="po-card__amount">{{ DemoData::money($po['amount']) }}</span>
                        <span class="text-right muted">Paid {{ DemoData::money($po['paid']) }}
                            <span @class(['po-card__due', 'text-success' => $po['due'] === 0, 'text-warn' => $po['due'] > 0 && $po['paid'] > 0, 'text-danger' => $po['paid'] === 0])>Due {{ DemoData::money($po['due']) }}</span>
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="alert alert--success" role="status" x-show="flash" x-cloak x-text="flash"></div>

    <div class="si-cols">
        <div class="si-main">
            {{-- Purchase header --}}
            <section class="card card--stack form-lg" aria-labelledby="h-new" style="gap: 14px">
                <div class="card-title-row">
                    <h2 id="h-new" class="card__title">New purchase <span class="muted fw-600">{{ $defaults['po'] }}</span></h2>
                    <x-admin.status-chip status="draft" />
                </div>
                <div class="field-row">
                    <div class="field" style="--basis: 240px; flex-grow: 2">
                        <label for="sup">Supplier</label>
                        <div class="cluster" style="--gap: 6px; flex-wrap: nowrap">
                            <select id="sup" class="select" x-model="supplierId">
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s['id'] }}" @selected($s['id'] === $defaults['supplier'])>{{ $s['label'] }}</option>
                                @endforeach
                            </select>
                            <a class="btn btn--outline-strong" style="height: 44px; font-size: 13px" href="{{ route('admin.suppliers') }}">+ Add supplier</a>
                        </div>
                    </div>
                    <label class="field">Supplier invoice no.
                        <input class="input" type="text" name="invoice" value="{{ $defaults['invoice'] }}">
                    </label>
                </div>
                <div class="field-row">
                    <label class="field">Purchase date
                        <input class="input" type="date" name="date" value="{{ $defaults['date'] }}">
                    </label>
                    <label class="field" style="--basis: 200px">Receive into
                        <select class="select" name="location">
                            <option>Main shop (Dhaka) · shared with online</option>
                            <option>Warehouse · Mirpur</option>
                        </select>
                    </label>
                    <label class="field">Received by
                        <select class="select" name="received_by">
                            <option>Store Manager</option>
                            <option>Sales Staff 01</option>
                        </select>
                    </label>
                </div>
            </section>

            {{-- Items --}}
            <section class="card card--stack" aria-labelledby="h-items" style="gap: 14px">
                <div class="card-title-row">
                    <h2 id="h-items" class="card__title">Items</h2>
                    <span class="card-meta" x-text="lines.length + ' lines · ' + pcs + ' pcs'">{{ count($lines) }} lines · {{ $pcs }} pcs</span>
                </div>
                <div class="field-row" style="--basis: 240px; gap: 10px">
                    <form @submit.prevent="scan()">
                        <div class="input-group input-group--scan">
                            <x-admin.icon name="barcode" :size="20" />
                            <label for="scanin" class="visually-hidden">Scan barcode to add item</label>
                            <input id="scanin" type="text" inputmode="numeric" autocomplete="off" placeholder="Scan barcode, each scan adds 1 pc" x-model="scanCode">
                        </div>
                    </form>
                    <div class="suggest" @click.outside="q = ''">
                        <div class="input-group input-group--48">
                            <x-admin.icon name="search" :size="18" :stroke="2" />
                            <label for="searchin" class="visually-hidden">Search product to add</label>
                            <input id="searchin" type="search" autocomplete="off" placeholder="Search product, then pick size and colour" x-model="q" @keydown.escape="q = ''">
                        </div>
                        <ul class="suggest__list" x-show="q.trim().length > 1" x-cloak>
                            <template x-for="v in results" :key="v.code">
                                <li><button type="button" class="suggest__item" @click="addVariant(v); q = ''">
                                    <span class="photo photo--34" :style="'--tone: ' + v.tone"><img :src="v.img" alt=""></span>
                                    <span><span class="fw-600" x-text="v.name"></span>
                                        <span class="media-row__meta" x-text="v.size + ' · ' + v.colour + ' · ' + v.sku + ' · in stock ' + v.stock"></span></span>
                                </button></li>
                            </template>
                            <li class="suggest__empty" x-show="!results.length">No variant matches.</li>
                        </ul>
                    </div>
                </div>
                <p class="alert alert--warn" role="alert" x-show="scanError" x-cloak x-text="scanError"></p>

                <div class="si-table-wrap">
                    <table class="table si-table" style="--table-min: 860px">
                        <caption class="visually-hidden">Purchase lines with quantity, unit cost, total and stock before and after</caption>
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Variant</th>
                                <th scope="col" class="text-center">Qty</th>
                                <th scope="col" class="num">Unit cost ৳</th>
                                <th scope="col" class="num">Total</th>
                                <th scope="col" class="text-center">Stock now → after</th>
                                <th scope="col"><span class="visually-hidden">Remove</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(l, i) in lines" :key="l.sku">
                                <tr>
                                    <td>
                                        <div class="media-row">
                                            <span class="photo photo--36" :style="'--tone: ' + l.tone"><img :src="l.img" :alt="l.name"></span>
                                            <span class="media-row__text"><span class="media-row__name" x-text="l.name"></span><span class="media-row__meta mono" x-text="l.sku"></span></span>
                                        </div>
                                    </td>
                                    <td><span class="swatch-label"><span class="fw-700" x-text="l.size"></span>·<span class="swatch" :style="'--swatch: ' + l.hex"></span><span x-text="l.colour"></span></span></td>
                                    <td class="text-center">
                                        <div class="stepper">
                                            <button type="button" class="stepper__btn" :aria-label="'Decrease quantity of ' + label(l)" @click="l.qty = Math.max(1, l.qty - 1)">−</button>
                                            <input class="stepper__input" type="text" inputmode="numeric" :id="'q-' + i" :aria-label="'Quantity of ' + label(l)"
                                                :value="l.qty" @change="l.qty = Math.max(1, n($event.target.value)); $event.target.value = l.qty">
                                            <button type="button" class="stepper__btn" :aria-label="'Increase quantity of ' + label(l)" @click="l.qty++">+</button>
                                        </div>
                                    </td>
                                    <td class="num">
                                        <input class="input-cell input-cell--w76" type="text" inputmode="numeric" :aria-label="'Unit cost of ' + label(l)"
                                            :value="group(l.cost)" @change="l.cost = n($event.target.value); $event.target.value = group(l.cost)">
                                    </td>
                                    <td class="num fw-700" x-text="money(l.qty * l.cost)"></td>
                                    <td class="text-center nowrap">
                                        <span class="stock-tag" :class="l.stock === 0 ? 'stock-tag--out' : (l.stock <= 5 ? 'stock-tag--low' : '')"
                                            x-text="l.stock === 0 ? '0 Out' : (l.stock <= 5 ? l.stock + ' Low' : l.stock)"></span>
                                        <span aria-hidden="true" class="muted">→</span><span class="visually-hidden">becomes</span>
                                        <span class="fw-700 text-success" x-text="l.stock + l.qty"></span>
                                    </td>
                                    <td>
                                        <button type="button" class="icon-btn icon-btn--sm si-remove" :aria-label="'Remove ' + label(l)" @click="lines.splice(i, 1)">
                                            <x-admin.icon name="close" :size="14" :stroke="2" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!lines.length" x-cloak>
                                <td colspan="7" class="text-center muted">No items yet. Scan a barcode or search a product to add it.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <label class="field">
                    <span>Note <span class="field__hint">Internal, not shown to supplier</span></span>
                    <textarea class="textarea" name="note" rows="2">{{ $defaults['note'] }}</textarea>
                </label>
            </section>
        </div>

        <div class="si-side">
            {{-- Totals --}}
            <section class="card card--stack si-totals" aria-labelledby="h-tot" style="gap: 10px">
                <h2 id="h-tot" class="card__title" style="margin-bottom: 4px">Totals</h2>
                <div class="kv"><span class="text-2" x-text="'Subtotal (' + pcs + ' pcs)'">Subtotal ({{ $pcs }} pcs)</span><strong x-text="money(subtotal)">{{ DemoData::money($sub) }}</strong></div>
                <div class="kv si-kv-input">
                    <label for="disc" class="text-2">Discount</label>
                    <span class="input-group si-amount"><span class="input-group__prefix">− ৳</span>
                        <input id="disc" type="text" inputmode="numeric" x-model="discount" @blur="discount = group(n(discount))" value="{{ DemoData::number($defaults['discount']) }}">
                    </span>
                </div>
                <div class="kv si-kv-input">
                    <label for="trans" class="text-2">Transport / labour cost</label>
                    <span class="input-group si-amount"><span class="input-group__prefix">+ ৳</span>
                        <input id="trans" type="text" inputmode="numeric" x-model="transport" @blur="transport = group(n(transport))" value="{{ DemoData::number($defaults['transport']) }}">
                    </span>
                </div>
                <div class="si-grand"><span class="fw-700">Grand total</span><span class="si-grand__value" x-text="money(grand)">{{ DemoData::money($grand) }}</span></div>
                <p class="fs-12 muted">Transport cost is spread across items to update average cost per piece.</p>
            </section>

            {{-- Payment --}}
            <section class="card card--stack form-lg" aria-labelledby="h-pay" style="gap: 12px">
                <h2 id="h-pay" class="card__title">Payment</h2>
                <div class="field">
                    <span id="lbl-method">Method</span>
                    <div class="option-grid" role="radiogroup" aria-labelledby="lbl-method" style="--min: 120px">
                        @foreach (array_keys($refs) as $m)
                            <button type="button" role="radio" class="option-tile option-tile--center option-tile--solid"
                                aria-checked="{{ $m === $defaults['method'] ? 'true' : 'false' }}" :aria-checked="(method === '{{ $m }}').toString()" @click="pickMethod('{{ $m }}')">{{ $m }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label for="paid">Paid amount</label>
                    <div class="cluster" style="--gap: 6px">
                        <span class="input-group input-group--44" style="flex: 1 1 140px; min-width: 0">
                            <span class="input-group__prefix" aria-hidden="true">৳</span>
                            <input id="paid" class="input-group__num fw-700" type="text" inputmode="numeric" x-model="paidText" @blur="paidText = group(paid)" value="{{ DemoData::number($paid) }}">
                        </span>
                        <button type="button" class="btn btn--sm btn--outline-strong" style="height: 44px" @click="paidText = group(grand)">Pay full</button>
                        <button type="button" class="btn btn--sm btn--outline-strong" style="height: 44px" @click="paidText = group(30000)">৳30,000</button>
                    </div>
                </div>
                <label class="field">Transaction / cheque ref
                    <input class="input" type="text" name="reference" x-model="reference" value="{{ $refs[$defaults['method']] }}">
                </label>
                <div class="due-box" :class="{ 'due-box--ok': due <= 0 }" aria-live="polite">
                    <span class="due-box__label" x-text="due > 0 ? 'Due to supplier' : 'Fully paid'">{{ $due > 0 ? 'Due to supplier' : 'Fully paid' }}</span>
                    <span class="due-box__value" x-text="money(due)">{{ DemoData::money($due) }}</span>
                </div>
                <p class="fs-12 muted"><span x-text="supplier.name">Supplier 03</span> balance after save: <strong class="text-2" x-text="money(supplier.due + due)">{{ DemoData::money($supplierDue + $due) }}</strong> due</p>
            </section>

            {{-- Save --}}
            <section class="card card--stack" style="gap: 12px">
                <button type="button" role="checkbox" class="check-row" aria-checked="{{ $defaults['print'] ? 'true' : 'false' }}" :aria-checked="print.toString()" @click="print = !print">
                    <span class="tick-box"><x-admin.icon name="check" :size="12" :stroke="4" /></span>
                    <span><span class="check-row__title">Print barcode labels after saving</span>
                        <span class="check-row__note" x-text="pcs + ' labels, one per piece · opens Barcode labels'">{{ $pcs }} labels, one per piece · opens Barcode labels</span></span>
                </button>
                <button type="button" class="btn btn--brand btn--lg btn--block" @click="save()" :disabled="!lines.length">
                    <span x-text="'Save purchase · ' + money(grand)">Save purchase · {{ DemoData::money($grand) }}</span>
                </button>
                <button type="button" class="btn btn--outline-strong btn--block" @click="flash = 'Draft {{ $defaults['po'] }} saved. This demo does not store changes yet.'">Save as draft</button>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const group = (n) => Number(n).toLocaleString('en-IN');

        Alpine.data('stockIn', () => ({
            lines: @js($lines),
            suppliers: @js($suppliers),
            variants: @js($variants),
            refs: @js($refs),
            supplierId: @js($defaults['supplier']),
            discount: @js(\App\Support\DemoData::number($defaults['discount'])),
            transport: @js(\App\Support\DemoData::number($defaults['transport'])),
            paidText: @js(\App\Support\DemoData::number($defaults['paid'])),
            method: @js($defaults['method']),
            reference: @js($refs[$defaults['method']]),
            print: @js($defaults['print']),
            scanCode: '',
            scanError: '',
            q: '',
            flash: '',
            barcodesUrl: @js(route('admin.barcodes')),

            group,
            n(v) { return parseInt(String(v ?? '').replace(/[^\d]/g, ''), 10) || 0; },
            money(v) { return (v < 0 ? '−' : '') + '৳' + group(Math.abs(v)); },
            label(l) { return l.name + ' ' + l.size + ' ' + l.colour; },

            get supplier() { return this.suppliers.find((s) => s.id === this.supplierId) || { name: '', due: 0 }; },
            get subtotal() { return this.lines.reduce((s, l) => s + l.qty * l.cost, 0); },
            get pcs() { return this.lines.reduce((s, l) => s + l.qty, 0); },
            get grand() { return Math.max(0, this.subtotal - this.n(this.discount) + this.n(this.transport)); },
            get paid() { return Math.min(this.n(this.paidText), this.grand); },
            get due() { return this.grand - this.paid; },
            pickMethod(m) { this.method = m; this.reference = this.refs[m]; },

            /* Adding lines */
            addVariant(v) {
                const line = this.lines.find((l) => l.sku === v.sku);
                if (line) { line.qty++; return; }
                this.lines.push({ name: v.name, sku: v.sku, size: v.size, colour: v.colour, hex: v.hex || '#E5E7EA', cost: v.cost || 0, stock: v.stock, qty: 1, img: v.img, tone: v.tone });
            },
            scan() {
                const code = this.scanCode.trim();
                if (!code) return;
                const v = this.variants[code] || Object.values(this.variants).find((x) => x.sku.toLowerCase() === code.toLowerCase());
                this.scanError = v ? '' : 'No product found for barcode ' + code + '.';
                if (v) this.addVariant(v);
                this.scanCode = '';
            },
            get results() {
                const q = this.q.trim().toLowerCase();
                if (q.length < 2) return [];
                return Object.values(this.variants).filter((v) => v.name.toLowerCase().includes(q) || v.sku.toLowerCase().includes(q)).slice(0, 8);
            },

            save() {
                this.flash = 'Purchase ' + @js($defaults['po']) + ' saved · ' + this.money(this.grand) + ' (' + this.pcs + ' pcs). This demo does not store changes yet.'
                    + (this.print ? ' Opening Barcode labels…' : '');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (this.print) setTimeout(() => { window.location.href = this.barcodesUrl; }, 1200);
            },
        }));
    });
</script>
@endpush
