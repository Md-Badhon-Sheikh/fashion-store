{{--
    Orders list — Orders.dc.html (desktop table) + M-AdminOrders.dc.html (phone, < 640px: order cards).
    Data: $rows (OrdersData::rows), $tabs, $tabCounts (all-time, desktop), $todayCounts (phone), $nextActions.
    Alpine (ordersIndex, script at the bottom):
        tab           active status tab (filters table rows and phone cards, counts update live)
        sel           selected order ids (row checkboxes + bulk action bar)
        moved         local status changes (phone "Confirm" / bulk "Mark confirmed"), with Undo toast
        filters       filter bar values (search filters by order no. / phone; other filters are visual)
        limit, page   phone "Load more", desktop pagination (visual)
--}}
@extends('admin.layouts.app')

@section('title', 'Orders')

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/orders.css') }}">
@endpush

@section('mobile_actions')
    <button type="button" class="icon-btn icon-btn--ghost" aria-label="Filter and sort" @click="$dispatch('orders-filters-toggle')">
        <x-admin.icon name="filter" :size="22" />
    </button>
    <a class="icon-btn icon-btn--brand" href="{{ route('admin.pos') }}" aria-label="New order at POS">
        <x-admin.icon name="plus" :size="22" :stroke="2.2" />
    </a>
@endsection

@php
    $cfg = [
        'rows' => array_map(fn ($r) => [
            'id' => $r['id'],
            'status' => $r['status'],
            'search' => strtolower($r['number'].' '.$r['phone'].' '.$r['phone_full'].' '.$r['items_summary']),
        ], $rows),
        'tabCounts' => $tabCounts,
        'todayCounts' => $todayCounts,
        'tones' => Status::toneMap(),
        'nextActions' => $nextActions,
        // Design state: three pending orders already ticked so the bulk bar shows.
        'selected' => ['WB-10491', 'WB-10490', 'PH-0217'],
        // Deep links: ?status=pending (dashboard status pills), ?q=… (topbar search, customer history).
        'tab' => array_key_exists((string) request()->query('status'), $tabs) ? (string) request()->query('status') : 'all',
        'q' => (string) request()->query('q', ''),
    ];
    $tone = fn (string $status) => Status::tone($status);
@endphp

@section('content')
<div class="orders" x-data="ordersIndex(@js($cfg))" @orders-filters-toggle.window="filtersOpen = !filtersOpen">

    <x-admin.page-header title="Orders" class="orders__header">
        <div class="page-header__subtitle hide-sm">Website, Facebook, phone and POS orders in one list · {{ $todayCounts['pending'] }} waiting for confirmation call</div>
        <x-slot:actions>
            <div class="cluster hide-sm">
                <button type="button" class="btn btn--outline">
                    <x-admin.icon name="download" :size="18" />Export
                </button>
                <button type="button" class="btn btn--brand">
                    <x-admin.icon name="plus" :size="18" :stroke="2" />Manual order<span class="btn__hint">phone / Facebook</span>
                </button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Phone: search + scan --}}
    <div class="orders-search show-sm">
        <x-admin.icon name="search" :size="18" :stroke="2" />
        <label for="orders-q-sm" class="visually-hidden">Search orders</label>
        <input id="orders-q-sm" type="search" placeholder="Order no., phone or product" x-model.debounce.200ms="filters.q">
        <button type="button" class="orders-search__scan" aria-label="Scan barcode or invoice QR">
            <x-admin.icon name="barcode" :size="18" />
        </button>
    </div>

    {{-- Desktop: status tabs with all-time counts --}}
    <div role="tablist" aria-label="Order status" class="tabs hide-sm">
        @foreach ($tabs as $key => $label)
            <button type="button" role="tab" class="tab"
                aria-selected="{{ $key === 'all' ? 'true' : 'false' }}"
                :aria-selected="(tab === '{{ $key }}').toString()"
                @click="setTab('{{ $key }}')">
                {{ $label }}
                <span class="tab__count ord-count ord-count--{{ $key === 'all' ? 'gray' : $tone($key) }}"
                    x-text="fmt(count('tabCounts', '{{ $key }}'))">{{ DemoData::number($tabCounts[$key]) }}</span>
            </button>
        @endforeach
    </div>

    {{-- Phone: pill tabs with today's counts --}}
    <div role="tablist" aria-label="Order status" class="pill-tabs show-sm">
        @foreach ($tabs as $key => $label)
            <button type="button" role="tab" class="pill-tab ord-pill"
                aria-selected="{{ $key === 'all' ? 'true' : 'false' }}"
                :aria-selected="(tab === '{{ $key }}').toString()"
                @click="setTab('{{ $key }}')">
                {{ $label }}
                <span class="pill-tab__count" x-text="count('todayCounts', '{{ $key }}')">{{ $todayCounts[$key] }}</span>
            </button>
        @endforeach
    </div>

    {{-- Filters (desktop always; phone when the topbar filter button is pressed) --}}
    <form class="filter-bar orders-filters" :class="{ 'is-open': filtersOpen }" @submit.prevent aria-label="Filter orders">
        <label class="field field--wide">Order no. or phone
            <span class="input-group">
                <x-admin.icon name="search" :size="16" :stroke="2" />
                <input type="search" placeholder="#WB-10482 or 017XXXXXXXX" x-model.debounce.200ms="filters.q">
            </span>
        </label>
        <label class="field">Source
            <select class="select select--sm" x-model="filters.source">
                <option>All sources</option><option>Online store</option><option>POS (shop)</option><option>Facebook</option><option>Phone</option>
            </select>
        </label>
        <label class="field">Payment method
            <select class="select select--sm" x-model="filters.method">
                <option>All methods</option><option>COD</option><option>Cash</option><option>Card</option><option>bKash</option><option>Nagad</option>
            </select>
        </label>
        <label class="field">Payment status
            <select class="select select--sm" x-model="filters.payment">
                <option>Any</option><option>Paid</option><option>Unpaid</option><option>COD collected</option><option>Refunded</option>
            </select>
        </label>
        <label class="field">Courier
            <select class="select select--sm" x-model="filters.courier">
                <option>All couriers</option><option>Steadfast</option><option>Pathao Courier</option><option>RedX</option><option>Own delivery</option><option>Not assigned</option>
            </select>
        </label>
        <div class="field field--wide">
            <span id="orders-range-label">Date range</span>
            <div class="date-range" role="group" aria-labelledby="orders-range-label">
                <label for="orders-from" class="visually-hidden">From date</label>
                <input id="orders-from" class="input input--sm" type="date" x-model="filters.from" value="2026-09-28">
                <span class="muted">to</span>
                <label for="orders-to" class="visually-hidden">To date</label>
                <input id="orders-to" class="input input--sm" type="date" x-model="filters.to" value="2026-10-04">
            </div>
        </div>
        <button type="button" class="btn btn--ghost btn--sm orders-filters__reset" @click="resetFilters()">Reset</button>
    </form>

    {{-- Undo toast (phone quick actions and bulk confirm) --}}
    <div class="orders-toast" role="status" aria-live="polite" x-show="toast" x-cloak>
        <x-admin.icon name="check" :size="18" :stroke="2.4" />
        <span class="orders-toast__text" x-text="toast"></span>
        <button type="button" class="orders-toast__undo" @click="undo()">Undo</button>
    </div>

    {{-- Desktop: bulk action bar --}}
    <div class="bulk-bar hide-sm" role="region" aria-label="Bulk actions" x-show="selCount > 0">
        <span class="bulk-bar__count" x-text="selCount + ' selected'">{{ count($cfg['selected']) }} selected</span>
        <button type="button" class="btn btn--accent btn--sm" @click="bulkConfirm()">
            <x-admin.icon name="check" :size="16" :stroke="2.2" />Mark confirmed
        </button>
        <button type="button" class="btn btn--dark-outline btn--sm">
            <x-admin.icon name="print" :size="16" />Print invoices
        </button>
        <button type="button" class="btn btn--dark-outline btn--sm">
            <x-admin.icon name="stock" :size="16" />Print packing slips
        </button>
        <label for="bulk-courier" class="visually-hidden">Assign courier</label>
        <select id="bulk-courier" class="select">
            <option>Assign courier…</option><option>Steadfast</option><option>Pathao Courier</option><option>RedX</option><option>Own delivery</option>
        </select>
        <button type="button" class="bulk-bar__clear" @click="clearSel()">Clear selection</button>
    </div>

    {{-- Desktop: orders table --}}
    <div class="table-card hide-sm">
        <table class="table" style="--table-min: 1180px">
            <caption class="visually-hidden">Orders</caption>
            <thead>
                <tr>
                    <th scope="col" class="cell-check">
                        <input type="checkbox" class="checkbox" aria-label="Select all orders on this page"
                            :checked="allSelected" x-effect="$el.indeterminate = selCount > 0 && !allSelected" @change="toggleAll()">
                    </th>
                    <th scope="col">Order no.</th>
                    <th scope="col">Date / time</th>
                    <th scope="col">Customer</th>
                    <th scope="col" class="num">Items</th>
                    <th scope="col">Source</th>
                    <th scope="col">Payment</th>
                    <th scope="col">Courier / tracking</th>
                    <th scope="col" class="num">Amount</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $o)
                    @php($selected = in_array($o['id'], $cfg['selected'], true))
                    <tr @class(['is-selected' => $selected]) x-show="matches('{{ $o['id'] }}')" :class="{ 'is-selected': isSel('{{ $o['id'] }}') }">
                        <td class="cell-check">
                            <input type="checkbox" class="checkbox" aria-label="Select order {{ $o['number'] }}"
                                @checked($selected) :checked="isSel('{{ $o['id'] }}')" @change="toggle('{{ $o['id'] }}')">
                        </td>
                        <td class="nowrap"><a class="fw-700" href="{{ route('admin.orders.show', $o['id']) }}">{{ $o['number'] }}</a></td>
                        <td class="nowrap">{{ $o['date'] }}<span class="cell-sub">{{ $o['time'] }}</span></td>
                        <td class="nowrap">{{ $o['phone'] }}<span class="cell-sub">{{ $o['area'] }}</span></td>
                        <td class="num">{{ $o['items_count'] }}</td>
                        <td><span class="tag">{{ $o['source'] }}</span></td>
                        <td class="nowrap">
                            <span class="fw-600 ord-block">{{ $o['payment_method'] }}</span>
                            <span class="ord-pay ord-pay--{{ $o['pay_tone'] }}">{{ $o['payment_status'] }}</span>
                        </td>
                        <td class="nowrap">
                            <span @class(['ord-block', 'muted' => $o['courier'] === 'Not assigned'])>{{ $o['courier'] }}</span>
                            <span class="cell-sub mono">{{ $o['tracking'] }}</span>
                        </td>
                        <td class="num cell-strong">{{ DemoData::money($o['amount']) }}</td>
                        <td>
                            <span class="chip chip--{{ $tone($o['status']) }}"
                                :class="chipClass('{{ $o['id'] }}')" x-text="statusLabel('{{ $o['id'] }}')">{{ Status::label($o['status']) }}</span>
                        </td>
                        <td class="cell-actions">
                            <div class="cluster" style="--gap: 6px">
                                <a class="btn btn--outline-strong btn--xs" href="{{ route('admin.orders.show', $o['id']) }}">View</a>
                                <a class="icon-btn icon-btn--sm" href="{{ route('admin.orders.invoice', $o['id']) }}" aria-label="Print invoice for {{ $o['number'] }}">
                                    <x-admin.icon name="print" :size="16" />
                                </a>
                                <button type="button" class="icon-btn icon-btn--sm" aria-label="More actions for {{ $o['number'] }}">
                                    <x-admin.icon name="more" :size="16" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="empty-state" x-show="visibleIds.length === 0" x-cloak>No orders with this status in the selected date range.</div>
    </div>

    {{-- Desktop: footer + pagination --}}
    <div class="table-footer hide-sm">
        <div class="cluster" style="--gap: 10px">
            <span x-text="showingDesktop">Showing 1–{{ count($rows) }} of {{ DemoData::number($tabCounts['all']) }}</span>
            <label for="orders-per-page" class="visually-hidden">Rows per page</label>
            <select id="orders-per-page" class="select select--xs orders-per-page">
                <option>25 per page</option><option>50 per page</option><option>100 per page</option>
            </select>
        </div>
        <nav class="pagination" aria-label="Pagination">
            <button type="button" class="pagination__btn" aria-label="Previous page" :disabled="page === 1" @click="page = Math.max(1, page - 1)" disabled>
                <x-admin.icon name="chevron-left" :size="16" />
            </button>
            @foreach ([1, 2, 3] as $n)
                <button type="button" class="pagination__btn" @if ($n === 1) aria-current="page" @endif
                    :aria-current="page === {{ $n }} ? 'page' : null" @click="page = {{ $n }}">{{ $n }}</button>
            @endforeach
            <span class="pagination__gap" aria-hidden="true">…</span>
            <button type="button" class="pagination__btn" :aria-current="page === 57 ? 'page' : null" @click="page = 57">57</button>
            <button type="button" class="pagination__btn" aria-label="Next page" :disabled="page === 57" @click="page = page === 3 ? 57 : Math.min(57, page + 1)">
                <x-admin.icon name="chevron-right" :size="16" />
            </button>
        </nav>
    </div>

    {{-- Phone: order cards --}}
    <section class="orders-cards show-sm" aria-label="Orders">
        <div class="orders-cards__meta">
            <span x-text="showingPhone">Showing 5 of {{ $todayCounts['all'] }} orders today</span>
            <span>Newest first</span>
        </div>

        <div class="orders-cards__empty" x-show="visibleIds.length === 0" x-cloak>No orders in this status right now.</div>

        @foreach ($rows as $i => $o)
            <article class="ord-card" x-show="inPage('{{ $o['id'] }}')" @if ($i >= 5) x-cloak @endif aria-labelledby="ord-card-{{ $o['id'] }}">
                <div class="ord-card__row">
                    <a id="ord-card-{{ $o['id'] }}" class="ord-card__id" href="{{ route('admin.orders.show', $o['id']) }}">{{ $o['number'] }}</a>
                    <span class="chip chip--{{ $tone($o['status']) }}"
                        :class="chipClass('{{ $o['id'] }}')" x-text="statusLabel('{{ $o['id'] }}')">{{ Status::label($o['status']) }}</span>
                </div>
                <div class="ord-card__meta">
                    <span>{{ $o['card_time'] }}</span>
                    <span>{{ $o['source'] }}</span>
                    <span class="ord-card__phone"><x-admin.icon name="phone" :size="14" :stroke="2" />{{ $o['phone'] }}</span>
                </div>
                <p class="ord-card__items"><strong>{{ $o['items_label'] }}</strong> · {{ $o['items_summary'] }}</p>
                <div class="ord-card__foot">
                    <span @class(['ord-card__pay', 'is-paid' => $o['paid']])>{{ $o['pay_label'] }}</span>
                    <span class="ord-card__amount">{{ DemoData::money($o['amount']) }}</span>
                </div>
                <div class="ord-card__actions">
                    <button type="button" class="btn btn--brand ord-card__primary"
                        x-show="nextAction('{{ $o['id'] }}')" @click="advance('{{ $o['id'] }}')"
                        @unless (isset($nextActions[$o['status']])) x-cloak @endunless>
                        <x-admin.icon name="check" :size="18" :stroke="2.4" />
                        <span x-text="nextAction('{{ $o['id'] }}')">{{ $nextActions[$o['status']] ?? '' }}</span>
                    </button>
                    <a class="btn btn--outline-strong ord-card__primary" href="{{ route('admin.orders.show', $o['id']) }}"
                        x-show="!nextAction('{{ $o['id'] }}')" @if (isset($nextActions[$o['status']])) x-cloak @endif>View details</a>
                    @if ($o['phone_full'])
                        <a class="btn btn--outline-strong ord-card__call" href="tel:{{ str_replace('-', '', $o['phone_full']) }}" aria-label="Call customer {{ $o['phone'] }}">
                            <x-admin.icon name="phone" :size="18" :stroke="2" class="text-brand" />Call
                        </a>
                    @endif
                    <a class="icon-btn ord-card__more" href="{{ route('admin.orders.show', $o['id']) }}" aria-label="More actions for order {{ $o['number'] }}">
                        <x-admin.icon name="more" :size="20" />
                    </a>
                </div>
            </article>
        @endforeach

        <button type="button" class="btn btn--outline-strong btn--block orders-cards__more" x-show="visibleIds.length > limit" @click="limit += 5">Load more orders</button>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const FLOW = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        const FILTERS = { q: '', source: 'All sources', method: 'All methods', payment: 'Any', courier: 'All couriers', from: '2026-09-28', to: '2026-10-04' };
        const TONES = ['amber', 'blue', 'violet', 'cyan', 'green', 'red', 'gray'];
        const label = (s) => s.charAt(0).toUpperCase() + s.slice(1);

        window.Alpine.data('ordersIndex', (cfg) => ({
            rows: cfg.rows,
            tabCounts: cfg.tabCounts,
            todayCounts: cfg.todayCounts,
            tones: cfg.tones,
            nextActions: cfg.nextActions,
            tab: cfg.tab,
            sel: cfg.selected.slice(),
            moved: {},
            undoMoved: null,
            toast: '',
            toastTimer: null,
            filters: { ...FILTERS, q: cfg.q },
            filtersOpen: false,
            limit: 5,
            page: 1,

            status(id) { return this.moved[id] || this.rows.find((r) => r.id === id).status; },
            statusLabel(id) { return label(this.status(id)); },
            // Object syntax so the server-rendered tone class is removed when the status changes.
            chipClass(id) {
                const tone = this.tones[this.status(id)] || 'gray';
                return Object.fromEntries(TONES.map((t) => ['chip--' + t, t === tone]));
            },
            matches(id) {
                const q = this.filters.q.trim().toLowerCase().replace(/^#/, '');
                const row = this.rows.find((r) => r.id === id);
                return (this.tab === 'all' || this.status(id) === this.tab) && (q === '' || row.search.includes(q));
            },
            get visibleIds() { return this.rows.filter((r) => this.matches(r.id)).map((r) => r.id); },
            inPage(id) { const i = this.visibleIds.indexOf(id); return i !== -1 && i < this.limit; },
            setTab(key) { this.tab = key; this.limit = 5; this.page = 1; },

            // Tab count = design count, adjusted by the local status changes on this page.
            count(set, key) {
                const base = this[set][key];
                if (key === 'all') return base;
                const now = this.rows.filter((r) => this.status(r.id) === key).length;
                const before = this.rows.filter((r) => r.status === key).length;
                return base + now - before;
            },
            fmt(n) { return Number(n).toLocaleString('en-IN'); },
            get showingDesktop() {
                const n = this.visibleIds.length;
                return 'Showing ' + (n ? '1–' + n : '0') + ' of ' + this.fmt(this.count('tabCounts', this.tab));
            },
            get showingPhone() {
                const shown = Math.min(this.limit, this.visibleIds.length);
                return 'Showing ' + shown + ' of ' + this.count('todayCounts', this.tab) + (this.tab === 'all' ? ' orders today' : ' ' + this.tab);
            },

            // Selection (desktop)
            isSel(id) { return this.sel.includes(id); },
            toggle(id) { this.sel = this.isSel(id) ? this.sel.filter((x) => x !== id) : this.sel.concat([id]); },
            get selCount() { return this.visibleIds.filter((id) => this.isSel(id)).length; },
            get allSelected() { return this.visibleIds.length > 0 && this.selCount === this.visibleIds.length; },
            toggleAll() {
                const ids = this.visibleIds;
                this.sel = this.allSelected ? this.sel.filter((id) => !ids.includes(id)) : [...new Set(this.sel.concat(ids))];
            },
            clearSel() { this.sel = []; },

            // Status changes with undo
            nextAction(id) { return this.nextActions[this.status(id)] || ''; },
            commit(moved, message) {
                this.undoMoved = this.moved;
                this.moved = moved;
                this.toast = message;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => { this.toast = ''; }, 8000);
            },
            advance(id) {
                const next = FLOW[FLOW.indexOf(this.status(id)) + 1];
                if (!next) return;
                this.commit({ ...this.moved, [id]: next }, '#' + id + ' moved to ' + label(next) + (next === 'confirmed' ? ' · SMS sent' : ''));
            },
            bulkConfirm() {
                const ids = this.visibleIds.filter((id) => this.isSel(id) && this.status(id) === 'pending');
                if (!ids.length) { this.toast = 'Only pending orders can be confirmed.'; return; }
                const moved = { ...this.moved };
                ids.forEach((id) => { moved[id] = 'confirmed'; });
                this.commit(moved, ids.length + (ids.length === 1 ? ' order' : ' orders') + ' marked confirmed · SMS sent');
                this.sel = this.sel.filter((id) => !ids.includes(id));
            },
            undo() {
                if (this.undoMoved) this.moved = this.undoMoved;
                this.undoMoved = null;
                this.toast = '';
            },
            resetFilters() { this.filters = { ...FILTERS }; },
        }));
    });
</script>
@endpush
