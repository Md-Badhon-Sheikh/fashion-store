{{--
    Barcode labels — Barcodes.dc.html.
    Queue of variants with label quantities, label settings and printer choice drive the
    live label preview (Alpine `barcodeLabels`). Scan test looks the code up in $lookup.
    Printing (window.print) outputs only the label sheet.
    Data: $queue, $usePo, $purchases, $lookup (barcode => variant), $scanCode, $scan.
--}}
@extends('admin.layouts.app')

@section('title', 'Barcode labels')

@use('App\Support\DemoData')

@php
    $total = array_sum(array_column($queue, 'qty'));
    $stockLabel = fn (int $n) => $n === 0 ? 'Out of stock' : ($n <= 5 ? 'Low stock · '.$n.' pcs' : 'In stock · '.$n.' pcs');
    $stockTone = fn (int $n) => $n === 0 ? 'red' : ($n <= 5 ? 'amber' : 'green');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/barcodes.css') }}">
@endpush

@section('content')
<div class="page-stack page-stack--lg" x-data="barcodeLabels">
    <x-admin.page-header class="no-print" title="Barcode labels" subtitle="Pick variants, set how many labels each, then print on a thermal label printer or an A4 sticker sheet.">
        <x-slot:actions>
            <button type="button" class="btn btn--outline-strong"><x-admin.icon name="download" :size="18" />Download PDF</button>
            <button type="button" class="btn btn--brand" @click="print()" :disabled="!total">
                <x-admin.icon name="print" :size="18" /><span x-text="'Print ' + total + ' labels'">Print {{ $total }} labels</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="bc-cols">
        <div class="bc-left no-print">
            {{-- 1. Products & variants --}}
            <section class="card card--stack" aria-labelledby="h-pick">
                <div class="card-title-row">
                    <h2 id="h-pick" class="card__title">1. Products &amp; variants</h2>
                    <span class="card-meta" x-text="rows.length + ' variants · ' + total + ' labels'">{{ count($queue) }} variants · {{ $total }} labels</span>
                </div>
                <div class="suggest" @click.outside="q = ''">
                    <div class="input-group input-group--44">
                        <x-admin.icon name="search" :size="16" :stroke="2" />
                        <label for="pq" class="visually-hidden">Search product or scan barcode</label>
                        <input id="pq" type="search" autocomplete="off" placeholder="Search product name, SKU or scan barcode to add"
                            x-model="q" @keydown.enter.prevent="addFirst()" @keydown.escape="q = ''">
                    </div>
                    <ul class="suggest__list" x-show="q.trim().length > 1" x-cloak>
                        <template x-for="v in results" :key="v.code">
                            <li><button type="button" class="suggest__item" @click="add(v)">
                                <span class="photo photo--34" :style="'--tone: ' + v.tone"><img :src="v.img" alt=""></span>
                                <span><span class="fw-600" x-text="v.name"></span>
                                    <span class="media-row__meta" x-text="v.size + ' · ' + v.colour + ' · ' + v.sku + ' · ' + v.price"></span></span>
                            </button></li>
                        </template>
                        <li class="suggest__empty" x-show="!results.length">No variant matches “<span x-text="q"></span>”.</li>
                    </ul>
                </div>

                <div class="check-panel">
                    <button type="button" role="checkbox" class="check-row" aria-checked="{{ $usePo ? 'true' : 'false' }}" :aria-checked="usePO.toString()" @click="toggleUsePO()">
                        <span class="tick-box"><x-admin.icon name="check" :size="12" :stroke="4" /></span>
                        <span><span class="check-row__title">Use stock-in quantity</span><span class="check-row__note">One label per piece received in the selected purchase</span></span>
                    </button>
                    <label class="field field--muted-label">From purchase
                        <select class="select select--sm" x-model="purchase">
                            @foreach ($purchases as $po)
                                <option>{{ $po }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <ul class="bc-queue" role="list">
                    @foreach ($queue as $i => $v)
                        <li class="bc-queue__row" x-show="!rows[{{ $i }}].removed" x-data="{ i: {{ $i }} }">
                            <span class="photo" style="--tone: {{ $v['tone'] }}">@if ($v['img'])<img src="{{ $v['img'] }}" alt="{{ $v['name'] }}">@endif</span>
                            <div class="bc-queue__text">
                                <div class="fw-700 fs-14">{{ $v['name'] }}</div>
                                <div class="fs-12 muted">{{ $v['size'] }} · {{ $v['colour'] }} · <span class="mono">{{ $v['sku'] }}</span></div>
                                <div class="fs-12 muted">{{ $v['price'] }} · in stock {{ $v['stock'] }} · PO qty {{ $v['po'] }}</div>
                            </div>
                            <div class="stepper">
                                <button type="button" class="stepper__btn" aria-label="Fewer labels for {{ $v['name'] }} {{ $v['size'] }} {{ $v['colour'] }}" @click="step(i, -1)">−</button>
                                <label for="q-{{ $i }}" class="visually-hidden">Labels to print for {{ $v['name'] }} {{ $v['size'] }} {{ $v['colour'] }}</label>
                                <input id="q-{{ $i }}" class="stepper__input" type="text" inputmode="numeric" value="{{ $v['qty'] }}"
                                    :value="rows[i].qty" @input="setQty(i, $event.target.value)">
                                <button type="button" class="stepper__btn" aria-label="More labels for {{ $v['name'] }} {{ $v['size'] }} {{ $v['colour'] }}" @click="step(i, 1)">+</button>
                            </div>
                            <button type="button" class="icon-btn icon-btn--sm icon-btn--ghost" aria-label="Remove {{ $v['name'] }} {{ $v['size'] }} {{ $v['colour'] }}" @click="rows[i].removed = true">
                                <x-admin.icon name="close" :size="14" :stroke="2" />
                            </button>
                        </li>
                    @endforeach
                    {{-- Variants added from search --}}
                    <template x-for="(v, i) in rows" :key="v.code + i">
                        <li class="bc-queue__row" x-show="v.added && !v.removed">
                            <span class="photo" :style="'--tone: ' + v.tone"><img :src="v.img" :alt="v.name"></span>
                            <div class="bc-queue__text">
                                <div class="fw-700 fs-14" x-text="v.name"></div>
                                <div class="fs-12 muted"><span x-text="v.size + ' · ' + v.colour + ' · '"></span><span class="mono" x-text="v.sku"></span></div>
                                <div class="fs-12 muted" x-text="v.price + ' · in stock ' + v.stock + ' · not in purchase'"></div>
                            </div>
                            <div class="stepper">
                                <button type="button" class="stepper__btn" :aria-label="'Fewer labels for ' + label(v)" @click="step(i, -1)">−</button>
                                <input class="stepper__input" type="text" inputmode="numeric" :aria-label="'Labels to print for ' + label(v)" :value="v.qty" @input="setQty(i, $event.target.value)">
                                <button type="button" class="stepper__btn" :aria-label="'More labels for ' + label(v)" @click="step(i, 1)">+</button>
                            </div>
                            <button type="button" class="icon-btn icon-btn--sm icon-btn--ghost" :aria-label="'Remove ' + label(v)" @click="v.removed = true">
                                <x-admin.icon name="close" :size="14" :stroke="2" />
                            </button>
                        </li>
                    </template>
                    <li class="bc-queue__empty" x-show="!rows.some((r) => !r.removed)" x-cloak>No variants yet. Search or scan above to add some.</li>
                </ul>
            </section>

            {{-- 2. Label settings --}}
            <section class="card card--stack" aria-labelledby="h-set" style="gap: 14px">
                <h2 id="h-set" class="card__title">2. Label settings</h2>
                <div class="field">
                    <span id="lbl-size">Label size</span>
                    <div class="option-row" role="radiogroup" aria-labelledby="lbl-size">
                        @foreach (['small' => ['38 × 25 mm', 'Small tag, most garments'], 'large' => ['50 × 30 mm', 'Larger tag, sets & three-piece']] as $key => [$label, $note])
                            <button type="button" role="radio" class="option-tile option-tile--tall" aria-checked="{{ $key === 'small' ? 'true' : 'false' }}"
                                :aria-checked="(size === '{{ $key }}').toString()" @click="size = '{{ $key }}'">
                                <span class="option-tile__text">{{ $label }}<span class="option-tile__note">{{ $note }}</span></span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <span id="lbl-show">Show on label</span>
                    <div class="option-row" role="group" aria-labelledby="lbl-show" style="--basis: auto">
                        @foreach (['store' => 'Store name', 'name' => 'Product name', 'variant' => 'Size / colour', 'price' => 'Price'] as $key => $label)
                            <button type="button" role="checkbox" class="option-tile option-tile--compact" aria-checked="true"
                                :aria-checked="show.{{ $key }}.toString()" @click="show.{{ $key }} = !show.{{ $key }}">
                                <span class="tick-box tick-box--sm"><x-admin.icon name="check" :size="10" :stroke="4" /></span>{{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="field-row form-lg">
                    <label class="field">Barcode type
                        <select class="select" x-model="type">
                            <option>Code128</option><option>EAN-13</option><option>QR code</option>
                        </select>
                    </label>
                    <label class="field">Price to print
                        <select class="select" x-model="priceMode">
                            <option value="mrp">Selling price (MRP)</option>
                            <option value="sale">Sale price, MRP struck</option>
                        </select>
                    </label>
                </div>
            </section>

            {{-- 3. Printer --}}
            <section class="card card--stack" aria-labelledby="h-prn" style="gap: 10px">
                <h2 id="h-prn" class="card__title">3. Printer</h2>
                <div class="option-stack" role="radiogroup" aria-labelledby="h-prn">
                    @foreach (['thermal' => ['Thermal label printer', 'Roll, 2 labels across · direct thermal'], 'a4' => ['A4 sticker sheet · 40 labels', '4 across × 10 down · any laser/inkjet printer']] as $key => [$label, $note])
                        <button type="button" role="radio" class="option-tile option-tile--xtall" aria-checked="{{ $key === 'a4' ? 'true' : 'false' }}"
                            :aria-checked="(printer === '{{ $key }}').toString()" @click="printer = '{{ $key }}'">
                            <span class="radio-dot"></span>
                            <span class="option-tile__text">{{ $label }}<span class="option-tile__note">{{ $note }}</span></span>
                        </button>
                    @endforeach
                </div>
                <p class="fs-12 muted">Printer connected: [LABEL PRINTER MODEL] via USB · last used today 11:20 AM</p>
            </section>
        </div>

        <div class="bc-right">
            {{-- Live preview --}}
            <section class="card card--stack bc-preview" aria-labelledby="h-prev" style="gap: 14px">
                <div class="card-title-row card-title-row--top no-print">
                    <div>
                        <h2 id="h-prev" class="card__title">Live preview</h2>
                        <div class="card__subtitle" x-text="previewMeta">38 × 25 mm · A4 sheet, 1 page(s) · {{ $total }} labels</div>
                    </div>
                    <div class="cluster">
                        <button type="button" class="btn btn--sm btn--outline-strong">Download PDF</button>
                        <button type="button" class="btn btn--sm btn--primary" @click="print()" :disabled="!total">Print</button>
                    </div>
                </div>
                <div class="bc-stage">
                    <div class="label-sheet" :class="{ 'label-sheet--thermal': printer === 'thermal', 'label-sheet--large': size === 'large' }"
                        role="img" :aria-label="'Preview of ' + total + ' labels'" aria-label="Preview of {{ $total }} labels">
                        <template x-for="(l, i) in labels" :key="i">
                            <div class="label-tag">
                                <div>
                                    <div class="label-tag__store" x-show="show.store">YOUR BRAND</div>
                                    <div class="label-tag__name" x-show="show.name" x-text="l.name"></div>
                                    <div class="label-tag__row">
                                        <span class="label-tag__variant" x-show="show.variant" x-text="'Size ' + l.size + ' · ' + l.colour"></span>
                                        <span class="label-tag__price" x-show="show.price" x-text="l.price"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="label-tag__bars" x-show="type !== 'QR code'"></div>
                                    <div class="label-tag__qr" x-show="type === 'QR code'"></div>
                                    <div class="label-tag__code" x-text="l.code"></div>
                                </div>
                            </div>
                        </template>
                        <div class="label-sheet__empty" x-show="!labels.length" x-cloak>No labels to preview</div>
                    </div>
                </div>
                <p class="fs-13 muted text-center no-print" x-show="hiddenCount > 0" x-cloak x-text="'+ ' + hiddenCount + ' more labels not shown in preview'"></p>
            </section>

            {{-- Scan test --}}
            <section class="card card--stack no-print" aria-labelledby="h-scan">
                <div class="card-title-row">
                    <h2 id="h-scan" class="card__title">Scan test</h2>
                    <span class="card-meta">Check a printed label reads correctly</span>
                </div>
                <form class="cluster" style="--gap: 8px" @submit.prevent="lookUp()">
                    <div class="input-group input-group--scan bc-scan">
                        <x-admin.icon name="barcode" :size="20" />
                        <label for="scan" class="visually-hidden">Scan or type barcode</label>
                        <input id="scan" class="mono bc-scan__input" type="text" inputmode="numeric" autocomplete="off" x-model="scanCode" value="{{ $scanCode }}">
                    </div>
                    <button type="submit" class="btn btn--outline-strong" style="height: 48px">Look up</button>
                </form>
                <div aria-live="polite">
                    <div class="scan-result" x-show="scan" @if (! $scan) x-cloak @endif>
                        <span class="photo photo--56" :style="'--tone: ' + (scan ? scan.tone : '')" style="--tone: {{ $scan['tone'] ?? '#E5E7EA' }}">
                            <img src="{{ $scan['img'] ?? '' }}" :src="scan ? scan.img : ''" alt="{{ $scan['name'] ?? '' }}" :alt="scan ? scan.name : ''">
                        </span>
                        <div class="scan-result__text">
                            <div class="scan-result__ok"><x-admin.icon name="check" :size="14" :stroke="3" /><span x-text="'Match found · ' + scannedType + ' read OK'">Match found · Code128 read OK</span></div>
                            <div class="fw-700 fs-15" x-text="scan ? scan.name : ''">{{ $scan['name'] ?? '' }}</div>
                            <div class="fs-13 text-2"><span x-text="scan ? 'Size ' + scan.size + ' · ' + scan.colour + ' · ' : ''">Size {{ $scan['size'] ?? '' }} · {{ $scan['colour'] ?? '' }} · </span><span class="mono" x-text="scan ? scan.sku : ''">{{ $scan['sku'] ?? '' }}</span></div>
                        </div>
                        <div class="scan-result__side">
                            <div class="scan-result__price" x-text="scan ? scan.price : ''">{{ $scan['price'] ?? '' }}</div>
                            <span class="chip chip--sm {{ $scan ? 'chip--'.$stockTone($scan['stock']) : '' }}" :class="scan ? stockTone(scan.stock) : {}"
                                x-text="scan ? stockLabel(scan.stock) : ''">{{ $scan ? $stockLabel($scan['stock']) : '' }}</span>
                        </div>
                    </div>
                    <div class="alert alert--danger" role="alert" x-show="scanned && !scan" x-cloak>
                        No variant found for barcode <strong class="mono" x-text="scanCode"></strong>. Check the label print quality or the code.
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('barcodeLabels', () => ({
            rows: @js(array_map(fn ($v) => $v + ['removed' => false, 'added' => false], $queue)),
            lookup: @js($lookup),
            purchase: @js($purchases[0]),
            usePO: @js($usePo),
            size: 'small',
            printer: 'a4',
            show: { store: true, name: true, variant: true, price: true },
            type: 'Code128',
            priceMode: 'mrp',
            q: '',
            scanCode: @js($scanCode),
            scan: @js($scan),
            scanned: false,
            scannedType: 'Code128',

            label(v) { return v.name + ' ' + v.size + ' ' + v.colour; },
            get live() { return this.rows.filter((r) => !r.removed); },
            get total() { return this.live.reduce((s, r) => s + r.qty, 0); },
            step(i, d) { this.rows[i].qty = Math.max(0, this.rows[i].qty + d); this.usePO = false; },
            setQty(i, v) { this.rows[i].qty = Math.max(0, parseInt(String(v).replace(/\D/g, ''), 10) || 0); this.usePO = false; },
            toggleUsePO() {
                this.usePO = !this.usePO;
                this.rows.forEach((r) => { r.qty = this.usePO ? (r.po || 0) : 1; });
            },

            /* Search & add */
            get results() {
                const q = this.q.trim().toLowerCase();
                if (q.length < 2) return [];
                return Object.values(this.lookup).filter((v) => v.name.toLowerCase().includes(q) || v.sku.toLowerCase().includes(q) || v.code.includes(q)).slice(0, 8);
            },
            add(v) {
                const existing = this.rows.find((r) => r.code === v.code && !r.removed);
                if (existing) existing.qty += 1;
                else this.rows.push({ ...v, id: 'n' + this.rows.length, po: 0, qty: 1, removed: false, added: true });
                this.q = '';
                this.usePO = false;
            },
            addFirst() {
                const exact = this.lookup[this.q.trim()];
                if (exact) return this.add(exact);
                if (this.results.length) this.add(this.results[0]);
            },

            /* Preview */
            get cap() { return this.printer === 'thermal' ? 12 : 40; },
            get allLabels() {
                const out = [];
                this.live.forEach((r) => { for (let k = 0; k < r.qty; k++) out.push(r); });
                return out;
            },
            get labels() { return this.allLabels.slice(0, this.cap); },
            get hiddenCount() { return Math.max(0, this.allLabels.length - this.cap); },
            get previewMeta() {
                const size = this.size === 'small' ? '38 × 25 mm' : '50 × 30 mm';
                const where = this.printer === 'thermal' ? 'thermal roll' : 'A4 sheet, ' + Math.max(1, Math.ceil(this.total / 40)) + ' page(s)';
                return size + ' · ' + where + ' · ' + this.total + ' labels';
            },
            print() { window.print(); },

            /* Scan test */
            lookUp() {
                const code = this.scanCode.replace(/\s/g, '');
                this.scan = this.lookup[code] || Object.values(this.lookup).find((v) => v.sku.toLowerCase() === code.toLowerCase()) || null;
                this.scanned = true;
                this.scannedType = this.type === 'QR code' ? 'QR code' : (/^\d{13}$/.test(code) && this.type === 'EAN-13' ? 'EAN-13' : 'Code128');
            },
            stockTone(n) { return { 'chip--red': n === 0, 'chip--amber': n > 0 && n <= 5, 'chip--green': n > 5 }; },
            stockLabel(n) { return n === 0 ? 'Out of stock' : (n <= 5 ? 'Low stock · ' + n + ' pcs' : 'In stock · ' + n + ' pcs'); },
        }));
    });
</script>
@endpush
