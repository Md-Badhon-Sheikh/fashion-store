{{--
    Customers — Customers.dc.html.
    Data: $stats, $customers (CustomersData::rows), $blocked (initially blocked ids), $blockedTotal, $total.
    Alpine (customersPage, script at the bottom):
        sel       selected customer id (row button / View) -> detail panel on the right (below on narrow screens)
        blocked   blocked customer ids; "Block / Unblock customer" toggles, chips, avatars and the KPI follow
    Each detail panel has its own small scope: revealed (phone number), draft + notes (Save note).
    Phones: the table scrolls horizontally and the detail panel stacks under the list.
--}}
@extends('admin.layouts.app')

@section('title', 'Customers')

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/customers.css') }}">
@endpush

@php
    $initial = $customers[0]['id'];
    $isBlocked = fn (string $id) => in_array($id, $blocked, true);
    $cfg = ['sel' => $initial, 'blocked' => $blocked, 'blockedTotal' => $blockedTotal];
@endphp

@section('content')
<div class="cust" x-data="customersPage(@js($cfg))">

    <x-admin.page-header title="Customers" subtitle="Website accounts and POS walk-in customers matched by phone number">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">Send SMS to segment</button>
            <button type="button" class="btn btn--outline">
                <x-admin.icon name="download" :size="18" />Export
            </button>
            <button type="button" class="btn btn--brand">
                <x-admin.icon name="plus" :size="18" :stroke="2" />Add customer
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="kpi-grid">
        @foreach ($stats as $k)
            <div class="kpi">
                <div class="kpi__label">{{ $k['label'] }}</div>
                <div class="kpi__value" @if ($k['key'] === 'blocked') x-text="blockedCount" @endif>{{ $k['value'] }}</div>
                <div @class(['kpi__sub', 'kpi__sub--'.$k['sub_tone'] => $k['sub_tone']])>{{ $k['sub'] }}</div>
            </div>
        @endforeach
    </div>

    <form class="filter-bar" aria-label="Filter customers" @submit.prevent>
        <label class="field field--wide">Name, phone or email
            <span class="input-group">
                <x-admin.icon name="search" :size="16" :stroke="2" />
                <input type="search" placeholder="017XXXXXXXX">
            </span>
        </label>
        <label class="field">Source
            <select class="select select--sm"><option>Online + POS</option><option>Online</option><option>POS</option></select>
        </label>
        <label class="field">Status
            <select class="select select--sm"><option>Any status</option><option>Active</option><option>Blocked</option></select>
        </label>
        <label class="field">Orders
            <select class="select select--sm"><option>Any</option><option>1 order</option><option>2–5 orders</option><option>6+ orders</option></select>
        </label>
        <label class="field">Joined
            <select class="select select--sm"><option>Any time</option><option>This month</option><option>Last 90 days</option><option>This year</option></select>
        </label>
        <label class="field cust-sort">Sort by
            <select class="select select--sm"><option>Last order, newest</option><option>Total spent, high to low</option><option>Most orders</option><option>Recently joined</option></select>
        </label>
    </form>

    <div class="row-wrap" style="--gap: 18px">
        {{-- Customer list --}}
        <section class="cust-list stack stack--md" aria-label="Customer list">
            <div class="table-card">
                <table class="table cust-table" style="--table-min: 820px">
                    <caption class="visually-hidden">Customers</caption>
                    <thead>
                        <tr>
                            <th scope="col">Customer</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Email</th>
                            <th scope="col" class="num">Orders</th>
                            <th scope="col" class="num">Total spent</th>
                            <th scope="col">Last order</th>
                            <th scope="col">Source</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $cu)
                            @php($id = $cu['id'])
                            @php($blk = $isBlocked($id))
                            <tr @class(['cust-row', 'is-selected' => $id === $initial]) :class="{ 'is-selected': sel === '{{ $id }}' }">
                                <td>
                                    <button type="button" class="cust-who" @click="pick('{{ $id }}')"
                                        aria-pressed="{{ $id === $initial ? 'true' : 'false' }}" :aria-pressed="(sel === '{{ $id }}').toString()"
                                        aria-controls="cust-panel-{{ $id }}">
                                        <span @class(['avatar avatar--sm cust-avatar', 'avatar--danger' => $blk]) :class="{ 'avatar--danger': isBlocked('{{ $id }}') }" aria-hidden="true">{{ $cu['initials'] }}</span>
                                        <span class="cust-who__text">
                                            <span class="cust-who__name">{{ $cu['name'] }}</span>
                                            <span class="cell-sub">Since {{ $cu['since'] }}</span>
                                        </span>
                                    </button>
                                </td>
                                <td class="nowrap">{{ $cu['phone_masked'] }}</td>
                                <td><span @class(['cust-email', 'muted' => $cu['email'] === 'No email'])>{{ $cu['email'] }}</span></td>
                                <td class="num fw-600">{{ $cu['orders'] }}</td>
                                <td class="num cell-strong">{{ DemoData::money($cu['spent']) }}</td>
                                <td class="nowrap">{{ $cu['last_order'] }}</td>
                                <td><span class="tag">{{ $cu['source'] }}</span></td>
                                <td>
                                    <span class="chip chip--{{ $blk ? 'red' : 'green' }}" :class="{ 'chip--red': isBlocked('{{ $id }}'), 'chip--green': !isBlocked('{{ $id }}') }"
                                        x-text="isBlocked('{{ $id }}') ? 'Blocked' : 'Active'">{{ $blk ? 'Blocked' : 'Active' }}</span>
                                </td>
                                <td class="cell-actions">
                                    <button type="button" class="btn btn--outline-strong btn--xs text-brand" @click="pick('{{ $id }}')" aria-label="View {{ $cu['name'] }}">View</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span>Showing 1–{{ count($customers) }} of {{ DemoData::number($total) }}</span>
                <nav class="pagination" aria-label="Pagination" x-data="{ page: 1 }">
                    <button type="button" class="pagination__btn" aria-label="Previous page" :disabled="page === 1" @click="page--" disabled><x-admin.icon name="chevron-left" :size="16" /></button>
                    @foreach ([1, 2, 3] as $n)
                        <button type="button" class="pagination__btn" @if ($n === 1) aria-current="page" @endif :aria-current="page === {{ $n }} ? 'page' : null" @click="page = {{ $n }}">{{ $n }}</button>
                    @endforeach
                    <button type="button" class="pagination__btn" aria-label="Next page" :disabled="page === 3" @click="page++"><x-admin.icon name="chevron-right" :size="16" /></button>
                </nav>
            </div>
        </section>

        {{-- Detail panel (one per customer, only the selected one is shown) --}}
        <aside class="cust-detail card card--flush" aria-label="Customer details" x-ref="detail">
            @foreach ($customers as $cu)
                @php($id = $cu['id'])
                @php($blk = $isBlocked($id))
                <div id="cust-panel-{{ $id }}" class="cust-panel" x-show="sel === '{{ $id }}'" @style(['display: none' => $id !== $initial])
                    x-data="{ revealed: false, draft: '', notes: [] }">

                    <div class="cust-panel__head">
                        <span @class(['avatar avatar--lg cust-avatar-lg', 'avatar--danger' => $blk]) :class="{ 'avatar--danger': isBlocked('{{ $id }}') }" aria-hidden="true">{{ $cu['initials'] }}</span>
                        <div class="cust-panel__who">
                            <div class="cluster">
                                <h2 class="cust-panel__name">{{ $cu['name'] }}</h2>
                                <span class="chip chip--sm chip--{{ $blk ? 'red' : 'green' }}" :class="{ 'chip--red': isBlocked('{{ $id }}'), 'chip--green': !isBlocked('{{ $id }}') }"
                                    x-text="isBlocked('{{ $id }}') ? 'Blocked' : 'Active'">{{ $blk ? 'Blocked' : 'Active' }}</span>
                            </div>
                            <div class="fs-13 muted mt-4">{{ $cu['source'] }} customer since {{ $cu['since'] }}</div>
                        </div>
                    </div>

                    <dl class="cust-stats">
                        <div class="cust-stat"><dt>Orders</dt><dd>{{ $cu['orders'] }}</dd></div>
                        <div class="cust-stat"><dt>Total spent</dt><dd>{{ DemoData::money($cu['spent']) }}</dd></div>
                        <div class="cust-stat"><dt>Avg. order</dt><dd>{{ DemoData::money($cu['avg_order']) }}</dd></div>
                        <div class="cust-stat"><dt>Returns / refused COD</dt><dd @class(['text-danger' => $cu['returns'] > 0])>{{ $cu['returns'] }}</dd></div>
                    </dl>

                    <div class="cust-section">
                        <h3 class="cust-section__title">Contact</h3>
                        <div class="split" style="--gap: 8px">
                            <span class="cust-contact">
                                <x-admin.icon name="phone" :size="16" class="muted" />
                                <span x-text="revealed ? @js($cu['phone']) : @js($cu['phone_masked'])">{{ $cu['phone_masked'] }}</span>
                            </span>
                            <button type="button" class="btn btn--outline-strong btn--xs cust-reveal" x-show="!revealed" @click="revealed = true">Reveal &amp; call</button>
                            <a class="btn btn--outline-strong btn--xs cust-reveal" x-show="revealed" x-cloak href="tel:{{ str_replace('-', '', $cu['phone']) }}">
                                <x-admin.icon name="phone" :size="14" :stroke="2" class="text-brand" />Call
                            </a>
                        </div>
                        <span @class(['cust-contact', 'muted' => $cu['email'] === 'No email'])>
                            <x-admin.icon name="mail" :size="16" class="muted" />{{ $cu['email'] }}
                        </span>
                        <div class="fs-12 muted">SMS opt-in: Yes · Account: {{ $cu['account'] }}</div>
                    </div>

                    <div class="cust-section">
                        <h3 class="cust-section__title">Addresses</h3>
                        @foreach ($cu['addresses'] as $ad)
                            <div class="cust-address">
                                <div class="split" style="--gap: 8px"><span class="fw-700">{{ $ad['label'] }}</span><span class="fs-12 muted">{{ $ad['tag'] }}</span></div>
                                <div class="text-2">{{ $ad['line'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="cust-section cust-section--tight">
                        <div class="split split--baseline">
                            <h3 class="cust-section__title">Order history</h3>
                            <a class="card__link" href="{{ route('admin.orders.index', ['q' => $cu['phone_masked']]) }}">All orders →</a>
                        </div>
                        <ul class="cust-history">
                            @foreach ($cu['history'] as $h)
                                <li class="cust-history__row">
                                    <span class="thumb cust-history__thumb" style="--tone: {{ $h['tone'] }}">
                                        <img src="{{ $h['image_url'] }}" alt="{{ $h['name'] }}" loading="lazy">
                                    </span>
                                    <div class="cust-history__main">
                                        <a class="fw-700 fs-13" href="{{ $h['exists'] ? route('admin.orders.show', $h['id']) : route('admin.orders.index', ['q' => $h['id']]) }}">{{ $h['number'] }}</a>
                                        <div class="cust-history__meta">{{ $h['date'] }} · {{ $h['name'] }}</div>
                                    </div>
                                    <div class="cust-history__side">
                                        <span class="fw-700 fs-13 tabular">{{ DemoData::money($h['amount']) }}</span>
                                        <x-admin.status-chip :status="$h['status']" class="cust-history__chip" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="cust-section">
                        <label for="cust-note-{{ $id }}" class="cust-section__title">Notes</label>
                        <template x-for="(n, i) in notes" :key="i">
                            <div class="note-box"><span x-text="n.text"></span><div class="fs-12 muted mt-4" x-text="n.by"></div></div>
                        </template>
                        @foreach ($cu['notes'] as $n)
                            <div class="note-box">{{ $n['text'] }}<div class="fs-12 muted mt-4">{{ $n['by'] }}</div></div>
                        @endforeach
                        <textarea id="cust-note-{{ $id }}" class="textarea fs-13" rows="2" placeholder="Add a note only staff can see" x-model="draft"></textarea>
                        <button type="button" class="btn btn--primary btn--sm cust-save-note"
                            @click="if (draft.trim()) { notes.unshift({ text: draft.trim(), by: 'Admin · just now' }); draft = ''; }">Save note</button>
                    </div>

                    <div class="cust-section cust-section--last">
                        <div class="grid-2" style="--gap: 8px">
                            <a class="btn btn--outline-strong" href="sms:{{ str_replace('-', '', $cu['phone']) }}">Send SMS</a>
                            <a class="btn btn--outline-strong" href="{{ route('admin.pos') }}">New order</a>
                        </div>
                        <button type="button" @class(['btn cust-block', 'is-blocked' => $blk]) :class="{ 'is-blocked': isBlocked('{{ $id }}') }"
                            aria-pressed="{{ $blk ? 'true' : 'false' }}" :aria-pressed="isBlocked('{{ $id }}').toString()" @click="toggleBlock('{{ $id }}')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"></circle><path d="M5.6 5.6l12.8 12.8"></path></svg>
                            <span x-text="isBlocked('{{ $id }}') ? 'Unblock customer' : 'Block customer'">{{ $blk ? 'Unblock customer' : 'Block customer' }}</span>
                        </button>
                        <p class="fs-12 muted" x-text="isBlocked('{{ $id }}') ? hintBlocked : hintActive">
                            {{ $blk ? 'Blocked: cannot place COD orders on the website or by phone. POS sales still allowed.' : 'Blocking stops COD orders from this phone number on the website and by phone. POS sales are still allowed.' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('customersPage', (cfg) => ({
            sel: cfg.sel,
            blocked: cfg.blocked.slice(),
            initialBlocked: cfg.blocked.length,
            hintBlocked: 'Blocked: cannot place COD orders on the website or by phone. POS sales still allowed.',
            hintActive: 'Blocking stops COD orders from this phone number on the website and by phone. POS sales are still allowed.',

            get blockedCount() { return cfg.blockedTotal + this.blocked.length - this.initialBlocked; },
            isBlocked(id) { return this.blocked.includes(id); },
            toggleBlock(id) {
                this.blocked = this.isBlocked(id) ? this.blocked.filter((x) => x !== id) : this.blocked.concat([id]);
            },
            pick(id) {
                this.sel = id;
                // When the panel sits under the list (narrow screens), bring it into view.
                this.$nextTick(() => {
                    const r = this.$refs.detail.getBoundingClientRect();
                    if (r.top > window.innerHeight - 80) this.$refs.detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
        }));
    });
</script>
@endpush
