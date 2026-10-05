{{--
    POS counter — POS.dc.html (desktop, >= 1200px) + T-POS.dc.html (tablet, < 1200px).
    The admin topbar is replaced by the dark POS bar (css/pages/pos.css); the sidebar stays
    (fixed >= 1024px, drawer below via the bar's menu button).
    Layout:  >= 1200  page scroll, products + 400px sale panel
             768–1199 full-height app, products grid and cart lines scroll inside
             < 768    stacked (products, then the sale panel)
    Alpine component `posCounter` (pushed script below): scan/search, category filter, cart,
    discount/coupon, payment methods, cash change, hold/resume, customer lookup, complete sale.
    Data: PosController (App\Support\Demo\PosData).
--}}
@extends('admin.layouts.app')

@section('title', 'POS')
@section('content_class', 'pos-content')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/pos.css') }}">
@endpush

@section('content')
<div class="pos" x-data="posCounter" @keydown.f2.window.prevent="$refs.scan.focus()">

    {{-- ===== POS bar ===== --}}
    <header class="pos-bar">
        <button type="button" class="pos-bar__menu" @click="openDrawer()" aria-label="Open menu"
            aria-controls="admin-sidebar" :aria-expanded="drawer.toString()" aria-expanded="false">
            <x-admin.icon name="menu" :size="22" :stroke="2" />
        </button>
        <a class="pos-bar__brand" href="{{ route('admin.dashboard') }}" aria-label="Back to dashboard">
            <x-admin.icon name="chevron-left" :size="18" :stroke="2.2" />YOUR BRAND
        </a>
        <span class="pos-bar__counter">POS · {{ $register['counter'] }}</span>
        <span class="pos-bar__meta">
            <span class="pos-desk-only">Cashier: </span>{{ $register['cashier'] }} ·
            <span class="pos-desk-only">Register</span> opened {{ $register['opened_at'] }}
        </span>

        <div class="pos-bar__actions">
            <button type="button" class="pos-bar__btn" @click="hold()">Hold sale</button>

            <div class="pos-held" @click.outside="heldOpen = false" @keydown.escape="heldOpen = false">
                <button type="button" class="pos-bar__btn" @click="heldOpen = !heldOpen"
                    aria-controls="pos-held-panel" :aria-expanded="heldOpen.toString()" aria-expanded="false">
                    Held<span class="pos-desk-only">&nbsp;sales</span>&nbsp;(<span x-text="held.length">{{ count($heldSales) }}</span>)
                </button>
                <div id="pos-held-panel" class="pos-held__panel" x-show="heldOpen" x-cloak x-transition.opacity>
                    <div class="pos-held__title">Held sales</div>
                    <template x-for="(h, i) in held" :key="h.ref">
                        <div class="pos-held__row">
                            <div class="pos-held__main">
                                <div class="fw-700" x-text="h.ref + ' · ' + h.time"></div>
                                <div class="fs-12 muted" x-text="heldSummary(h)"></div>
                            </div>
                            <button type="button" class="btn btn--outline-strong btn--xs" @click="resume(i)">Resume</button>
                        </div>
                    </template>
                    <p class="pos-held__empty" x-show="!held.length">No held sales.</p>
                </div>
            </div>

            <a class="pos-bar__btn" href="{{ route('admin.returns') }}">Return / exchange</a>
            <button type="button" class="pos-bar__btn pos-bar__btn--accent" @click="openClose()">Close register</button>
        </div>
    </header>

    <div class="pos-body">

        {{-- ===== Products ===== --}}
        <section class="pos-products" aria-label="Products">
            <form class="pos-scan" role="search" @submit.prevent="scan()">
                <x-admin.icon name="barcode" :size="26" class="pos-scan__icon" />
                <label for="pos-scan" class="visually-hidden">Scan barcode or search</label>
                <input id="pos-scan" x-ref="scan" x-model="q" type="text" autocomplete="off" spellcheck="false"
                    placeholder="Scan barcode or type product name / SKU…" @keydown.escape="q = ''">
                <span class="pos-scan__status" aria-hidden="true">Scanner ready</span>
            </form>
            <p class="pos-flash" role="status" aria-live="polite" :class="{ 'is-warn': flashTone === 'warn' }" x-text="flash"></p>

            {{-- Last scanned: card on desktop, one line on tablet --}}
            <div class="pos-last pos-desk-only" x-show="last" x-cloak>
                <span class="thumb pos-last__thumb" :style="'--tone:' + last.tone">
                    <img :src="last.img" :alt="last.name">
                </span>
                <div class="pos-last__text">
                    <div class="pos-last__eyebrow">Last scanned</div>
                    <div class="pos-last__name" x-text="last.name"></div>
                    <div class="pos-last__meta" x-text="last.variant + ' · Barcode ' + last.code"></div>
                </div>
                <div class="pos-last__figures">
                    <div><div class="pos-last__label">Price</div><div class="pos-last__value" x-text="fmt(last.price)"></div></div>
                    <div><div class="pos-last__label">In stock</div><div class="pos-last__value text-brand" x-text="last.stock + ' pcs'"></div></div>
                </div>
            </div>
            <p class="pos-last-line pos-tab-only" x-show="last" x-cloak>
                Last added: <strong x-text="last.name"></strong>
                <span x-text="' · ' + last.variant + ' · ' + fmt(last.price) + ' · ' + last.stock + ' in stock · ' + last.code"></span>
            </p>

            <div class="pos-chips" role="group" aria-label="Category">
                @foreach ($categories as $c)
                    <button type="button" class="pos-chip" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                        :aria-pressed="(cat === @js($c)).toString()" @click="cat = @js($c)">{{ $c }}</button>
                @endforeach
            </div>

            <div class="pos-grid-wrap">
                <div class="pos-grid">
                    <template x-for="p in filtered" :key="p.id">
                        <button type="button" class="pos-product" :class="{ 'is-in-cart': qtyOf(p.id) > 0 }" @click="add(p.id)"
                            :aria-label="'Add ' + p.name + ', ' + p.variant + ', ' + fmt(p.price)">
                            <span class="pos-product__media" :style="'--tone:' + p.tone">
                                <img :src="p.img" alt="" loading="lazy">
                                <span class="pos-product__qty" x-show="qtyOf(p.id) > 0" x-text="'× ' + qtyOf(p.id)"></span>
                            </span>
                            <span class="pos-product__name" x-text="p.name"></span>
                            <span class="pos-product__meta">
                                <span class="truncate" x-text="p.variant"></span>
                                <span class="pos-product__stock" :class="{ 'is-low': p.stock <= 5 }" x-text="p.stock <= 5 ? 'Only ' + p.stock + ' left' : p.stock + ' in stock'"></span>
                            </span>
                            <span class="pos-product__foot">
                                <span class="pos-product__price" x-text="fmt(p.price)"></span>
                                <span class="pos-product__add" aria-hidden="true"><x-admin.icon name="plus" :size="16" :stroke="2.6" /></span>
                            </span>
                        </button>
                    </template>
                </div>
                <div class="pos-grid-empty" x-show="!filtered.length" x-cloak>
                    No products match<span x-show="q.trim()" x-text="' “' + q.trim() + '”'"></span><span x-show="cat !== 'All'" x-text="' in ' + cat"></span>.
                    <button type="button" class="link-btn" @click="q = ''; cat = 'All'">Show all products</button>
                </div>
            </div>
        </section>

        {{-- ===== Current sale ===== --}}
        <aside class="pos-sale" aria-label="Current sale">
            <div class="pos-sale__head pos-tab-only">
                <div>
                    <span class="pos-sale__title">Current sale</span>
                    <span class="pos-sale__ref">#{{ $register['next_receipt'] }} · <span x-text="count + (count === 1 ? ' item' : ' items')"></span></span>
                </div>
                <button type="button" class="pos-sale__clear" @click="clearCart()" :disabled="!lines.length">Clear</button>
            </div>

            <div class="pos-customer">
                <label for="pos-cust" class="pos-customer__label">Customer (phone)</label>
                <div class="pos-customer__row">
                    <div class="input-group pos-customer__input">
                        <x-admin.icon name="phone" :size="16" :stroke="2" />
                        <input id="pos-cust" type="tel" inputmode="tel" autocomplete="off" x-model="phone" placeholder="01XXX-XXXXXX">
                    </div>
                    <button type="button" class="btn btn--outline-strong pos-customer__walkin" @click="phone = ''">Walk-in</button>
                </div>
                <div class="pos-customer__status" :class="'is-' + customer.tone" x-text="customer.text" aria-live="polite"></div>
            </div>

            <div class="pos-lines">
                <p class="pos-lines__empty" x-show="!lines.length" x-cloak>
                    Cart is empty. Scan a barcode<span class="pos-tab-only"> or tap a product</span> to add items.
                </p>
                <template x-for="l in cartLines" :key="l.id">
                    <div class="pos-line">
                        <div class="pos-line__main">
                            <div class="pos-line__name" x-text="l.name"></div>
                            <div class="pos-line__meta" x-text="l.variant + ' · ' + fmt(l.price)"></div>
                        </div>
                        <div class="qty-stepper">
                            <button type="button" :aria-label="'Decrease quantity of ' + l.name" @click="setQty(l.id, -1)"><x-admin.icon name="minus" :size="14" :stroke="2.6" /></button>
                            <span class="qty-stepper__value" x-text="l.qty"></span>
                            <button type="button" :aria-label="'Increase quantity of ' + l.name" @click="setQty(l.id, 1)"><x-admin.icon name="plus" :size="14" :stroke="2.6" /></button>
                        </div>
                        <div class="pos-line__total" x-text="fmt(l.price * l.qty)"></div>
                        <button type="button" class="pos-line__remove" :aria-label="'Remove ' + l.name" @click="remove(l.id)"><x-admin.icon name="close" :size="16" :stroke="2.2" /></button>
                    </div>
                </template>
            </div>

            <div class="pos-totals">
                <div class="pos-totals__row">
                    <span class="muted" x-text="'Subtotal (' + count + ' items)'">Subtotal</span>
                    <span x-text="fmt(subtotal)"></span>
                </div>
                <div class="pos-totals__row">
                    <span class="muted" x-text="discountLabel">Discount</span>
                    <span class="pos-totals__disc">
                        <button type="button" class="pos-mini-btn" @click="discountOpen = !discountOpen"
                            aria-controls="pos-discount" :aria-expanded="discountOpen.toString()" aria-expanded="false">৳ / % · Coupon</button>
                        <span class="text-brand fw-600" x-text="'−' + fmt(discountAmount)"></span>
                    </span>
                </div>
                <div id="pos-discount" class="pos-discount" x-show="discountOpen" x-cloak>
                    <div class="pos-discount__row">
                        <div class="segmented" role="group" aria-label="Discount type">
                            <button type="button" class="segmented__btn" :aria-pressed="(discount.type === 'amount').toString()" @click="setDiscountType('amount')">৳ amount</button>
                            <button type="button" class="segmented__btn" :aria-pressed="(discount.type === 'percent').toString()" @click="setDiscountType('percent')">%</button>
                        </div>
                        <label for="pos-disc-val" class="visually-hidden">Discount value</label>
                        <input id="pos-disc-val" class="input input--xs input--num pos-discount__value" type="number" min="0" step="1"
                            :max="discount.type === 'percent' ? 100 : subtotal" x-model.number="discount.value" @input="discount.coupon = null">
                    </div>
                    <form class="pos-discount__row" @submit.prevent="applyCoupon()">
                        <label for="pos-coupon" class="visually-hidden">Coupon code</label>
                        <input id="pos-coupon" class="input input--xs" type="text" x-model="couponCode" placeholder="Coupon code, e.g. EID10" autocomplete="off">
                        <button type="submit" class="btn btn--outline-strong btn--xs">Apply</button>
                    </form>
                    <p class="fs-12" :class="couponOk ? 'text-success' : 'text-danger'" x-show="couponMsg" x-text="couponMsg" role="status"></p>
                </div>
                <div class="pos-totals__grand">
                    <span>Total</span><span x-text="fmt(total)"></span>
                </div>
            </div>

            <div class="pos-pay">
                <div class="pos-methods" role="radiogroup" aria-label="Payment method" @keydown.right.prevent="cyclePay(1)" @keydown.left.prevent="cyclePay(-1)">
                    <template x-for="m in methods" :key="m.key">
                        <button type="button" role="radio" class="pos-method" :aria-checked="(pay === m.key).toString()"
                            :tabindex="pay === m.key ? 0 : -1" @click="pay = m.key" x-text="m.label"></button>
                    </template>
                </div>

                {{-- Cash --}}
                <div class="pos-pay__panel" x-show="pay === 'cash'">
                    <div class="pos-cash">
                        <label class="field pos-cash__field">Cash received
                            <input x-ref="cash" class="input pos-cash__input" type="text" inputmode="numeric" autocomplete="off"
                                :value="received === 0 ? '' : fmt(receivedAmount)" :placeholder="fmt(total)" @input="setReceived($event.target.value)">
                        </label>
                        <div class="pos-change" :class="{ 'is-short': change < 0 }" aria-live="polite">
                            <div class="pos-change__label" x-text="change < 0 ? 'Short by' : 'Change due'">Change due</div>
                            <div class="pos-change__value" x-text="fmt(Math.abs(change))"></div>
                        </div>
                    </div>
                    <div class="pos-quick" role="group" aria-label="Quick cash amounts">
                        <template x-for="(v, i) in quickCash" :key="v">
                            <button type="button" class="pos-quick__btn" :aria-pressed="(receivedAmount === v).toString()"
                                @click="received = v" x-text="i === 0 ? 'Exact' : fmt(v)"></button>
                        </template>
                    </div>
                </div>

                {{-- Card --}}
                <div class="pos-pay__panel" x-show="pay === 'card'" x-cloak>
                    <label class="field">Card approval code / last 4 digits
                        <input class="input" type="text" inputmode="numeric" x-model="cardCode" placeholder="e.g. 4821" autocomplete="off">
                    </label>
                    <p class="pos-pay__hint">Charge <span x-text="fmt(total)"></span> on the card machine first, then enter the code.</p>
                </div>

                {{-- bKash / Nagad --}}
                <div class="pos-pay__panel" x-show="pay === 'bkash' || pay === 'nagad'" x-cloak>
                    <label class="field"><span x-text="payLabel + ' transaction ID'">Transaction ID</span>
                        <input class="input" type="text" x-model="trxId" placeholder="e.g. 9JX4K2L8QP" autocomplete="off" spellcheck="false">
                    </label>
                    <p class="pos-pay__hint">Customer sends <span x-text="fmt(total)"></span> to merchant number {{ $register['merchant_number'] }}.</p>
                </div>

                {{-- Split --}}
                <div class="pos-pay__panel pos-split" x-show="pay === 'split'" x-cloak>
                    <div class="pos-split__row">
                        <label for="pos-split-cash" class="pos-split__label">Cash</label>
                        <input id="pos-split-cash" class="input" type="number" min="0" step="1" x-model.number="splitCash">
                    </div>
                    <div class="pos-split__row">
                        <label for="pos-split-rest" class="pos-split__label">bKash</label>
                        <input id="pos-split-rest" class="input" type="text" readonly :value="fmt(splitRest)">
                    </div>
                </div>

                <a class="pos-complete" href="{{ $receiptUrl }}" :class="{ 'is-disabled': !lines.length }"
                    :aria-disabled="(!lines.length).toString()" @click="complete($event)">
                    <span class="pos-desk-only" x-text="'Complete sale & print receipt · ' + fmt(total)">Complete sale &amp; print receipt</span>
                    <span class="pos-tab-only pos-complete__tab"><x-admin.icon name="check" :size="20" :stroke="2.4" /><span x-text="'Complete sale · ' + fmt(total)">Complete sale</span></span>
                </a>
                <p class="pos-pay__foot pos-desk-only">Stock is deducted automatically · Receipt prints on 80mm thermal printer</p>
            </div>
        </aside>
    </div>

    {{-- ===== Close register confirmation ===== --}}
    <div class="pos-dialog" x-show="closeOpen" x-cloak @keydown.escape.window="closeOpen = false">
        <div class="pos-dialog__backdrop" @click="closeOpen = false"></div>
        <div class="pos-dialog__box" role="dialog" aria-modal="true" aria-labelledby="pos-close-title">
            <h2 id="pos-close-title" class="pos-dialog__title">Close register?</h2>
            <p class="muted fs-14">Count the cash drawer for {{ $register['counter'] }} before closing. Held sales stay saved for the next shift.</p>
            <p class="alert alert--warn" x-show="lines.length">The current sale is not completed yet (<span x-text="count"></span> items). Hold or complete it first.</p>
            <div class="pos-dialog__actions">
                <button type="button" class="btn btn--outline" x-ref="closeCancel" @click="closeOpen = false">Cancel</button>
                <a class="btn btn--accent" href="{{ route('admin.dashboard') }}">Close register</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const CFG = @js([
        'catalog' => $catalog,
        'cart' => $cart,
        'last' => $lastScanned,
        'discount' => $discount,
        'coupons' => $coupons,
        'held' => $heldSales,
        'customers' => $customers,
        'phone' => $phone,
    ]);
    const METHODS = [
        { key: 'cash', label: 'Cash' }, { key: 'card', label: 'Card' }, { key: 'bkash', label: 'bKash' },
        { key: 'nagad', label: 'Nagad' }, { key: 'split', label: 'Split' },
    ];
    const fmt = (n) => '৳' + Math.round(Math.max(0, n)).toLocaleString('en-IN');

    window.Alpine.data('posCounter', () => ({
        catalog: CFG.catalog,
        byId: Object.fromEntries(CFG.catalog.map((p) => [p.id, p])),
        methods: METHODS,
        cat: 'All',
        q: '',
        lines: CFG.cart.map((l) => ({ ...l })),
        lastId: CFG.last,
        pay: 'cash',
        received: null,      // null = auto (rounded up to ৳500), number = typed / quick amount
        cardCode: '',
        trxId: '',
        splitCash: 4000,
        discount: { ...CFG.discount },
        discountOpen: false,
        couponCode: '',
        couponMsg: '',
        couponOk: true,
        held: CFG.held.map((h) => ({ ...h, lines: h.lines.map((l) => ({ ...l })) })),
        heldOpen: false,
        heldSeq: CFG.held.length + 1,
        phone: CFG.phone,
        closeOpen: false,
        flash: '',
        flashTone: 'ok',
        flashTimer: null,

        init() {
            this.$nextTick(() => this.$refs.scan && this.$refs.scan.focus());
        },

        fmt,

        /* ---- Catalog ---- */
        get filtered() {
            const t = this.q.trim().toLowerCase();
            return this.catalog.filter((p) => (this.cat === 'All' || p.cat === this.cat)
                && (!t || p.name.toLowerCase().includes(t) || p.sku.toLowerCase().includes(t) || p.code.includes(t) || p.variant.toLowerCase().includes(t)));
        },
        get last() { return this.byId[this.lastId] || null; },
        qtyOf(id) { const l = this.lines.find((x) => x.id === id); return l ? l.qty : 0; },

        /* Enter in the scan box: exact barcode / SKU first, then first name match. */
        scan() {
            const term = this.q.trim();
            if (!term) return;
            const t = term.toLowerCase();
            const p = this.catalog.find((x) => x.code === term || x.sku.toLowerCase() === t)
                || this.catalog.find((x) => x.name.toLowerCase().includes(t) || x.sku.toLowerCase().includes(t));
            if (!p) { this.notify('No product matches “' + term + '”. Check the barcode or search by name.', 'warn'); return; }
            if (this.add(p.id)) this.q = '';
        },
        add(id) {
            const p = this.byId[id];
            const line = this.lines.find((l) => l.id === id);
            if ((line ? line.qty : 0) >= p.stock) { this.notify('Only ' + p.stock + ' of ' + p.name + ' (' + p.variant + ') in stock.', 'warn'); return false; }
            if (line) line.qty++; else this.lines.push({ id, qty: 1 });
            this.lastId = id;
            this.notify('Added ' + p.name + ' · ' + p.variant + ' · ' + fmt(p.price));
            return true;
        },
        notify(text, tone = 'ok') {
            this.flash = text;
            this.flashTone = tone;
            clearTimeout(this.flashTimer);
            this.flashTimer = setTimeout(() => { this.flash = ''; }, 4000);
        },

        /* ---- Cart ---- */
        get cartLines() { return this.lines.map((l) => ({ ...this.byId[l.id], qty: l.qty })); },
        get count() { return this.lines.reduce((s, l) => s + l.qty, 0); },
        get subtotal() { return this.lines.reduce((s, l) => s + this.byId[l.id].price * l.qty, 0); },
        setQty(id, d) {
            const line = this.lines.find((l) => l.id === id);
            if (!line) return;
            if (d > 0 && line.qty >= this.byId[id].stock) { this.notify('No more stock for ' + this.byId[id].name + '.', 'warn'); return; }
            line.qty += d;
            if (line.qty <= 0) this.remove(id);
        },
        remove(id) { this.lines = this.lines.filter((l) => l.id !== id); },
        clearCart() { this.lines = []; this.received = null; },

        /* ---- Discount ---- */
        get discountAmount() {
            if (!this.subtotal) return 0;
            const v = Math.max(0, Number(this.discount.value) || 0);
            const amt = this.discount.type === 'percent' ? Math.round(this.subtotal * Math.min(v, 100) / 100) : v;
            return Math.min(amt, this.subtotal);
        },
        get discountLabel() {
            if (this.discount.coupon) return 'Discount · ' + this.discount.coupon;
            return this.discount.type === 'percent' && this.discount.value ? 'Discount (' + this.discount.value + '%)' : 'Discount';
        },
        setDiscountType(type) { this.discount = { type, value: this.discount.type === type ? this.discount.value : 0, coupon: null }; },
        applyCoupon() {
            const code = this.couponCode.trim().toUpperCase();
            const c = CFG.coupons[code];
            if (!code) return;
            if (!c) { this.couponOk = false; this.couponMsg = 'Coupon ' + code + ' is not valid at the counter.'; return; }
            this.discount = { type: c.type, value: c.value, coupon: code };
            this.couponOk = true;
            this.couponMsg = code + ' applied · ' + c.label;
            this.couponCode = '';
        },
        get total() { return Math.max(0, this.subtotal - this.discountAmount); },

        /* ---- Payment ---- */
        get payLabel() { return (METHODS.find((m) => m.key === this.pay) || {}).label || ''; },
        cyclePay(d) {
            const i = METHODS.findIndex((m) => m.key === this.pay);
            this.pay = METHODS[(i + d + METHODS.length) % METHODS.length].key;
            this.$nextTick(() => { const el = this.$root.querySelector('.pos-method[aria-checked="true"]'); el && el.focus(); });
        },
        get receivedAmount() { return this.received === null ? Math.ceil(this.total / 500) * 500 : this.received; },
        get change() { return this.receivedAmount - this.total; },
        setReceived(text) { const d = String(text).replace(/\D/g, ''); this.received = d ? Number(d) : 0; },
        get quickCash() {
            const t = this.total;
            const out = [];
            [t, Math.ceil(t / 500) * 500, Math.ceil(t / 1000) * 1000, Math.ceil(t / 1000) * 1000 + 1000]
                .forEach((v) => { if (v > 0 && !out.includes(v)) out.push(v); });
            return out;
        },
        get splitRest() { return Math.max(this.total - (Number(this.splitCash) || 0), 0); },

        complete(e) {
            if (!this.lines.length) { e.preventDefault(); this.notify('Add at least one item before completing the sale.', 'warn'); this.$refs.scan.focus(); return; }
            if (this.pay === 'cash' && this.change < 0) { e.preventDefault(); this.notify('Cash received is less than the total.', 'warn'); this.$refs.cash.focus(); }
            // Static export: the link opens the receipt of a sample POS sale.
        },

        /* ---- Hold / resume ---- */
        hold(silent = false) {
            if (!this.lines.length) { if (!silent) this.notify('Cart is empty, nothing to hold.', 'warn'); return; }
            const time = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            const ref = 'HOLD-' + String(this.heldSeq++).padStart(2, '0');
            this.held.push({ ref, time, phone: this.phone, lines: this.lines.map((l) => ({ ...l })) });
            this.lines = [];
            this.received = null;
            this.phone = '';
            if (!silent) this.notify('Sale held as ' + ref + '. Resume it from Held sales.');
        },
        resume(i) {
            const h = this.held.splice(i, 1)[0];
            this.hold(true);
            this.lines = h.lines;
            this.phone = h.phone || '';
            this.received = null;
            this.heldOpen = false;
            this.notify(h.ref + ' resumed.');
        },
        heldSummary(h) {
            const pcs = h.lines.reduce((s, l) => s + l.qty, 0);
            const amt = h.lines.reduce((s, l) => s + this.byId[l.id].price * l.qty, 0);
            return pcs + (pcs === 1 ? ' item' : ' items') + ' · ' + fmt(amt) + ' · ' + (h.phone || 'Walk-in');
        },

        /* ---- Customer lookup (static) ---- */
        get customer() {
            const d = this.phone.replace(/\D/g, '');
            if (!d) return { tone: 'muted', text: 'Walk-in customer · no phone saved' };
            const c = CFG.customers[d];
            if (c) return { tone: 'ok', text: c.account + ' · ' + c.orders + ' previous ' + (c.orders === 1 ? 'order' : 'orders') };
            if (/^01\d{9}$/.test(d)) return { tone: 'muted', text: 'New customer · a profile is saved with this sale' };
            return { tone: 'warn', text: 'Enter an 11-digit mobile number (01XXXXXXXXX)' };
        },

        openClose() {
            this.closeOpen = true;
            this.$nextTick(() => this.$refs.closeCancel.focus());
        },
    }));
});
</script>
@endpush
