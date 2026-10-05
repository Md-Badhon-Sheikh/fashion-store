{{--
    Accounting — Accounting.dc.html.
    Alpine component `accountingPage` (script at the bottom):
      range    oct | sep | h6 — recalculates the KPI tiles, income-by-channel and expenses-by-category
               (all figures are pre-computed per range in App\Support\Demo\AccountingData::byRange())
      tx       all | income | expense — filters the transaction history
      form     "Add expense" fields; Save adds the expense to the top of "Recent expenses"
    Chart: admin.partials.bar-chart with income (series 1, blue) and expense (series 2, orange) bars
    paired per month by the .acc-chart rules in css/pages/accounting.css.
--}}
@extends('admin.layouts.app')

@section('title', 'Accounting')

@use('App\Support\DemoData')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/accounting.css') }}">
@endpush

@php
    $pluck = fn (string $field) => array_map(fn ($r) => $r[$field], $byRange);
    $d = $byRange[$defaultRange];
    $meta = $rangeMeta[$defaultRange];

    // Grouped bars: income + expense bar per month; the month label sits on the first bar of each pair.
    $bars = [];
    foreach ($months as $m) {
        $bars[] = ['x' => $m['label'], 'tip' => $m['full'].' · Income '.$m['income_text'], 'segments' => [['series' => 'online', 'value' => $m['income']]]];
        $bars[] = ['x' => '', 'tip' => $m['full'].' · Expenses '.$m['expense_text'], 'segments' => [['series' => 'pos', 'value' => $m['expense']]]];
    }
@endphp

@section('content')
<div class="acc" x-data="accountingPage">

    <x-admin.page-header title="Accounting">
        <div class="page-header__subtitle">Income, expenses and money owed · <span x-text="meta[range].text">{{ $meta['text'] }}</span></div>
        <x-slot:actions>
            <div class="acc-range">
                <div class="segmented" role="group" aria-label="Date range">
                    @foreach ($rangeOptions as $key => $label)
                        <button type="button" class="segmented__btn"
                            aria-pressed="{{ $key === $defaultRange ? 'true' : 'false' }}" :aria-pressed="(range === '{{ $key }}' && !custom).toString()"
                            @click="range = '{{ $key }}'; custom = false">{{ $label }}</button>
                    @endforeach
                    <button type="button" class="segmented__btn" aria-pressed="false" :aria-pressed="custom.toString()"
                        @click="custom = true; $nextTick(() => $refs.from.focus())">Custom</button>
                </div>
                <div class="acc-dates">
                    <label><span class="visually-hidden">From date</span><input type="date" class="input" x-ref="from" value="{{ $meta['from'] }}" :value="meta[range].from" @change="custom = true"></label>
                    <span class="muted">to</span>
                    <label><span class="visually-hidden">To date</span><input type="date" class="input" value="{{ $meta['to'] }}" :value="meta[range].to" @change="custom = true"></label>
                </div>
                <button type="button" class="btn btn--outline"><x-admin.icon name="download" :size="18" />Export</button>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="acc-note" role="note">
        <x-admin.icon name="info" :size="18" :stroke="2" />
        <span><strong>Basic financial tracking — not a replacement for full accounting software.</strong> Figures come from orders, POS day-close, courier settlements and expenses entered here. Share the export with your accountant for VAT and tax filing.</span>
    </div>

    {{-- KPI tiles (range-aware ones swap with `range`) --}}
    <div class="kpi-grid" aria-live="polite">
        <x-admin.kpi label="Income (sales received)" :value="$pluck('income')" :sub="$pluck('income_sub')" class="acc-kpi" />
        <x-admin.kpi label="Expenses" :value="$pluck('expense')" sub="Rent, salary, marketing and other running costs" class="acc-kpi" />
        <x-admin.kpi label="Gross profit" :value="$pluck('gross')" :sub="$pluck('gross_sub')" class="acc-kpi" />
        <x-admin.kpi label="Net profit" :value="$pluck('net')" sub="Gross profit − expenses" :tone="$d['net_negative'] ? 'danger' : 'success'" class="acc-kpi"
            x-bind:class="{ 'kpi--danger': R[range].net_negative, 'kpi--success': !R[range].net_negative }" />
        @foreach ($balances as $b)
            <x-admin.kpi :label="$b['label']" :value="$b['value']" :sub="$b['sub']" :tone="$b['tone']" class="acc-kpi" />
        @endforeach
    </div>

    <div class="row-wrap">
        {{-- Income vs expenses chart --}}
        <x-admin.card class="acc-col-3" title="Income vs expenses, last 6 months" subtitle="Monthly totals, ৳ lakh · expenses exclude cost of goods">
            <x-slot:action>
                <div class="legend">
                    <span class="legend__item"><span class="legend__swatch legend__swatch--online"></span>Income</span>
                    <span class="legend__item"><span class="legend__swatch legend__swatch--pos"></span>Expenses</span>
                </div>
            </x-slot:action>
            <div class="acc-chart">
                @include('admin.partials.bar-chart', [
                    'bars' => $bars, 'max' => 24, 'ticks' => ['24L', '16L', '8L', '0'],
                    'height' => 210, 'minWidth' => 480, 'barMax' => 30, 'gap' => 4,
                    'label' => 'Monthly income and expenses, April to September 2026, in lakh Taka',
                ])
            </div>
            <details class="acc-details">
                <summary>View as table</summary>
                <div class="table-wrap">
                    <table class="table table--compact" style="--table-min: 420px">
                        <caption class="visually-hidden">Income and expenses by month, ৳ lakh</caption>
                        <thead><tr><th scope="col">Month</th><th scope="col" class="num">Income</th><th scope="col" class="num">Expenses</th><th scope="col" class="num">Difference</th></tr></thead>
                        <tbody>
                            @foreach ($months as $m)
                                <tr><td>{{ $m['full'] }}</td><td class="num">{{ $m['income_text'] }}</td><td class="num">{{ $m['expense_text'] }}</td><td class="num fw-700">{{ $m['diff_text'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </x-admin.card>

        {{-- Income by channel --}}
        <section class="card acc-col-2" aria-labelledby="acc-channel-title">
            <div class="card__head card__head--tight">
                <div>
                    <h2 id="acc-channel-title" class="card__title">Income by channel</h2>
                    <div class="card__subtitle" x-text="meta[range].text">{{ $meta['text'] }}</div>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table acc-channels" style="--table-min: 400px">
                    <thead><tr><th scope="col">Channel</th><th scope="col" class="num">Amount</th><th scope="col">Share</th></tr></thead>
                    <tbody>
                        @foreach ($d['channels'] as $i => $c)
                            <tr>
                                <td><span class="fw-600">{{ $c['name'] }}</span><span class="cell-sub">{{ $c['note'] }}</span></td>
                                <td class="num cell-strong" x-text="R[range].channels[{{ $i }}].amount">{{ $c['amount'] }}</td>
                                <td class="acc-share">
                                    <span class="acc-share__track" role="presentation"><span class="acc-share__fill" style="width: {{ $c['w'] }}%"></span></span>
                                    <span class="acc-share__pct">{{ $c['pct'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total income</td>
                            <td class="num fw-800" x-text="R[range].income">{{ $d['income'] }}</td>
                            <td class="acc-share__pct fw-500">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>

    <div class="row-wrap">
        {{-- Expenses by category + recent expenses --}}
        <section class="card acc-col-3 card--stack acc-expenses" aria-labelledby="acc-exp-title">
            <div class="split split--baseline">
                <h2 id="acc-exp-title" class="card__title">Expenses by category</h2>
                <span class="fs-13 muted"><span x-text="meta[range].text">{{ $meta['text'] }}</span> · total <span x-text="R[range].expense">{{ $d['expense'] }}</span></span>
            </div>
            <div class="hbars acc-cats">
                @foreach ($d['cats'] as $i => $c)
                    <div class="hbar acc-cat">
                        <span class="hbar__label">{{ $c['name'] }}</span>
                        <div class="hbar__track" title="{{ $c['tip'] }}" :title="R[range].cats[{{ $i }}].tip" role="presentation">
                            <div class="hbar__fill" style="width: {{ $c['w'] }}%" :style="{ width: R[range].cats[{{ $i }}].w + '%' }"></div>
                        </div>
                        <span class="hbar__value fw-700" x-text="R[range].cats[{{ $i }}].amount">{{ $c['amount'] }}</span>
                    </div>
                @endforeach
            </div>

            <div>
                <h3 class="acc-h3">Recent expenses</h3>
                <div class="table-wrap">
                    <table class="table acc-table" style="--table-min: 600px">
                        <thead><tr><th scope="col">Date</th><th scope="col">Category</th><th scope="col">Note</th><th scope="col">Method</th><th scope="col" class="num">Amount</th><th scope="col">Receipt</th></tr></thead>
                        <tbody>
                            <template x-for="(e, i) in added" :key="e.id">
                                <tr class="acc-new">
                                    <td class="nowrap" x-text="e.date"></td>
                                    <td><span class="acc-tag" x-text="e.cat"></span></td>
                                    <td x-text="e.note || '—'"></td>
                                    <td class="text-2" x-text="e.method"></td>
                                    <td class="num cell-strong" x-text="e.amount"></td>
                                    <td><a href="#add-expense" class="fs-13 fw-600" x-text="e.receipt"></a></td>
                                </tr>
                            </template>
                            @foreach ($expenses as $e)
                                <tr>
                                    <td class="nowrap">{{ $e['date'] }}</td>
                                    <td><span class="acc-tag">{{ $e['cat'] }}</span></td>
                                    <td>{{ $e['note'] }}</td>
                                    <td class="text-2">{{ $e['method'] }}</td>
                                    <td class="num cell-strong">{{ $e['amount'] }}</td>
                                    <td><a href="#receipt" class="fs-13 fw-600">{{ $e['receipt'] }}<span class="visually-hidden"> receipt for {{ $e['note'] }}</span></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- Add expense --}}
        <form id="add-expense" class="card acc-col-add stack acc-form" style="--gap: 14px" aria-labelledby="acc-add-title" @submit.prevent="saveExpense()">
            <h2 id="acc-add-title" class="card__title">Add expense</h2>
            <div class="row-wrap" style="--gap: 12px">
                <label class="field text-2 acc-f">Date *
                    <input type="date" class="input acc-input" x-model="form.date" value="2026-10-04" required>
                </label>
                <label class="field text-2 acc-f">Category *
                    <select class="select acc-input" x-model="form.cat" required>
                        @foreach ($expenseCategories as $c)
                            <option @selected($c === 'Marketing')>{{ $c }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="row-wrap" style="--gap: 12px">
                <label class="field text-2 acc-f">Amount *
                    <span class="acc-amount"><span class="acc-amount__cur">৳</span><input type="text" inputmode="numeric" x-model="form.amount" value="15,000" required></span>
                </label>
                <label class="field text-2 acc-f">Payment method *
                    <select class="select acc-input" x-model="form.method" required>
                        @foreach ($paymentMethods as $m)
                            <option @selected($m === 'Card')>{{ $m }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="field text-2">Note
                <textarea class="textarea" rows="2" x-model="form.note">Facebook ads — Puja campaign boost</textarea>
            </label>
            <div class="field">
                <span class="field__label text-2" id="acc-attach-l">Attachment</span>
                <label class="acc-upload">
                    <input type="file" class="visually-hidden" accept=".jpg,.jpeg,.png,.pdf" aria-labelledby="acc-attach-l" @change="form.file = $event.target.files.length ? $event.target.files[0].name : ''">
                    <x-admin.icon name="upload" :size="20" :stroke="2" />
                    <span class="fw-600" x-text="form.file || 'Upload receipt or invoice'">Upload receipt or invoice</span>
                    <span class="fs-12 muted">JPG, PNG or PDF up to 5 MB</span>
                </label>
            </div>
            <p class="alert alert--danger" x-show="error" x-text="error" x-cloak role="alert"></p>
            <p class="alert alert--success" x-show="saved" x-text="saved" x-cloak role="status"></p>
            <div class="cluster acc-form__actions">
                <button type="button" class="btn btn--outline" @click="clearForm()">Clear</button>
                <button type="submit" class="btn btn--brand">Save expense</button>
            </div>
        </form>
    </div>

    {{-- Transaction history --}}
    <section id="transactions" class="card" aria-labelledby="acc-tx-title">
        <div class="card__head acc-tx-head">
            <div>
                <h2 id="acc-tx-title" class="card__title">Transaction history</h2>
                <div class="card__subtitle">Cash + bank + mobile wallet, newest first · opening balance {{ $opening['date'] }}: {{ DemoData::money($opening['amount']) }}</div>
            </div>
            <div class="acc-seg" role="group" aria-label="Transaction type">
                @foreach (['all' => 'All', 'income' => 'Income', 'expense' => 'Expense'] as $key => $label)
                    <button type="button" class="acc-seg__btn" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}" :aria-pressed="(tx === '{{ $key }}').toString()"
                        @click="tx = '{{ $key }}'">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="table-wrap">
            <table class="table acc-table" style="--table-min: 860px">
                <thead>
                    <tr><th scope="col">Date</th><th scope="col">Type</th><th scope="col">Description</th><th scope="col">Ref</th><th scope="col">Method</th><th scope="col" class="num">Amount</th><th scope="col" class="num">Balance</th></tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $t)
                        <tr x-show="tx === 'all' || tx === '{{ $t['type'] }}'">
                            <td class="nowrap">{{ $t['date'] }}</td>
                            <td><span class="chip chip--sm {{ $t['type'] === 'income' ? 'chip--green' : 'chip--red' }}">{{ ucfirst($t['type']) }}</span></td>
                            <td>{{ $t['desc'] }}</td>
                            <td class="mono text-2">{{ $t['ref'] }}</td>
                            <td class="text-2">{{ $t['method'] }}</td>
                            <td class="num cell-strong {{ $t['type'] === 'income' ? 'text-success' : 'acc-out' }}">{{ $t['amount'] }}</td>
                            <td class="num">{{ $t['balance'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const R = @js($byRange);
    const META = @js($rangeMeta);
    const DEFAULT_FORM = { date: '2026-10-04', cat: 'Marketing', amount: '15,000', method: 'Card', note: 'Facebook ads — Puja campaign boost', file: '' };
    const fmt = (n) => new Intl.NumberFormat('en-IN').format(Math.round(n));
    const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    Alpine.data('accountingPage', () => ({
        R,
        meta: META,
        range: @js($defaultRange),
        custom: false,
        tx: 'all',
        added: [],
        form: { ...DEFAULT_FORM },
        error: '',
        saved: '',

        saveExpense() {
            const amount = parseFloat(String(this.form.amount).replace(/[^0-9.]/g, '')) || 0;
            if (!this.form.date || amount <= 0) {
                this.error = 'Enter a date and an amount above ৳0.';
                this.saved = '';
                return;
            }
            const [y, m, d] = this.form.date.split('-').map(Number);
            this.added.unshift({
                id: Date.now(),
                date: d + ' ' + MONTHS[m - 1] + ' ' + y,
                cat: this.form.cat,
                note: this.form.note.trim(),
                method: this.form.method,
                amount: '৳' + fmt(amount),
                receipt: this.form.file ? 'View' : 'Add',
            });
            this.error = '';
            this.saved = 'Expense of ৳' + fmt(amount) + ' added to recent expenses.';
        },
        clearForm() {
            this.form = { date: DEFAULT_FORM.date, cat: 'Rent', amount: '', method: 'Cash', note: '', file: '' };
            this.error = '';
            this.saved = '';
        },
    }));
});
</script>
@endpush
