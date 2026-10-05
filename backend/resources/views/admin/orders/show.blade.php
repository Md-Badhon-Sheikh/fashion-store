{{--
    Order detail — OrderDetail.dc.html (desktop) + the order-detail part of M-AdminOrders.dc.html (phone, < 640px:
    summary card, vertical stepper, stacked cards, sticky bottom action bar above the tab bar).
    Data: $order (DemoData::orders() shape), $detail (OrdersData::detail), $flow (status flow).
    Alpine (orderDetail, script at the bottom):
        status / idx    current status and reached step; "Update status", the phone "Update status to …"
                        button and "Save & mark as Shipped" move the stepper and log an activity line
        choice          value of the status select (options are built from the current step)
        added           activity lines added on this page (shown above the server-rendered history)
--}}
@extends('admin.layouts.app')

@section('title', 'Order '.$order['number'])

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/orders.css') }}">
@endpush

@section('mobile_actions')
    <button type="button" class="icon-btn icon-btn--ghost" aria-label="Copy order link"
        @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href)">
        <x-admin.icon name="copy" :size="20" />
    </button>
    <a class="icon-btn icon-btn--ghost" href="{{ route($detail['is_pos'] ? 'admin.orders.receipt' : 'admin.orders.invoice', $order['id']) }}" aria-label="Print {{ $detail['is_pos'] ? 'receipt' : 'invoice' }}">
        <x-admin.icon name="print" :size="20" />
    </a>
@endsection

@php
    $c = $order['customer'];
    $isPos = $detail['is_pos'];
    $closed = $isPos || in_array($order['status'], ['cancelled', 'returned'], true);
    $serverNext = $closed ? null : ($flow[$detail['step_index'] + 1] ?? null);
    $stepState = function (int $i) use ($order, $detail): string {
        $idx = $detail['step_index'];
        if ($i < $idx || ($i === $idx && in_array($order['status'], ['delivered', 'returned'], true))) {
            return 'done';
        }

        return $i === $idx ? 'now' : 'todo';
    };
    $cfg = [
        'id' => $order['id'],
        'status' => $order['status'],
        'idx' => $detail['step_index'],
        'flow' => $flow,
        'times' => $detail['step_times'],
        'tones' => Status::toneMap(),
        'pos' => $isPos, // in-store sales are complete at the counter: no status changes
    ];
    $printRoute = $isPos ? 'admin.orders.receipt' : 'admin.orders.invoice';
    $itemsLabel = $order['items_count'].' '.($order['items_count'] === 1 ? 'item' : 'items');
@endphp

@section('content')
<div class="od" x-data="orderDetail(@js($cfg))">

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.orders.index') }}">Orders</a><span aria-hidden="true">/</span><span aria-current="page">{{ $order['number'] }}</span>
    </nav>

    {{-- Desktop header: title, chips, print links, status update --}}
    <div class="od-head">
        <div class="od-head__text">
            <div class="od-head__title">
                <h1 class="page-header__title">Order {{ $order['number'] }}</h1>
                <span class="chip chip--{{ Status::tone($order['status']) }}" :class="chipClass()" x-text="label(status)">{{ Status::label($order['status']) }}</span>
                <span class="chip">Source: {{ $detail['source_label'] }}</span>
            </div>
            <div class="page-header__subtitle mt-0">Placed {{ $order['date'] }}, {{ $order['time'] }} · {{ $order['payment_method_label'] }} · {{ $itemsLabel }}</div>
        </div>
        <div class="od-head__actions">
            @if ($isPos)
                <a class="btn btn--outline" href="{{ route('admin.orders.receipt', $order['id']) }}">Print receipt</a>
                <a class="btn btn--outline" href="{{ route('admin.orders.invoice', $order['id']) }}">Print invoice</a>
            @else
                <a class="btn btn--outline" href="{{ route('admin.orders.invoice', $order['id']) }}">Print invoice</a>
                <a class="btn btn--outline" href="{{ route('admin.orders.packing-slip', $order['id']) }}">Packing slip</a>
            @endif
            <label for="od-status" class="visually-hidden">Change status</label>
            <select id="od-status" class="select" x-model="choice" :disabled="!options.length" @disabled($closed)>
                <template x-for="o in options" :key="o.value">
                    <option :value="o.value" x-text="o.label" :selected="o.value === choice"></option>
                </template>
            </select>
            <button type="button" class="btn btn--brand" :disabled="!options.length" @click="updateStatus(choice)" @disabled($closed)>Update status</button>
            <button type="button" class="btn btn--danger" :disabled="!canCancel" @click="cancelOrder()" @disabled(! in_array($order['status'], ['pending', 'confirmed', 'processing', 'shipped'], true))>Cancel order</button>
        </div>
    </div>

    <p class="visually-hidden" role="status" aria-live="polite" x-text="announce"></p>

    {{-- Phone: summary card --}}
    <section class="card od-summary show-sm" aria-labelledby="od-title-sm">
        <h1 id="od-title-sm" class="visually-hidden">Order {{ $order['number'] }}</h1>
        <div class="fs-13 muted">{{ $order['date'] }}, {{ $order['time'] }} · {{ $detail['source_label'] }}</div>
        <div class="cluster">
            <span class="chip chip--{{ Status::tone($order['status']) }}" :class="chipClass()" x-text="label(status)">{{ Status::label($order['status']) }}</span>
            <span class="chip">Source: {{ $detail['source_label'] }}</span>
            <x-admin.status-chip :status="$order['payment_status']" :label="$order['payment_method'].' · '.$order['payment_status']" />
        </div>
        <div class="split" style="align-items: flex-end">
            <div class="fs-14 muted">{{ $order['payment_method_label'] }} · {{ $itemsLabel }}</div>
            <div class="od-summary__total">
                <div class="fs-12 muted">Total</div>
                <div class="od-summary__amount">{{ DemoData::money($order['amount']) }}</div>
            </div>
        </div>
    </section>

    {{-- Desktop: horizontal stepper --}}
    <ol class="steps hide-sm" aria-label="Order progress">
        @foreach ($flow as $i => $name)
            @php($state = $stepState($i))
            <li @class(['step', 'step--done' => $state === 'done', 'step--now' => $state === 'now'])
                :class="{ 'step--done': stepState({{ $i }}) === 'done', 'step--now': stepState({{ $i }}) === 'now' }"
                @if ($state === 'now') aria-current="step" @endif :aria-current="stepState({{ $i }}) === 'now' ? 'step' : null">
                <div class="step__bar"></div>
                <div class="step__name">
                    <span class="step__mark" x-show="stepState({{ $i }}) === 'done'" @style(['display: none' => $state !== 'done'])><x-admin.icon name="check" :size="14" :stroke="2.6" /></span>
                    <span class="step__dot" x-show="stepState({{ $i }}) === 'now'" @style(['display: none' => $state !== 'now']) aria-hidden="true"></span>
                    <span class="step__ring" x-show="stepState({{ $i }}) === 'todo'" @style(['display: none' => $state !== 'todo']) aria-hidden="true"></span>
                    {{ ucfirst($name) }}
                    <span class="visually-hidden" x-text="', ' + stepState({{ $i }})"></span>
                </div>
                <div class="step__time" x-text="stepTime({{ $i }})">{{ $detail['step_times'][$i] }}</div>
            </li>
        @endforeach
    </ol>

    {{-- Phone: vertical stepper --}}
    <section class="card show-sm" aria-labelledby="od-progress-sm">
        <h2 id="od-progress-sm" class="card__title" style="margin-bottom: 14px">Order progress</h2>
        <ol class="vsteps" aria-labelledby="od-progress-sm">
            @foreach ($flow as $i => $name)
                @php($state = $stepState($i))
                <li @class(['vstep', 'vstep--done' => $state === 'done', 'vstep--now' => $state === 'now'])
                    :class="{ 'vstep--done': stepState({{ $i }}) === 'done', 'vstep--now': stepState({{ $i }}) === 'now' }"
                    @if ($state === 'now') aria-current="step" @endif :aria-current="stepState({{ $i }}) === 'now' ? 'step' : null">
                    <div class="vstep__rail">
                        <span class="vstep__node">
                            <span x-show="stepState({{ $i }}) === 'done'" @style(['display: none' => $state !== 'done'])><x-admin.icon name="check" :size="14" :stroke="3" /></span>
                            <span class="vstep__pulse" x-show="stepState({{ $i }}) === 'now'" @style(['display: none' => $state !== 'now'])></span>
                        </span>
                        <span class="vstep__line"></span>
                    </div>
                    <div class="vstep__body">
                        <div class="vstep__head">
                            <span class="vstep__name">{{ ucfirst($name) }}<span class="visually-hidden" x-text="', ' + stepState({{ $i }})"></span></span>
                            <span class="vstep__time" x-text="stepTime({{ $i }})">{{ $detail['step_times'][$i] }}</span>
                        </div>
                        <div class="vstep__note">{{ $detail['step_notes'][$i] }}</div>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    <div class="od-cols">
        <div class="od-col od-col--main">
            {{-- Items + totals --}}
            <x-admin.card title="Items" class="od-card--items">
                <div class="table-wrap hide-sm">
                    <table class="table" style="--table-min: 560px">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Variant SKU</th>
                                <th scope="col" class="num">Price</th>
                                <th scope="col" class="num">Qty</th>
                                <th scope="col" class="num">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order['items'] as $it)
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="thumb thumb--sm" style="--tone: {{ $it['tone'] }}">
                                                @if ($it['image_url'])<img src="{{ $it['image_url'] }}" alt="{{ $it['name'] }}" loading="lazy">@endif
                                            </span>
                                            <span>
                                                <span class="product-cell__name">{{ $it['name'] }}</span>
                                                <span class="product-cell__meta">{{ $it['variant'] }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="mono">{{ $it['sku'] }}</td>
                                    <td class="num">{{ DemoData::money($it['price']) }}</td>
                                    <td class="num">{{ $it['qty'] }}</td>
                                    <td class="num cell-strong">{{ DemoData::money($it['total']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="od-items-sm show-sm">
                    @foreach ($order['items'] as $it)
                        <div class="od-item">
                            <span class="thumb" style="--tone: {{ $it['tone'] }}">
                                @if ($it['image_url'])<img src="{{ $it['image_url'] }}" alt="{{ $it['name'] }}" loading="lazy">@endif
                            </span>
                            <div class="od-item__body">
                                <div class="fw-700 fs-14">{{ $it['name'] }}</div>
                                <div class="fs-13 muted">{{ $it['variant'] }}</div>
                                <div class="mono text-2">{{ $it['sku'] }}</div>
                                <div class="od-item__line"><span class="text-2">{{ DemoData::money($it['price']) }} × {{ $it['qty'] }}</span><span class="fw-700">{{ DemoData::money($it['total']) }}</span></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="totals">
                    <div class="kv"><span class="kv__label">Subtotal</span><span>{{ DemoData::money($order['subtotal']) }}</span></div>
                    @if ($order['coupon'])
                        <div class="kv"><span class="kv__label">Coupon {{ $order['coupon']['code'] }}</span><span>{{ DemoData::money(-$order['coupon']['amount']) }}</span></div>
                    @endif
                    @if ($order['discount'] > 0)
                        <div class="kv"><span class="kv__label">Discount</span><span>{{ DemoData::money(-$order['discount']) }}</span></div>
                    @endif
                    @unless ($isPos)
                        <div class="kv"><span class="kv__label">Delivery ({{ $order['delivery_zone'] }})</span><span>{{ DemoData::money($order['delivery_fee']) }}</span></div>
                    @endunless
                    <div class="totals__grand"><span>Total</span><span>{{ DemoData::money($order['amount']) }}</span></div>
                    @if ($order['due'] > 0)
                        <div class="od-due"><span>{{ $order['payment_method'] === 'COD' ? 'Due on delivery (COD)' : 'Due' }}</span><span>{{ DemoData::money($order['due']) }}</span></div>
                    @elseif ($order['payment_status'] !== 'Refunded')
                        <div class="kv text-success fw-700"><span>Paid · {{ $order['payment_method'] }}</span><span>{{ DemoData::money($order['amount']) }}</span></div>
                    @endif
                </div>
            </x-admin.card>

            {{-- Activity timeline --}}
            <x-admin.card title="Activity" class="od-card--activity">
                <ol class="timeline">
                    <template x-for="(a, i) in added" :key="i">
                        <li class="timeline__item">
                            <span class="timeline__dot"></span>
                            <div><div class="timeline__what" x-text="a.what"></div><div class="timeline__meta" x-text="a.who + ' · ' + a.when"></div></div>
                        </li>
                    </template>
                    @foreach ($order['activity'] as $a)
                        <li class="timeline__item">
                            <span class="timeline__dot"></span>
                            <div><div class="timeline__what">{{ $a['what'] }}</div><div class="timeline__meta">{{ $a['who'] }} · {{ $a['when'] }}</div></div>
                        </li>
                    @endforeach
                </ol>
            </x-admin.card>

            {{-- Notifications log --}}
            <x-admin.card title="Notifications sent" class="od-card--notices">
                <x-slot:action>
                    <button type="button" class="btn btn--sm btn--outline-strong" @click="resend()">Resend SMS</button>
                </x-slot:action>
                <template x-for="(n, i) in resent" :key="'r' + i">
                    <div class="od-notice">
                        <span class="tag">SMS</span>
                        <span class="od-notice__text" x-text="n.text"></span>
                        <span class="fs-12 muted" x-text="n.when"></span>
                        <span class="od-notice__state"><x-admin.icon name="check" :size="14" :stroke="2.4" />Queued</span>
                    </div>
                </template>
                @foreach ($order['notifications'] as $n)
                    <div class="od-notice">
                        <span class="tag">{{ $n['channel'] }}</span>
                        <span class="od-notice__text">{{ $n['text'] }}</span>
                        <span class="fs-12 muted">{{ $n['when'] }}</span>
                        <span class="od-notice__state"><x-admin.icon name="check" :size="14" :stroke="2.4" />{{ $n['state'] }}</span>
                    </div>
                @endforeach
            </x-admin.card>
        </div>

        <div class="od-col od-col--side">
            {{-- Customer --}}
            <x-admin.card title="Customer" class="card--stack od-card--customer fs-14">
                <div class="fw-700">{{ $c['name'] }}</div>
                @if ($c['phone'])
                    <div class="text-2">{{ $c['phone'] }}@if ($c['email'] && $c['email'] !== 'No email') · {{ $c['email'] }}@endif</div>
                @endif
                <div class="muted">{{ $detail['customer_line'] }}</div>
                <div class="od-addr">
                    <div class="od-addr__label">{{ $isPos ? 'Sold at' : 'Shipping address' }}</div>
                    <div class="od-addr__text">{{ $isPos ? $c['area'] : $c['address'] }}</div>
                </div>
                @if ($c['phone'])
                    <div class="grid-2 show-sm">
                        <a class="btn btn--outline-strong" href="tel:{{ str_replace('-', '', $c['phone']) }}"><x-admin.icon name="phone" :size="18" :stroke="2" class="text-brand" />Call</a>
                        <a class="btn btn--outline-strong" href="sms:{{ str_replace('-', '', $c['phone']) }}"><x-admin.icon name="mail" :size="18" :stroke="2" class="text-brand" />Send SMS</a>
                    </div>
                @endif
            </x-admin.card>

            {{-- Courier (manual entry; API booking is Phase 2) --}}
            @if ($detail['show_courier'])
                <x-admin.card title="Courier" class="card--stack od-card--courier od-courier">
                    <x-slot:action><span class="od-tagline">Manual entry</span></x-slot:action>
                    <label class="field">Courier service
                        <select class="select">
                            @foreach ($detail['courier_options'] as $opt)
                                <option @selected($opt === $detail['courier_selected'])>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">Consignment / tracking ID
                        <input class="input" type="text" placeholder="e.g. SF-58213049" value="{{ $detail['tracking_value'] }}">
                    </label>
                    <div class="grid-2">
                        <label class="field">COD amount
                            <input class="input" type="text" inputmode="numeric" value="{{ DemoData::money($order['due']) }}">
                        </label>
                        <label class="field">Courier charge
                            <input class="input" type="text" inputmode="numeric" value="{{ DemoData::money($order['courier_charge']) }}">
                        </label>
                    </div>
                    <button type="button" class="btn btn--primary" @click="saveCourier()" x-text="courierLabel">{{ $detail['step_index'] < 3 && ! $closed ? 'Save & mark as Shipped' : 'Save courier details' }}</button>
                    <div class="od-courier__phase">
                        <button type="button" class="btn" disabled>Book via courier API</button>
                        <span class="od-tagline">Phase 2</span>
                    </div>
                </x-admin.card>
            @else
                <x-admin.card title="Delivery" class="card--stack od-card--courier fs-14">
                    <div class="kv"><span class="kv__label">Type</span><span class="kv__value">In-store sale</span></div>
                    <div class="muted">Handed over at the counter. No courier needed.</div>
                </x-admin.card>
            @endif

            {{-- Payment --}}
            <x-admin.card title="Payment" class="card--stack od-card--payment">
                <div class="kv"><span class="kv__label">Method</span><span class="kv__value">{{ $order['payment_method_label'] }}</span></div>
                <div class="kv" style="align-items: center"><span class="kv__label">Status</span><x-admin.status-chip :status="$order['payment_status']" size="sm" /></div>
                <div class="kv"><span class="kv__label">Courier settlement</span><span>{{ $isPos ? 'Not applicable' : $order['courier_settlement'] }}</span></div>
            </x-admin.card>

            {{-- Internal note --}}
            <section class="card card--stack od-card--note" style="--gap: 10px">
                <label for="od-note" class="od-note-label">Internal note</label>
                <textarea id="od-note" class="textarea" rows="3" placeholder="Only staff can see this"></textarea>
                @if ($order['note'])
                    <div class="note-box">{{ $order['note']['text'] }} — {{ $order['note']['by'] }}</div>
                @endif
            </section>
        </div>

        {{-- Phone: cancel at the end of the page --}}
        <button type="button" class="btn btn--danger btn--block show-sm od-cancel-sm" :disabled="!canCancel" @click="cancelOrder()"
            @disabled(! in_array($order['status'], ['pending', 'confirmed', 'processing', 'shipped'], true))>Cancel order</button>
    </div>

    {{-- Phone: sticky bottom action bar --}}
    <div class="od-actionbar" role="region" aria-label="Order actions">
        <div class="od-actionbar__status">
            <span>Current: <span class="fw-700 od-cur--{{ Status::tone($order['status']) }}" :class="curClass()" x-text="label(status)">{{ Status::label($order['status']) }}</span></span>
            <span>Next: <span class="fw-700 od-next" x-text="nextName ? label(nextName) : 'Order complete'">{{ $serverNext ? ucfirst($serverNext) : 'Order complete' }}</span></span>
        </div>
        <div class="od-actionbar__buttons">
            <a class="btn btn--outline-strong od-actionbar__print" href="{{ route($printRoute, $order['id']) }}">
                <x-admin.icon name="print" :size="20" />Print
            </a>
            <button type="button" class="btn btn--brand od-actionbar__update" :disabled="!nextName" @click="advance()"
                x-text="nextName ? 'Update status to ' + label(nextName) : label(status)"
                @disabled($serverNext === null)>{{ $serverNext ? 'Update status to '.ucfirst($serverNext) : Status::label($order['status']) }}</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const TONES = ['amber', 'blue', 'violet', 'cyan', 'green', 'red', 'gray'];
        const label = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';

        window.Alpine.data('orderDetail', (cfg) => ({
            flow: cfg.flow,
            tones: cfg.tones,
            status: cfg.status,
            idx: cfg.idx,
            reached: cfg.idx,
            choice: '',
            added: [],
            resent: [],
            announce: '',

            init() { this.choice = this.options.length ? this.options[0].value : ''; },
            label,
            get closed() { return cfg.pos || ['cancelled', 'returned'].includes(this.status); },
            get canCancel() { return ['pending', 'confirmed', 'processing', 'shipped'].includes(this.status); },
            get nextName() { return this.closed ? '' : (this.flow[this.idx + 1] || ''); },
            get options() {
                if (this.closed) return [];
                const opts = this.flow.slice(this.idx + 1).map((s) => ({ value: s, label: 'Mark as ' + label(s) }));
                if (this.idx > 0) opts.push({ value: this.flow[this.idx - 1], label: 'Back to ' + label(this.flow[this.idx - 1]) });
                return opts;
            },
            get courierLabel() { return this.idx < 3 && !this.closed ? 'Save & mark as Shipped' : 'Save courier details'; },

            stepState(i) {
                if (i < this.idx || (i === this.idx && ['delivered', 'returned'].includes(this.status))) return 'done';
                return i === this.idx ? 'now' : 'todo';
            },
            stepTime(i) {
                if (i > this.idx) return '—';
                return i <= this.reached ? cfg.times[i] : 'Just now';
            },
            tone() { return this.tones[this.status] || 'gray'; },
            chipClass() { const t = this.tone(); return Object.fromEntries(TONES.map((x) => ['chip--' + x, x === t])); },
            curClass() { const t = this.tone(); return Object.fromEntries(TONES.map((x) => ['od-cur--' + x, x === t])); },

            log(what) { this.added.unshift({ what, who: 'Admin', when: 'Just now' }); },
            updateStatus(to) {
                if (!to || to === this.status) return;
                const i = this.flow.indexOf(to);
                if (i < this.idx) this.reached = Math.min(this.reached, i);
                this.status = to;
                this.idx = i;
                this.log('Status changed to ' + label(to));
                this.announce = 'Status updated to ' + label(to);
                this.choice = this.options.length ? this.options[0].value : '';
            },
            advance() { if (this.nextName) this.updateStatus(this.nextName); },
            saveCourier() {
                if (this.idx < 3 && !this.closed) { this.log('Courier details saved'); this.updateStatus('shipped'); return; }
                this.log('Courier details saved');
                this.announce = 'Courier details saved';
            },
            cancelOrder() {
                if (!this.canCancel || !window.confirm('Cancel order #' + cfg.id + '? The customer gets an SMS.')) return;
                this.status = 'cancelled';
                this.log('Order cancelled · stock released');
                this.announce = 'Order cancelled';
                this.choice = '';
            },
            resend() {
                this.resent.unshift({ text: 'Your order #' + cfg.id + ' is ' + label(this.status).toLowerCase() + '.', when: 'Just now' });
                this.announce = 'SMS queued';
            },
        }));
    });
</script>
@endpush
