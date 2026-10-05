{{--
    Returns & exchanges — Returns.dc.html.
    Data: $tabs, $tones, $methods, $requests (ReturnsData), $policyDays.
    Alpine (returnsPage, script at the bottom):
        tab / sel        status tab (counts update live) and the selected request (detail panel)
        statuses         local status changes: Approve, Reject (asks for a reason), Mark item received,
                         Send new size / Complete refund & close
        restock, method  restock switch and refund method (Cash / bKash / Store credit)
        sizes            picked exchange size per request (out-of-stock sizes are disabled)
    Phones: tabs become a scrolling pill row; the list and the detail panel stack.
--}}
@extends('admin.layouts.app')

@section('title', 'Returns & exchanges')

@use('App\Support\DemoData')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/returns.css') }}">
@endpush

@php
    $initialTab = 'requests';
    $initialSel = 'RR-0146';
    $initialMethod = 'bkash';
    $tabOf = function (string $status) use ($tabs): string {
        foreach ($tabs as $key => $t) {
            if (in_array($status, $t['statuses'], true)) {
                return $key;
            }
        }

        return 'requests';
    };
    $cfg = [
        'tab' => $initialTab,
        'sel' => $initialSel,
        'method' => $initialMethod,
        'tabs' => $tabs,
        'tones' => $tones,
        'methods' => $methods,
        'requests' => array_map(fn ($r) => [
            'id' => $r['id'], 'status' => $r['status'], 'type' => $r['type'], 'price' => $r['price'],
            'from' => $r['from'], 'to' => $r['to'], 'sku' => $r['sku'], 'colour' => $r['colour'], 'closed_note' => $r['closed_note'],
        ], $requests),
    ];
    $closed = ['refunded', 'exchanged', 'rejected'];
@endphp

@section('content')
<div class="rr" x-data="returnsPage(@js($cfg))">

    <x-admin.page-header title="Returns & exchanges" subtitle="{{ $policyDays }}-day exchange policy · requests from website, phone and the shop counter">
        <x-slot:actions>
            <a class="btn btn--outline" href="{{ route('admin.settings') }}">Return policy settings</a>
            <a class="btn btn--brand" href="{{ route('admin.pos') }}">
                <x-admin.icon name="plus" :size="18" :stroke="2" />New request at counter
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tabs: underlined on desktop, pill row on phones --}}
    @foreach (['tabs hide-sm' => 'tab', 'pill-tabs show-sm' => 'pill-tab'] as $listClass => $tabClass)
        <div role="tablist" aria-label="Request status" class="{{ $listClass }}">
            @foreach ($tabs as $key => $t)
                <button type="button" role="tab" class="{{ $tabClass }}"
                    aria-selected="{{ $key === $initialTab ? 'true' : 'false' }}"
                    :aria-selected="(tab === '{{ $key }}').toString()" @click="setTab('{{ $key }}')">
                    {{ $t['label'] }}
                    <span class="{{ $tabClass }}__count" x-text="count('{{ $key }}')">{{ $t['count'] }}</span>
                </button>
            @endforeach
        </div>
    @endforeach

    <form class="filter-bar" aria-label="Filter requests" @submit.prevent>
        <label class="field field--wide">Request / order no. or phone
            <span class="input-group">
                <x-admin.icon name="search" :size="16" :stroke="2" />
                <input type="search" placeholder="RR-0146, #WB-10438 or 017XXXXXXXX">
            </span>
        </label>
        <label class="field">Type
            <select class="select select--sm"><option>Return + exchange</option><option>Return (refund)</option><option>Exchange (size / colour)</option></select>
        </label>
        <label class="field">Reason
            <select class="select select--sm"><option>Any reason</option><option>Size issue</option><option>Defect / damage</option><option>Colour different</option><option>Wrong item sent</option><option>Changed mind</option></select>
        </label>
        <label class="field">Channel
            <select class="select select--sm"><option>All channels</option><option>Online</option><option>POS (shop)</option></select>
        </label>
        <label class="field">Requested
            <select class="select select--sm"><option>Last 30 days</option><option>Last 7 days</option><option>This month</option><option>Custom range</option></select>
        </label>
    </form>

    <div class="row-wrap" style="--gap: 18px">
        {{-- Request list --}}
        <section class="rr-list" aria-label="Requests">
            @foreach ($requests as $r)
                @php($inInitial = $tabOf($r['status']) === $initialTab)
                <button type="button" @class(['rr-item', 'is-selected' => $r['id'] === $initialSel])
                    x-show="inTab('{{ $r['id'] }}')" @style(['display: none' => ! $inInitial])
                    :class="{ 'is-selected': sel === '{{ $r['id'] }}' }"
                    aria-pressed="{{ $r['id'] === $initialSel ? 'true' : 'false' }}" :aria-pressed="(sel === '{{ $r['id'] }}').toString()"
                    aria-controls="rr-panel-{{ $r['id'] }}" @click="pick('{{ $r['id'] }}')">
                    <span class="thumb rr-item__thumb" style="--tone: {{ $r['tone'] }}">
                        <img src="{{ $r['image_url'] }}" alt="" loading="lazy">
                    </span>
                    <span class="rr-item__main">
                        <span class="rr-item__ids"><span class="rr-item__id">{{ $r['id'] }}</span><span class="muted">Order {{ $r['order'] }}</span><span class="muted">· {{ $r['date'] }}</span></span>
                        <span class="rr-item__name">{{ $r['name'] }}</span>
                        <span class="rr-item__meta">{{ $r['variant'] }} · {{ $r['reason'] }}</span>
                    </span>
                    <span class="rr-item__side">
                        <span class="chip chip--{{ $tones[$r['status']] }}" :class="chipClass('{{ $r['id'] }}')" x-text="stLabel('{{ $r['id'] }}')">{{ ucfirst($r['status']) }}</span>
                        <span @class(['rr-type', 'rr-type--exchange' => $r['type'] === 'exchange'])>{{ $r['type_label'] }}</span>
                    </span>
                </button>
            @endforeach
            <div class="rr-empty" x-show="visible.length === 0" x-cloak>No requests in this tab.</div>
            <div class="fs-13 muted rr-showing" x-text="showing">Showing 3 of 3 in Requests</div>
        </section>

        {{-- Detail panel (one per request; only the selected one is shown) --}}
        <aside class="rr-detail card card--flush" aria-label="Request details" x-ref="detail">
            @foreach ($requests as $r)
                @php($id = $r['id'])
                @php($isEx = $r['type'] === 'exchange')
                <div id="rr-panel-{{ $id }}" class="rr-panel" x-show="sel === '{{ $id }}'" @style(['display: none' => $id !== $initialSel])
                    x-data="{ rejecting: false, reason: '' }">

                    <div class="rr-sec rr-sec--head">
                        <div class="split" style="--gap: 8px">
                            <h2 class="rr-panel__title">Request {{ $id }}</h2>
                            <span class="chip chip--{{ $tones[$r['status']] }}" :class="chipClass('{{ $id }}')" x-text="stLabel('{{ $id }}')">{{ ucfirst($r['status']) }}</span>
                        </div>
                        <div class="fs-13 muted">Order
                            <a class="fw-700" href="{{ $r['order_exists'] ? route('admin.orders.show', $r['order_id']) : route('admin.orders.index', ['q' => $r['order_id']]) }}">{{ $r['order'] }}</a>
                            · delivered {{ $r['delivered'] }} · {{ $r['pay'] }}</div>
                        <div class="fs-13 muted">Customer {{ $r['phone'] }} · requested {{ $r['date'] }} via {{ $r['channel'] }}</div>
                        <div @class(['rr-policy', 'rr-policy--out' => ! $r['in_window']])>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"></circle><path d="M8 12l3 3 5-6"></path></svg>
                            @if ($r['in_window'])
                                Within {{ $policyDays }}-day window · {{ $r['days'] }} {{ $r['days'] === 1 ? 'day' : 'days' }} after delivery
                            @else
                                Outside {{ $policyDays }}-day window · {{ $r['days'] }} days after delivery
                            @endif
                        </div>
                    </div>

                    <div class="rr-sec rr-product">
                        <span class="thumb rr-product__thumb" style="--tone: {{ $r['tone'] }}">
                            <img src="{{ $r['image_url'] }}" alt="{{ $r['name'] }}" loading="lazy">
                        </span>
                        <div class="rr-product__body">
                            <div class="fw-700 rr-product__name">{{ $r['name'] }}</div>
                            <div class="text-2">{{ $r['variant'] }} · qty 1 · {{ DemoData::money($r['price']) }}</div>
                            <div class="mono muted">{{ $r['sku'] }}</div>
                            <div class="cluster mt-4" style="--gap: 6px">
                                <span @class(['rr-type', 'rr-type--exchange' => $isEx])>{{ $r['type_label'] }}</span>
                                <span class="tag">Reason: {{ $r['reason'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rr-sec">
                        <h3 class="rr-sec__title">Customer photos ({{ count($r['photos']) }})</h3>
                        <div class="rr-photos">
                            @forelse ($r['photos'] as $ph)
                                <button type="button" class="rr-photo" style="--tone: {{ $ph['tone'] }}" aria-label="Open {{ $ph['label'] }}">{{ $ph['label'] }}</button>
                            @empty
                                <span class="rr-photo rr-photo--none">No photos sent</span>
                            @endforelse
                        </div>
                        <figure class="rr-quote">
                            <blockquote>{{ $r['note'] }}</blockquote>
                            <figcaption>Customer note · {{ $r['date'] }}</figcaption>
                        </figure>
                    </div>

                    <div class="rr-sec rr-sec--form">
                        <div class="split" style="--gap: 12px; flex-wrap: nowrap">
                            <div>
                                <div id="rr-restock-{{ $id }}" class="fw-700 fs-14">Restock item</div>
                                <div class="fs-12 muted" x-text="restock ? 'Adds 1 pc back to {{ $r['sku'] }} when the item is received in good condition' : 'Item will be moved to damaged stock, not back to sale'">Adds 1 pc back to {{ $r['sku'] }} when the item is received in good condition</div>
                            </div>
                            <button type="button" role="switch" class="switch switch--lg" aria-labelledby="rr-restock-{{ $id }}"
                                aria-checked="true" :aria-checked="restock.toString()" @click="restock = !restock"></button>
                        </div>

                        <fieldset class="rr-fieldset">
                            <legend class="rr-legend">Refund method</legend>
                            <div class="rr-choices" role="radiogroup" aria-label="Refund method">
                                @foreach ($methods as $key => $m)
                                    <button type="button" role="radio" class="rr-choice"
                                        aria-checked="{{ $key === $initialMethod ? 'true' : 'false' }}" :aria-checked="(method === '{{ $key }}').toString()"
                                        @click="method = '{{ $key }}'">{{ $m['label'] }}</button>
                                @endforeach
                            </div>
                            <div class="form-row">
                                <label class="field">Refund amount
                                    <input class="input input--num rr-amount" type="text" inputmode="numeric" value="{{ DemoData::money($isEx ? 0 : $r['price']) }}">
                                </label>
                                <label class="field"><span x-text="methods[method].field">{{ $methods[$initialMethod]['field'] }}</span>
                                    <input class="input" type="text" placeholder="{{ $methods[$initialMethod]['placeholder'] }}" :placeholder="methods[method].placeholder">
                                </label>
                            </div>
                            <div class="fs-12 muted">
                                {{ $isEx ? 'Same-price exchange: nothing to refund. Use only if the new variant costs less.' : 'Full item price. Delivery charge (৳70) is not refunded.' }}
                            </div>
                        </fieldset>

                        <fieldset class="rr-fieldset">
                            <legend class="rr-legend">Exchange to new variant</legend>
                            @if ($isEx)
                                <div class="fs-12 muted">{{ $r['colour'] }} · stock shown for this colour</div>
                                <div class="rr-sizes" role="radiogroup" aria-label="New size">
                                    @foreach ($r['sizes'] as $s)
                                        <button type="button" role="radio" class="rr-size rr-size--{{ $s['level'] }}"
                                            aria-checked="{{ $s['label'] === $r['to'] ? 'true' : 'false' }}"
                                            :aria-checked="(size('{{ $id }}') === @js($s['label'])).toString()"
                                            @click="pickSize('{{ $id }}', @js($s['label']))" @disabled($s['stock'] === 0)>
                                            <span class="rr-size__label">{{ $s['label'] }}</span>
                                            <span class="rr-size__stock">{{ $s['stock_label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                <div class="rr-summary">
                                    <span class="text-2" x-text="exchangeSummary('{{ $id }}')">{{ $r['from'] }} → {{ $r['to'] }} · {{ $r['colour'] }}</span>
                                    <span class="fw-700">Price difference ৳0</span>
                                </div>
                            @else
                                <div class="rr-summary rr-summary--note">
                                    <span>Customer asked for a refund, not an exchange.
                                        <button type="button" class="link-btn rr-offer">Offer exchange instead</button></span>
                                </div>
                            @endif
                        </fieldset>

                        <label class="field">Item comes back by
                            <select class="select">
                                <option>Courier pickup (৳60 charged to customer)</option><option>Customer drops at shop</option><option>Swap at door with new parcel</option>
                            </select>
                        </label>
                    </div>

                    {{-- Actions by status --}}
                    <div class="rr-sec rr-sec--actions">
                        <div class="stack stack--sm" x-show="st('{{ $id }}') === 'requested'" @style(['display: none' => $r['status'] !== 'requested'])>
                            <div class="rr-decide" x-show="!rejecting">
                                <button type="button" class="btn btn--brand rr-approve" @click="approve('{{ $id }}')">
                                    <x-admin.icon name="check" :size="18" :stroke="2.2" />Approve
                                </button>
                                <button type="button" class="btn btn--danger rr-reject" @click="rejecting = true; $nextTick(() => $refs.reason{{ str_replace('-', '', $id) }}.focus())">Reject</button>
                            </div>
                            <div class="stack stack--sm" x-show="rejecting" x-cloak>
                                <label class="field">Reason for rejecting (sent to the customer by SMS)
                                    <textarea class="textarea" rows="2" x-model="reason" x-ref="reason{{ str_replace('-', '', $id) }}" placeholder="e.g. Request came after the {{ $policyDays }}-day window"></textarea>
                                </label>
                                <div class="cluster">
                                    <button type="button" class="btn btn--danger-solid btn--sm" :disabled="!reason.trim()" @click="reject('{{ $id }}', reason); rejecting = false">Reject request</button>
                                    <button type="button" class="btn btn--ghost btn--sm" @click="rejecting = false">Back</button>
                                </div>
                            </div>
                            <p class="fs-12 muted">Approving sends an SMS with pickup details to the customer. Rejecting asks for a reason.</p>
                        </div>

                        <button type="button" class="btn btn--primary rr-next" x-show="nextLabel('{{ $id }}')" @style(['display: none' => ! in_array($r['status'], ['approved', 'received'], true)])
                            @click="advance('{{ $id }}')" x-text="nextLabel('{{ $id }}')">{{ $r['status'] === 'approved' ? 'Mark item received' : ($isEx ? 'Send new size & close' : 'Complete refund & close') }}</button>

                        <div class="note-box" x-show="isClosed('{{ $id }}')" @style(['display: none' => ! in_array($r['status'], $closed, true)])
                            x-text="closedNote('{{ $id }}')">{{ $r['closed_note'] }}</div>
                    </div>
                </div>
            @endforeach
        </aside>
    </div>

    <p class="visually-hidden" role="status" aria-live="polite" x-text="announce"></p>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const TONES = ['amber', 'blue', 'violet', 'cyan', 'green', 'red', 'gray'];
        const money = (n) => '৳' + Number(n).toLocaleString('en-IN');
        const label = (s) => s.charAt(0).toUpperCase() + s.slice(1);

        window.Alpine.data('returnsPage', (cfg) => ({
            tab: cfg.tab,
            sel: cfg.sel,
            method: cfg.method,
            methods: cfg.methods,
            restock: true,
            statuses: {},
            notes: {},
            sizes: {},
            announce: '',

            req(id) { return cfg.requests.find((r) => r.id === id); },
            st(id) { return this.statuses[id] || this.req(id).status; },
            stLabel(id) { return label(this.st(id)); },
            chipClass(id) { const t = cfg.tones[this.st(id)] || 'gray'; return Object.fromEntries(TONES.map((x) => ['chip--' + x, x === t])); },
            tabOf(status) { return Object.keys(cfg.tabs).find((k) => cfg.tabs[k].statuses.includes(status)); },
            inTab(id) { return this.tabOf(this.st(id)) === this.tab; },
            get visible() { return cfg.requests.filter((r) => this.inTab(r.id)); },

            // Tab count = design total, adjusted by status changes made on this page.
            count(key) {
                const now = cfg.requests.filter((r) => this.tabOf(this.st(r.id)) === key).length;
                const before = cfg.requests.filter((r) => this.tabOf(r.status) === key).length;
                return cfg.tabs[key].count + now - before;
            },
            get showing() { return 'Showing ' + this.visible.length + ' of ' + this.count(this.tab) + ' in ' + cfg.tabs[this.tab].label; },
            setTab(key) {
                this.tab = key;
                const ids = this.visible.map((r) => r.id);
                if (ids.length && !ids.includes(this.sel)) this.sel = ids[0];
            },
            pick(id) {
                this.sel = id;
                this.$nextTick(() => {
                    const r = this.$refs.detail.getBoundingClientRect();
                    if (r.top > window.innerHeight - 80) this.$refs.detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            size(id) { return this.sizes[id] || this.req(id).to; },
            pickSize(id, s) { this.sizes = { ...this.sizes, [id]: s }; },
            exchangeSummary(id) { const r = this.req(id); return r.from + ' → ' + this.size(id) + ' · ' + r.colour; },

            isClosed(id) { return ['refunded', 'exchanged', 'rejected'].includes(this.st(id)); },
            closedNote(id) { return this.notes[id] || this.req(id).closed_note || ''; },
            nextLabel(id) {
                const s = this.st(id);
                if (s === 'approved') return 'Mark item received';
                if (s === 'received') return this.req(id).type === 'exchange' ? 'Send new size & close' : 'Complete refund & close';
                return '';
            },
            setStatus(id, status, note) {
                this.statuses = { ...this.statuses, [id]: status };
                if (note) this.notes = { ...this.notes, [id]: note };
                this.announce = 'Request ' + id + ' ' + status;
            },
            approve(id) { this.setStatus(id, 'approved'); },
            reject(id, reason) { this.setStatus(id, 'rejected', 'Rejected just now: ' + reason.trim() + ' SMS sent to customer.'); },
            advance(id) {
                const r = this.req(id);
                const s = this.st(id);
                const stock = this.restock ? ' Item restocked.' : ' Item moved to damaged stock.';
                if (s === 'approved') return this.setStatus(id, 'received');
                if (s !== 'received') return;
                if (r.type === 'exchange') {
                    this.setStatus(id, 'exchanged', 'New size ' + this.size(id) + ' sent just now.' + stock);
                } else {
                    this.setStatus(id, 'refunded', 'Refunded ' + money(r.price) + ' via ' + this.methods[this.method].label + ' just now.' + stock);
                }
            },
        }));
    });
</script>
@endpush
