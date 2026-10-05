{{--
    Coupons — Coupons.dc.html.
    Alpine component `couponsPage` (script at the bottom):
      tab / q / channel   filter the coupon list (table on desktop, cards on phones < 640px)
      off{code}           on/off switch per coupon; a paused, non-expired coupon shows "Inactive"
      type/code/value/…   create form: % / ৳ switch, Generate code, live checkout preview
--}}
@extends('admin.layouts.app')

@section('title', 'Coupons')

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/coupons.css') }}">
@endpush

@php
    $digits = fn (string $s) => (int) preg_replace('/\D/', '', $s);
    $js = [
        'coupons' => array_map(fn ($c) => [
            'code' => $c['code'],
            'status' => $c['status'],
            'channel' => $c['channel'],
            'enabled' => $c['enabled'],
            'type' => $c['type'] === 'Percentage' ? 'percent' : 'fixed',
            'value' => (string) $digits($c['value']),
            'cap' => $c['cap'] ? DemoData::number($digits($c['cap'])) : '',
            'min' => DemoData::number($digits($c['min'])),
            'limit' => (string) $c['limit'],
            'per' => (string) $c['per_customer'],
        ], $coupons),
        'codes' => $codes,
        'types' => $types,
        'cart' => $cart,
        'tones' => Status::toneMap(),
    ];
    $tabs = ['all' => 'All', 'active' => 'Active', 'scheduled' => 'Scheduled', 'expired' => 'Expired'];
    $shown = fn ($c) => ! $c['enabled'] && $c['status'] !== 'expired' ? 'inactive' : $c['status'];
    $initialCount = fn ($key) => $key === 'all' ? count($coupons) : count(array_filter($coupons, fn ($c) => $shown($c) === $key));
@endphp

@section('content')
<div class="cpn" x-data="couponsPage">

    <x-admin.page-header title="Coupons" subtitle="Discount codes for the online store and POS · customers enter the code at checkout or staff apply it at the till">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">
                <x-admin.icon name="download" :size="18" />Export usage
            </button>
            <a class="btn btn--brand" href="#create" @click="startNew()">
                <x-admin.icon name="plus" :size="18" />Create coupon
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="kpi-grid">
        @foreach ($kpis as $k)
            <x-admin.kpi :label="$k['label']" :value="$k['value']" :sub="$k['sub']" class="cpn-kpi" />
        @endforeach
    </div>

    {{-- Coupon list --}}
    <section class="card card--flush cpn-list" aria-labelledby="cpn-list-title">
        <h2 id="cpn-list-title" class="visually-hidden">All coupons</h2>
        <div class="cpn-list__head">
            {{-- Desktop: underlined tabs. Phone: scrolling pill row. --}}
            <div class="tabs cpn-tabs hide-sm" role="tablist" aria-label="Coupon status">
                @foreach ($tabs as $key => $label)
                    <button type="button" role="tab" class="tab"
                        aria-selected="{{ $key === 'all' ? 'true' : 'false' }}" :aria-selected="(tab === '{{ $key }}').toString()"
                        @click="tab = '{{ $key }}'">
                        {{ $label }} (<span x-text="count('{{ $key }}')">{{ $initialCount($key) }}</span>)
                    </button>
                @endforeach
            </div>
            <div class="pill-tabs show-sm" role="tablist" aria-label="Coupon status">
                @foreach ($tabs as $key => $label)
                    <button type="button" role="tab" class="pill-tab"
                        aria-selected="{{ $key === 'all' ? 'true' : 'false' }}" :aria-selected="(tab === '{{ $key }}').toString()"
                        @click="tab = '{{ $key }}'">
                        {{ $label }}<span class="pill-tab__count" x-text="count('{{ $key }}')">{{ $initialCount($key) }}</span>
                    </button>
                @endforeach
            </div>

            <div class="cpn-filters">
                <label class="input-group cpn-search">
                    <span class="visually-hidden">Search coupon code</span>
                    <x-admin.icon name="search" :size="16" />
                    <input type="search" placeholder="Search code" x-model.trim="q" autocomplete="off">
                </label>
                <label class="cpn-channel">
                    <span class="visually-hidden">Channel</span>
                    <select class="select select--sm" x-model="channel">
                        <option value="all">All channels</option>
                        <option value="online">Online only</option>
                        <option value="pos">POS only</option>
                    </select>
                </label>
            </div>
        </div>

        {{-- Desktop / tablet table --}}
        <div class="table-wrap hide-sm">
            <table class="table cpn-table" style="--table-min: 1180px">
                <thead>
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Type</th>
                        <th scope="col" class="num">Value</th>
                        <th scope="col" class="num">Min order</th>
                        <th scope="col">Usage</th>
                        <th scope="col" class="num">Per customer</th>
                        <th scope="col">Applies to</th>
                        <th scope="col">Valid from – to</th>
                        <th scope="col">Status</th>
                        <th scope="col">On / off</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($coupons as $c)
                        @php($code = $c['code'])
                        <tr x-show="visible('{{ $code }}')">
                            <td>
                                <span class="coupon-code">{{ $code }}</span>
                                <span class="cell-sub cpn-channels">{{ $c['channels'] }}</span>
                            </td>
                            <td>{{ $c['type'] }}</td>
                            <td class="num cell-strong">
                                {{ $c['value'] }}
                                @if ($c['cap'])<span class="cell-sub fw-500">{{ $c['cap'] }}</span>@endif
                            </td>
                            <td class="num">{{ $c['min'] }}</td>
                            <td class="cpn-usage">
                                <div class="fw-600">{{ $c['used_label'] }} <span class="muted fw-500">/ {{ $c['limit_label'] }}</span></div>
                                <div class="meter" role="presentation"><div class="meter__fill" style="width: {{ $c['usage_pct'] }}%"></div></div>
                            </td>
                            <td class="num">{{ $c['per_customer'] }}</td>
                            <td><span class="cell-sub">{{ $c['scope'] }}</span>{{ $c['target'] }}</td>
                            <td class="nowrap">{{ $c['from'] }}<span class="cell-sub">to {{ $c['to'] }}</span></td>
                            <td>
                                <span class="chip chip--{{ Status::tone($shown($c)) }}" :class="chipClass('{{ $code }}')" x-text="label('{{ $code }}')">{{ Status::label($shown($c)) }}</span>
                            </td>
                            <td>
                                <button type="button" role="switch" class="switch switch--lg" aria-label="Enable {{ $code }}"
                                    aria-checked="{{ $c['enabled'] ? 'true' : 'false' }}" :aria-checked="(!off['{{ $code }}']).toString()"
                                    @click="toggle('{{ $code }}')"></button>
                            </td>
                            <td class="cell-actions">
                                <a class="cpn-action" href="#create" @click="edit('{{ $code }}')">Edit</a>
                                <a class="cpn-action cpn-action--2" href="#create" @click="duplicate('{{ $code }}')">Duplicate</a>
                                <a class="cpn-action cpn-action--muted" href="{{ route('admin.reports') }}">Usage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Phone: coupons as cards --}}
        <ul class="cpn-cards show-sm" role="list">
            @foreach ($coupons as $c)
                @php($code = $c['code'])
                <li class="list-card cpn-card" x-show="visible('{{ $code }}')">
                    <div class="list-card__row">
                        <span class="coupon-code">{{ $code }}</span>
                        <span class="chip chip--{{ Status::tone($shown($c)) }}" :class="chipClass('{{ $code }}')" x-text="label('{{ $code }}')">{{ Status::label($shown($c)) }}</span>
                    </div>
                    <div class="list-card__meta">{{ $c['channels'] }} · {{ $c['target'] }}</div>
                    <div class="cpn-card__facts">
                        <span><span class="muted">{{ $c['type'] }}</span> <strong>{{ $c['value'] }}</strong>@if ($c['cap']) <span class="muted">({{ $c['cap'] }})</span>@endif</span>
                        <span><span class="muted">Min</span> {{ $c['min'] }}</span>
                    </div>
                    <div class="cpn-card__usage">
                        <span class="fs-13"><span class="fw-600">{{ $c['used_label'] }}</span> <span class="muted">/ {{ $c['limit_label'] }} used</span></span>
                        <div class="meter" role="presentation"><div class="meter__fill" style="width: {{ $c['usage_pct'] }}%"></div></div>
                    </div>
                    <div class="list-card__meta">{{ $c['from'] }} – {{ $c['to'] }}</div>
                    <div class="list-card__foot">
                        <span class="cluster" style="--gap: 16px">
                            <a class="cpn-action" href="#create" @click="edit('{{ $code }}')">Edit</a>
                            <a class="cpn-action cpn-action--2" href="#create" @click="duplicate('{{ $code }}')">Duplicate</a>
                        </span>
                        <button type="button" role="switch" class="switch switch--lg" aria-label="Enable {{ $code }}"
                            aria-checked="{{ $c['enabled'] ? 'true' : 'false' }}" :aria-checked="(!off['{{ $code }}']).toString()"
                            @click="toggle('{{ $code }}')"></button>
                    </div>
                </li>
            @endforeach
        </ul>

        <x-admin.empty-state x-show="visibleCount() === 0" x-cloak icon="coupon" title="No coupons match" message="Try another status tab, code or channel." />

        <div class="cpn-list__foot">
            <span>Showing <span x-text="visibleCount()">{{ count($coupons) }}</span> of {{ count($coupons) }} coupons · usage counts confirmed orders only (cancelled orders release the use)</span>
            <span>Status is automatic from dates; the switch pauses a coupon without deleting it</span>
        </div>
    </section>

    {{-- Create coupon + checkout preview --}}
    <div id="create" class="row-wrap cpn-create">
        <form class="card col-main cpn-form" @submit.prevent aria-labelledby="cpn-form-title">
            <div class="card__head cpn-form__head">
                <h2 id="cpn-form-title" class="cpn-form__title" x-text="editing ? 'Edit coupon' : 'Create coupon'">Create coupon</h2>
                <span class="fs-13 muted">Fields marked * are required</span>
            </div>

            <div class="stack" style="--gap: 18px">
                <div class="field cpn-code-field">
                    <label for="cp-code" class="field__label text-2">Coupon code *</label>
                    <div class="cpn-code-row">
                        <input id="cp-code" type="text" class="input cpn-code-input" x-model="code" value="{{ $codes[0] }}"
                            maxlength="16" autocomplete="off" spellcheck="false" aria-describedby="cp-code-hint" x-ref="code">
                        <button type="button" class="btn btn--outline-brand" @click="generate()">
                            <x-admin.icon name="refresh" :size="16" :stroke="2" />Generate
                        </button>
                    </div>
                    <span id="cp-code-hint" class="field__hint">Letters and numbers only, 4–16 characters. Not case-sensitive for customers.</span>
                </div>

                <div class="row-wrap" style="--gap: 14px">
                    <div class="field cpn-type-field">
                        <span id="cp-type-l" class="field__label text-2">Discount type *</span>
                        <div role="radiogroup" aria-labelledby="cp-type-l" class="cpn-types">
                            @foreach ($types as $key => $t)
                                <button type="button" role="radio" class="choice"
                                    aria-checked="{{ $key === 'percent' ? 'true' : 'false' }}" :aria-checked="(type === '{{ $key }}').toString()"
                                    @click="setType('{{ $key }}')" @keydown.arrow-right.prevent="setType('fixed')" @keydown.arrow-left.prevent="setType('percent')"
                                    tabindex="{{ $key === 'percent' ? '0' : '-1' }}" :tabindex="type === '{{ $key }}' ? 0 : -1">
                                    <span class="choice__dot" aria-hidden="true"></span>
                                    <span>{{ $t['label'] }}<span class="choice__hint">{{ $t['hint'] }}</span></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="field cpn-value-field">
                        <label for="cp-val" class="field__label text-2">Discount value *</label>
                        <div class="addon-input">
                            <input id="cp-val" type="text" inputmode="decimal" x-model="value" value="{{ $types['percent']['default'] }}" aria-describedby="cp-val-hint">
                            <span class="addon-input__suffix" x-text="types[type].suffix">{{ $types['percent']['suffix'] }}</span>
                        </div>
                        <span id="cp-val-hint" class="field__hint" x-text="types[type].value_hint">{{ $types['percent']['value_hint'] }}</span>
                    </div>
                </div>

                <div class="form-grid" style="--min: 200px; gap: 14px">
                    <label class="field text-2">Minimum order amount
                        <span class="addon-input"><span class="addon-input__prefix">৳</span><input type="text" inputmode="numeric" x-model="min" value="2,000"></span>
                    </label>
                    <label class="field text-2" x-show="type === 'percent'">Maximum discount cap
                        <span class="addon-input"><span class="addon-input__prefix">৳</span><input type="text" inputmode="numeric" x-model="cap" value="500"></span>
                    </label>
                    <label class="field text-2">Total usage limit
                        <input type="text" inputmode="numeric" class="input cpn-input" x-model="limit" value="300">
                    </label>
                    <label class="field text-2">Limit per customer
                        <input type="text" inputmode="numeric" class="input cpn-input" x-model="per" value="1">
                    </label>
                </div>

                <div class="form-grid" style="--min: 220px; gap: 14px">
                    <label class="field text-2">Applies to *
                        <select class="select cpn-input">
                            <option>All products</option>
                            <option selected>Specific categories</option>
                            <option>Specific products</option>
                        </select>
                    </label>
                    <label class="field text-2">Categories
                        <select class="select cpn-input">
                            <option>Women › Three-Piece, Women › Kurti</option>
                            <option>Men › Panjabi</option>
                            <option>Kids</option>
                        </select>
                    </label>
                    <label class="field text-2">Starts *
                        <input type="datetime-local" class="input cpn-input" value="2026-10-15T00:00">
                    </label>
                    <label class="field text-2">Ends *
                        <input type="datetime-local" class="input cpn-input" value="2026-10-25T23:59">
                    </label>
                </div>

                <fieldset class="cpn-channels-set">
                    <legend>Channels *</legend>
                    <label class="check"><input type="checkbox" checked>Online store (checkout)</label>
                    <label class="check"><input type="checkbox" checked>POS (in-store till)</label>
                    <label class="check"><input type="checkbox">First order only</label>
                    <label class="check"><input type="checkbox">Exclude items already on flash sale</label>
                </fieldset>

                <div class="cpn-form__actions">
                    <button type="button" class="btn btn--outline" @click="startNew()">Cancel</button>
                    <button type="button" class="btn btn--outline">Save as inactive</button>
                    <button type="submit" class="btn btn--brand cpn-save">Save coupon</button>
                </div>
            </div>
        </form>

        <aside class="col-side stack" aria-label="Checkout preview">
            <section class="card card--stack" aria-labelledby="cpn-preview-title">
                <h2 id="cpn-preview-title" class="cpn-side-title">How it applies</h2>
                <div class="fs-13 muted">Example cart: {{ $cart['label'] }}</div>
                <div class="cpn-preview" aria-live="polite">
                    <div class="kv"><span>Subtotal</span><span class="tabular">{{ DemoData::money($cart['subtotal']) }}</span></div>
                    <div class="kv cpn-preview__discount"><span>Coupon <span class="mono cpn-preview__code" x-text="code.toUpperCase()">{{ $codes[0] }}</span></span><span class="tabular" x-text="'−' + money(discount())">−৳500</span></div>
                    <div class="kv muted"><span>{{ $cart['delivery_label'] }}</span><span class="tabular">{{ DemoData::money($cart['delivery']) }}</span></div>
                    <div class="kv cpn-preview__total"><span>Total</span><span class="tabular" x-text="money(cart.subtotal - discount() + cart.delivery)">{{ DemoData::money($cart['subtotal'] - 500 + $cart['delivery']) }}</span></div>
                </div>
                <div class="cpn-note" x-text="note()">20% of ৳4,340 = ৳868, capped at the ৳500 maximum discount. Delivery charge is never discounted.</div>
            </section>

            <section class="card card--stack cpn-errors" aria-labelledby="cpn-errors-title">
                <h2 id="cpn-errors-title" class="cpn-side-title">Error messages customers see</h2>
                <ul class="cpn-errors__list" role="list">
                    @foreach ($customerErrors as $e)
                        <li>{{ $e[0] }} — {{ $e[1] }}</li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const DATA = @js($js);
    const byCode = Object.fromEntries(DATA.coupons.map((c) => [c.code, c]));
    const num = (s) => parseFloat(String(s).replace(/[^0-9.]/g, '')) || 0;
    const fmt = (n) => new Intl.NumberFormat('en-IN').format(Math.round(n));

    Alpine.data('couponsPage', () => ({
        types: DATA.types,
        cart: DATA.cart,
        tab: 'all',
        q: '',
        channel: 'all',
        off: Object.fromEntries(DATA.coupons.map((c) => [c.code, !c.enabled])),

        // ----- list -----
        shown(code) {
            const c = byCode[code];
            return this.off[code] && c.status !== 'expired' ? 'inactive' : c.status;
        },
        label(code) { const s = this.shown(code); return s.charAt(0).toUpperCase() + s.slice(1); },
        chipClass(code) { return 'chip--' + (DATA.tones[this.shown(code)] || 'gray'); },
        toggle(code) { this.off[code] = !this.off[code]; },
        count(key) {
            return key === 'all' ? DATA.coupons.length : DATA.coupons.filter((c) => this.shown(c.code) === key).length;
        },
        visible(code) {
            const c = byCode[code];
            if (this.tab !== 'all' && this.shown(code) !== this.tab) return false;
            if (this.channel !== 'all' && c.channel !== this.channel) return false;
            return !this.q || code.toLowerCase().includes(this.q.toLowerCase());
        },
        visibleCount() { return DATA.coupons.filter((c) => this.visible(c.code)).length; },

        // ----- create / edit form -----
        editing: false,
        codeIdx: 0,
        code: DATA.codes[0],
        type: 'percent',
        value: DATA.types.percent.default,
        min: '2,000',
        cap: '500',
        limit: '300',
        per: '1',
        setType(t) { if (this.type !== t) { this.type = t; this.value = DATA.types[t].default; } },
        generate() { this.codeIdx += 1; this.code = DATA.codes[this.codeIdx % DATA.codes.length]; },
        load(c, code) {
            this.type = c.type; this.value = c.value; this.min = c.min; this.cap = c.cap || '500';
            this.limit = c.limit === '0' ? '' : c.limit; this.per = c.per; this.code = code;
        },
        edit(code) { this.editing = true; this.load(byCode[code], code); },
        duplicate(code) {
            this.editing = false; this.load(byCode[code], '');
            this.$nextTick(() => this.$refs.code.focus());
        },
        startNew() {
            this.editing = false; this.codeIdx = 0; this.code = DATA.codes[0];
            this.type = 'percent'; this.value = DATA.types.percent.default;
            this.min = '2,000'; this.cap = '500'; this.limit = '300'; this.per = '1';
        },

        // ----- checkout preview -----
        money(n) { return '৳' + fmt(n); },
        rawDiscount() {
            const v = num(this.value);
            return this.type === 'percent' ? Math.round(this.cart.subtotal * v / 100) : v;
        },
        discount() {
            if (this.cart.subtotal < num(this.min)) return 0;
            const raw = this.rawDiscount();
            const cap = num(this.cap);
            const d = this.type === 'percent' && cap ? Math.min(cap, raw) : raw;
            return Math.min(d, this.cart.subtotal);
        },
        note() {
            const tail = ' Delivery charge is never discounted.';
            const min = num(this.min);
            if (this.cart.subtotal < min) {
                return 'The example subtotal is below the ' + this.money(min) + ' minimum, so the customer sees "Add ' + this.money(min - this.cart.subtotal) + ' more to use this coupon".' + tail;
            }
            if (this.type === 'percent') {
                const raw = this.rawDiscount();
                const cap = num(this.cap);
                const base = num(this.value) + '% of ' + this.money(this.cart.subtotal) + ' = ' + this.money(raw);
                return (cap && raw > cap ? base + ', capped at the ' + this.money(cap) + ' maximum discount.' : base + '.') + tail;
            }
            return 'Flat ' + this.money(num(this.value)) + ' off because the subtotal is above the ' + this.money(min) + ' minimum.' + tail;
        },
    }));
});
</script>
@endpush
