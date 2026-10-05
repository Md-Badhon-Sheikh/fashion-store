{{--
    Flash sale — FlashSale.dc.html.
    Alpine component `flashSale` (script at the bottom):
      sel        selected campaign (cards are aria-pressed buttons); "New campaign" opens a blank draft
      lists{}    product ids per campaign — Add (search results) / Remove (trash button)
      mode       'price' (৳ sale price) | 'percent' (% off); the input edits sale[] either way
      elapsed    live countdown (seconds since page load)
    The store preview uses the first two products of the selected campaign.
--}}
@extends('admin.layouts.app')

@section('title', 'Flash sale')

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/flash-sales.css') }}">
@endpush

@php
    $cur = collect($campaigns)->firstWhere('id', $selected);
    $countdown = function (array $c): string {
        if ($c['status'] === 'ended') {
            return $c['ended_label'];
        }
        $s = $c['seconds'];
        $txt = sprintf('%dd %02dh %02dm', intdiv($s, 86400), intdiv($s % 86400, 3600), intdiv($s % 3600, 60));

        return ($c['status'] === 'running' ? 'Ends in ' : 'Starts in ').$txt;
    };
@endphp

@section('content')
<div class="fls" x-data="flashSale">

    <x-admin.page-header title="Flash sale" subtitle="Time-limited sale prices with their own stock cap · prices switch automatically at start and end time">
        <x-slot:actions>
            <button type="button" class="btn btn--brand" @click="newCampaign()">
                <x-admin.icon name="plus" :size="18" />New campaign
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Campaign picker --}}
    <section class="card fls-campaigns" aria-labelledby="fls-campaigns-title">
        <div class="fls-campaigns__head">
            <h2 id="fls-campaigns-title" class="card__title">Campaigns</h2>
            <span class="fs-13 muted">Select a campaign to edit</span>
        </div>
        <div class="fls-campaigns__grid">
            @foreach ($campaigns as $c)
                <button type="button" class="fls-campaign"
                    aria-pressed="{{ $c['id'] === $selected ? 'true' : 'false' }}" :aria-pressed="(sel === '{{ $c['id'] }}').toString()"
                    @click="sel = '{{ $c['id'] }}'">
                    <span class="fls-campaign__top">
                        <span class="fls-campaign__name">{{ $c['name'] }}</span>
                        <x-admin.status-chip :status="$c['status']" size="sm" />
                    </span>
                    <span class="fs-13 muted">{{ $c['range'] }}</span>
                    <span class="fls-campaign__foot">
                        <span><span x-text="lists['{{ $c['id'] }}'].length">{{ count($c['products']) }}</span> products · {{ DemoData::number($c['sold']) }} sold</span>
                        <span @class(['fls-campaign__cd', 'is-live' => $c['status'] === 'running']) x-text="countdownText('{{ $c['id'] }}')">{{ $countdown($c) }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    <div class="row-wrap fls-main">
        {{-- Campaign editor --}}
        <section class="card col-main fls-editor" aria-labelledby="fls-editor-title">
            <div class="fls-editor__head">
                <div class="cluster" style="--gap: 10px">
                    <h2 id="fls-editor-title" class="fls-editor__title" x-text="sel === 'new' ? 'New campaign' : 'Edit campaign'">Edit campaign</h2>
                    <span class="chip chip--{{ Status::tone($cur['status']) }}" :class="'chip--' + tone(cur().status)" x-text="statusLabel(cur().status)">{{ Status::label($cur['status']) }}</span>
                    <span class="fs-13 muted tabular" x-text="countdownText(sel)">{{ $countdown($cur) }}</span>
                </div>
                <div class="cluster">
                    <button type="button" class="btn btn--sm fls-btn-stop" x-text="stopLabel()">End sale now</button>
                    <button type="button" class="btn btn--sm btn--brand fls-btn-save">Save changes</button>
                </div>
            </div>

            <div class="form-grid" style="--min: 220px; gap: 14px">
                <label class="field text-2">Campaign name *
                    <input type="text" class="input fls-input" value="{{ $cur['name'] }}" :value="cur().name" placeholder="e.g. Pohela Boishakh Flash">
                </label>
                <label class="field text-2">Starts *
                    <input type="datetime-local" class="input fls-input" value="{{ $cur['start'] }}" :value="cur().start">
                </label>
                <label class="field text-2">Ends *
                    <input type="datetime-local" class="input fls-input" value="{{ $cur['end'] }}" :value="cur().end">
                </label>
            </div>

            <div class="row-wrap" style="--gap: 16px">
                <div class="fls-banner-field">
                    <span class="field__label text-2">Banner image</span>
                    <div class="fls-banner" style="--tone: {{ $banner['tone'] }}">
                        <span>Banner photo · 1600 × 500 px</span>
                        <img src="{{ $banner['img'] }}" alt="" :alt="(cur().name || 'Flash sale') + ' banner'" loading="lazy">
                    </div>
                    <div class="cluster">
                        <button type="button" class="btn btn--xs btn--outline-strong">Replace image</button>
                        <button type="button" class="btn btn--xs btn--outline-strong">Mobile version</button>
                        <span class="fs-12 muted">JPG or WebP, under 400 KB</span>
                    </div>
                </div>
                <fieldset class="fls-show-on">
                    <legend>Show on</legend>
                    <label class="check"><input type="checkbox" checked>Home page countdown strip</label>
                    <label class="check"><input type="checkbox" checked>Flash sale page</label>
                    <label class="check"><input type="checkbox" checked>Product page badge</label>
                    <label class="check"><input type="checkbox">Also apply price in POS</label>
                </fieldset>
            </div>

            {{-- Products in the sale --}}
            <div class="stack" style="--gap: 10px">
                <div class="split">
                    <h3 class="fls-h3">Products in this sale (<span x-text="rows().length">{{ count($cur['products']) }}</span>)</h3>
                    <div class="fls-mode">
                        <span id="fls-mode-l">Set discount by</span>
                        <div role="radiogroup" aria-labelledby="fls-mode-l" class="fls-seg">
                            <button type="button" role="radio" class="fls-seg__btn" aria-checked="true" :aria-checked="(mode === 'price').toString()"
                                :tabindex="mode === 'price' ? 0 : -1" @click="mode = 'price'" @keydown.arrow-right.prevent="mode = 'percent'; $nextTick(() => $el.nextElementSibling.focus())">Sale price (৳)</button>
                            <button type="button" role="radio" class="fls-seg__btn" aria-checked="false" :aria-checked="(mode === 'percent').toString()"
                                tabindex="-1" :tabindex="mode === 'percent' ? 0 : -1" @click="mode = 'percent'" @keydown.arrow-left.prevent="mode = 'price'; $nextTick(() => $el.previousElementSibling.focus())">% off</button>
                        </div>
                    </div>
                </div>

                <div class="fls-add">
                    <form class="fls-add__bar" role="search" @submit.prevent="addAll()">
                        <label class="input-group input-group--lg fls-add__search">
                            <span class="visually-hidden">Search products to add</span>
                            <x-admin.icon name="search" :size="16" :stroke="2" />
                            <input type="search" placeholder="Search by product name or SKU" x-model="q" value="kurti" autocomplete="off">
                        </label>
                        <button type="submit" class="btn btn--outline-brand">Add products</button>
                    </form>
                    <div class="fls-results" x-show="results().length" x-cloak>
                        <span class="fs-12 muted" id="fls-results-l">Search results</span>
                        <ul class="fls-results__list" role="list" aria-labelledby="fls-results-l">
                            <template x-for="r in results()" :key="r.key">
                                <li class="fls-result">
                                    <span class="thumb fls-thumb-xs" :style="'--tone:' + r.tone"><img :src="r.img" :alt="r.name" loading="lazy"></span>
                                    <span class="fls-result__text"><span class="fw-600" x-text="r.name"></span><span class="muted" x-text="' · ' + r.sku + ' · ' + money(r.reg) + ' · ' + r.stock + ' in stock'"></span></span>
                                    <button type="button" class="btn btn--xs btn--brand" @click="add(r.key)" :aria-label="'Add ' + r.name">Add</button>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <p class="fs-12 muted" x-show="q.trim() && !results().length" x-cloak>No other products match “<span x-text="q.trim()"></span>”.</p>
                </div>

                <div class="table-card fls-table-card">
                    <table class="table fls-table" style="--table-min: 860px">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Variant</th>
                                <th scope="col" class="num">Regular</th>
                                <th scope="col" x-text="mode === 'price' ? 'Sale price' : '% off'">Sale price</th>
                                <th scope="col">Sale stock limit</th>
                                <th scope="col">Sold</th>
                                <th scope="col"><span class="visually-hidden">Remove</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="p in rows()" :key="p.key">
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="thumb fls-thumb" :style="'--tone:' + p.tone"><img :src="p.img" :alt="p.name" loading="lazy"></span>
                                            <span><span class="product-cell__name" x-text="p.name"></span><span class="product-cell__meta" x-text="'SKU ' + p.sku"></span></span>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="select select--xs fls-variant" :aria-label="'Variant for ' + p.name">
                                            <option x-text="p.variant"></option>
                                            <option>Choose sizes…</option>
                                        </select>
                                    </td>
                                    <td class="num muted" x-text="money(p.reg)"></td>
                                    <td>
                                        <div class="fls-price">
                                            <span class="fls-price__box">
                                                <span class="fls-price__affix" x-show="mode === 'price'">৳</span>
                                                <input type="text" inputmode="numeric" :aria-label="(mode === 'price' ? 'Sale price' : 'Percent off') + ' for ' + p.name"
                                                    :value="inputValue(p.key)" @change="setInput(p.key, $event.target.value); $event.target.value = inputValue(p.key)">
                                                <span class="fls-price__affix fls-price__affix--end" x-show="mode === 'percent'">%</span>
                                            </span>
                                            <span class="fls-price__derived" x-text="derived(p.key)"></span>
                                        </div>
                                    </td>
                                    <td class="nowrap">
                                        <input type="text" inputmode="numeric" class="input input--xs fls-limit" :aria-label="'Sale stock limit for ' + p.name"
                                            :value="limit[p.key]" @change="setLimit(p.key, $event.target.value); $event.target.value = limit[p.key]">
                                        <span class="fs-12 muted fls-of" x-text="'of ' + p.stock"></span>
                                    </td>
                                    <td class="fls-sold">
                                        <template x-if="soldFor(p.key) >= limit[p.key]">
                                            <span class="chip chip--red chip--sm">Sale stock used up</span>
                                        </template>
                                        <template x-if="soldFor(p.key) < limit[p.key]">
                                            <div>
                                                <div class="fw-600"><span x-text="soldFor(p.key)"></span> <span class="muted fw-500" x-text="'/ ' + limit[p.key]"></span></div>
                                                <div class="fls-meter" role="presentation"><div class="fls-meter__fill" :style="'width:' + pctSold(p.key) + '%'"></div></div>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="cell-actions">
                                        <button type="button" class="icon-btn icon-btn--sm fls-remove" :aria-label="'Remove ' + p.name + ' from sale'" @click="remove(p.key)">
                                            <x-admin.icon name="trash" :size="16" :stroke="2" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <x-admin.empty-state x-show="!rows().length" x-cloak icon="flash" title="No products in this campaign" message="Search above and add products to put them on flash sale." />
                </div>
                <p class="fs-12 muted">When the sale stock limit is reached the product returns to its regular price. Sale price must be below the regular price and above the purchase cost.</p>
            </div>
        </section>

        {{-- Store preview --}}
        <aside class="col-side fls-preview" aria-labelledby="fls-preview-title">
            <div class="split split--baseline">
                <h2 id="fls-preview-title" class="fls-preview__title">Store preview</h2>
                <a class="card__link" href="{{ DemoData::storeUrl() }}" target="_blank" rel="noopener">Open store →</a>
            </div>
            <div class="card fls-preview__card">
                <div class="fls-banner fls-banner--sm" style="--tone: {{ $banner['tone'] }}">
                    <span>Banner photo</span>
                    <img src="{{ $banner['img'] }}" alt="" :alt="(cur().name || 'Flash sale') + ' banner'" loading="lazy">
                </div>
                <div class="fls-strip">
                    <div>
                        <div class="fls-strip__title">Flash Sale</div>
                        <div class="fs-12 fls-strip__name" x-text="cur().name || 'New campaign'">{{ $cur['name'] }}</div>
                    </div>
                    <div class="fls-strip__cd">
                        <span class="fls-strip__cd-label" x-text="cdLabel()">Ends in</span>
                        <div class="fls-cd" role="timer" aria-live="off">
                            <template x-for="b in cdBoxes()" :key="b.u">
                                <span class="fls-cd__box"><span x-text="b.v"></span><span class="fls-cd__unit" x-text="b.u"></span></span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="fls-preview__grid">
                    <template x-for="p in rows().slice(0, 2)" :key="p.key">
                        <div class="fls-pcard">
                            <div class="fls-pcard__img" :style="'--tone:' + p.tone">
                                <span>Product photo</span>
                                <img :src="p.img" :alt="p.name" loading="lazy">
                                <span class="fls-pcard__badge" x-text="'−' + pct(p.key) + '%'"></span>
                            </div>
                            <div class="fls-pcard__name" x-text="p.name"></div>
                            <div class="fls-pcard__price"><span class="fls-pcard__sale" x-text="money(sale[p.key])"></span><s class="fls-pcard__reg" x-text="money(p.reg)"></s></div>
                            <div class="fls-pcard__bar" role="presentation"><div :style="'width:' + previewPct(p.key) + '%'"></div></div>
                            <div class="fls-pcard__left" x-text="leftText(p.key)"></div>
                        </div>
                    </template>
                </div>
                <p class="fs-13 muted" x-show="!rows().length" x-cloak>Add products to see them in the preview.</p>
            </div>
            <p class="fs-12 muted">Preview uses the first two products in the list. Prices shown to customers update as you edit.</p>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const P = @js($products);
    const CAMPAIGNS = @js($campaigns);
    const TONES = @js(Status::toneMap());
    const NEW = { id: 'new', name: '', range: '', start: '', end: '', status: 'draft', seconds: 0, ended_label: 'Not scheduled yet', sold: 0, products: [] };
    const byId = Object.fromEntries(CAMPAIGNS.map((c) => [c.id, c]));
    const num = (s) => parseFloat(String(s).replace(/[^0-9.]/g, '')) || 0;
    const fmt = (n) => new Intl.NumberFormat('en-IN').format(Math.round(n));
    const pad = (n) => String(n).padStart(2, '0');

    Alpine.data('flashSale', () => ({
        sel: @js($selected),
        mode: 'price',
        q: 'kurti',
        elapsed: 0,
        lists: Object.fromEntries(CAMPAIGNS.map((c) => [c.id, c.products.slice()]).concat([['new', []]])),
        sale: Object.fromEntries(Object.entries(P).map(([k, p]) => [k, p.sale])),
        limit: Object.fromEntries(Object.entries(P).map(([k, p]) => [k, p.limit])),

        init() {
            const timer = setInterval(() => { this.elapsed += 1; }, 1000);
            window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
        },

        // ----- campaigns -----
        cur() { return this.sel === 'new' ? NEW : byId[this.sel]; },
        campaign(id) { return id === 'new' ? NEW : byId[id]; },
        tone(status) { return TONES[status] || 'gray'; },
        statusLabel(s) { return s.charAt(0).toUpperCase() + s.slice(1); },
        left(id) { return Math.max(0, this.campaign(id).seconds - this.elapsed); },
        countdownText(id) {
            const c = this.campaign(id);
            if (c.status === 'ended' || c.status === 'draft') return c.ended_label;
            const s = this.left(id);
            const txt = Math.floor(s / 86400) + 'd ' + pad(Math.floor(s % 86400 / 3600)) + 'h ' + pad(Math.floor(s % 3600 / 60)) + 'm';
            return (c.status === 'running' ? 'Ends in ' : 'Starts in ') + txt;
        },
        cdLabel() {
            const s = this.cur().status;
            return s === 'running' ? 'Ends in' : s === 'scheduled' ? 'Starts in' : s === 'draft' ? 'Not scheduled' : 'Sale ended';
        },
        cdBoxes() {
            const s = this.cur().status === 'ended' || this.cur().status === 'draft' ? 0 : this.left(this.sel);
            const v = [Math.floor(s / 86400), Math.floor(s % 86400 / 3600), Math.floor(s % 3600 / 60), s % 60];
            return ['Days', 'Hrs', 'Min', 'Sec'].map((u, i) => ({ u, v: pad(v[i]) }));
        },
        stopLabel() {
            return { running: 'End sale now', scheduled: 'Cancel campaign', ended: 'Delete campaign', draft: 'Discard draft' }[this.cur().status];
        },
        newCampaign() { this.sel = 'new'; this.lists.new = []; },

        // ----- products -----
        rows() { return this.lists[this.sel].map((k) => ({ key: k, ...P[k] })); },
        money(n) { return '৳' + fmt(n); },
        pct(k) { return Math.round((1 - this.sale[k] / P[k].reg) * 100); },
        inputValue(k) { return this.mode === 'price' ? fmt(this.sale[k]) : String(this.pct(k)); },
        derived(k) { return this.mode === 'price' ? this.pct(k) + '% off' : '= ' + this.money(this.sale[k]); },
        setInput(k, raw) {
            const v = num(raw);
            const reg = P[k].reg;
            const next = this.mode === 'price' ? v : Math.round(reg * (1 - Math.min(v, 99) / 100));
            if (next > 0 && next < reg) this.sale[k] = Math.round(next);
        },
        setLimit(k, raw) { const v = Math.round(num(raw)); if (v > 0) this.limit[k] = v; },
        soldFor(k) {
            const s = this.cur().status;
            if (s === 'ended') return Math.min(this.limit[k], Math.round(this.limit[k] * 0.8));
            return s === 'running' ? P[k].sold : 0;
        },
        pctSold(k) { return Math.min(100, Math.round(this.soldFor(k) / this.limit[k] * 100)); },
        previewPct(k) { return this.pctSold(k); },
        leftText(k) {
            const sold = this.soldFor(k);
            const lim = this.limit[k];
            return sold >= lim ? 'Sold out at sale price' : sold + ' sold · ' + (lim - sold) + ' left at this price';
        },

        // ----- add / remove -----
        results() {
            const q = this.q.trim().toLowerCase();
            if (!q) return [];
            const inList = this.lists[this.sel];
            return Object.entries(P)
                .filter(([k, p]) => !inList.includes(k) && [p.name, p.sku, p.category, p.tags].join(' ').toLowerCase().includes(q))
                .slice(0, 3)
                .map(([k, p]) => ({ key: k, ...p }));
        },
        add(k) { if (!this.lists[this.sel].includes(k)) this.lists[this.sel].push(k); },
        addAll() { this.results().forEach((r) => this.add(r.key)); },
        remove(k) { this.lists[this.sel] = this.lists[this.sel].filter((x) => x !== k); },
    }));
});
</script>
@endpush
