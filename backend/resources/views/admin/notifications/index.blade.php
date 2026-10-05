{{--
    SMS & email notifications — Notifications.dc.html.
    Alpine component `notifPage` (script at the bottom):
      section            active jump tab (templates | events | gateway | logs)
      ev[row][channel]   event × channel switches (null = not applicable)
      tplKey, lang       template being edited + language (en | bn)
      texts[key][lang]   editable template texts; placeholders such as {name} stay literal
      logStatus[i]       log row status (retry → queued); logCh / logSt filters
    Placeholders are plain single-brace text ({name}), so Blade never parses them;
    in Alpine they only live inside JS strings and x-text / :value bindings.
--}}
@extends('admin.layouts.app')

@section('title', 'SMS & email')

@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/notifications.css') }}">
@endpush

@php
    $tpl = $templates[$activeTemplate];
    $jumpTabs = ['templates' => 'Templates', 'events' => 'Event settings', 'gateway' => 'Gateway', 'logs' => 'Logs'];
    $logTone = ['delivered' => 'green', 'failed' => 'red', 'queued' => 'amber'];
    $onCount = 0;
    $totalCount = 0;
    foreach ($events as $e) {
        foreach ($e['on'] as $v) {
            if ($v !== null) { $totalCount++; $onCount += $v ? 1 : 0; }
        }
    }

    // Initial Alpine state (see the script at the bottom).
    $alpineConfig = [
        'events' => array_map(fn ($e) => $e['on'], $events),
        'templates' => $templates,
        'tplKey' => $activeTemplate,
        'sample' => $sample,
        'logs' => array_map(fn ($l) => ['status' => $l['status'], 'channel' => $l['channel']], $logs),
        'logTone' => $logTone,
    ];
@endphp

@section('content')
<div class="nt" x-data="notifPage">

    <x-admin.page-header title="SMS & email notifications" subtitle="Automatic messages to customers and admins on every order and account event">
        <x-slot:actions>
            <button type="button" class="btn btn--outline" @click="jump('templates'); $nextTick(() => $refs.testPhone.focus())">Send test message</button>
            <button type="button" class="btn btn--brand">Save changes</button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Section jump tabs (pill row on phones) --}}
    <nav class="tabs nt-tabs hide-sm" aria-label="Notification sections">
        @foreach ($jumpTabs as $id => $label)
            <a class="tab" href="#{{ $id }}" aria-current="{{ $id === 'templates' ? 'true' : 'false' }}"
                :aria-current="(section === '{{ $id }}').toString()" @click.prevent="jump('{{ $id }}')">{{ $label }}</a>
        @endforeach
    </nav>
    <nav class="pill-tabs show-sm" aria-label="Notification sections">
        @foreach ($jumpTabs as $id => $label)
            <a class="pill-tab nt-pill" href="#{{ $id }}" aria-current="{{ $id === 'templates' ? 'true' : 'false' }}"
                :aria-current="(section === '{{ $id }}').toString()" @click.prevent="jump('{{ $id }}')">{{ $label }}</a>
        @endforeach
    </nav>

    {{-- Status cards --}}
    <div class="grid-auto nt-stats">
        <section class="kpi nt-stat" aria-labelledby="nt-sms-balance">
            <div class="nt-stat__head">
                <h2 id="nt-sms-balance" class="kpi__label">SMS balance</h2>
                <x-admin.status-chip status="active" label="Connected" />
            </div>
            <div class="kpi__value">{{ number_format($stats['sms_left']) }} <span class="nt-unit">SMS left</span></div>
            <div class="nt-meter" role="img" aria-label="{{ $stats['sms_left_pct'] }}% of the SMS package left">
                <div class="nt-meter__fill" style="width: {{ $stats['sms_left_pct'] }}%"></div>
            </div>
            <div class="kpi__sub">Gateway: {{ $stats['sms_gateway'] }} · ≈ {{ $stats['sms_days_left'] }} days at current usage</div>
            <div class="cluster nt-stat__foot">
                <button type="button" class="btn btn--outline-strong btn--sm">Recharge</button>
                <span class="fs-12 fw-600 nt-warn">Alert below {{ $stats['sms_alert_below'] }} SMS</span>
            </div>
        </section>

        <section class="kpi nt-stat" aria-labelledby="nt-smtp-status">
            <div class="nt-stat__head">
                <h2 id="nt-smtp-status" class="kpi__label">Email (SMTP)</h2>
                <x-admin.status-chip status="active" />
            </div>
            <div class="kpi__value nt-stat__host">{{ $stats['smtp_host'] }}</div>
            <div class="kpi__sub">From: {{ $stats['smtp_from'] }} · Port {{ $stats['smtp_port'] }} · {{ $stats['smtp_encryption'] }}</div>
            <div class="kpi__sub">Last test: {{ $stats['smtp_last_test'] }}</div>
        </section>

        <section class="kpi" aria-labelledby="nt-sent-today">
            <h2 id="nt-sent-today" class="kpi__label">Sent today</h2>
            <div class="kpi__value">{{ $stats['sent_sms'] }} <span class="nt-unit">SMS</span> · {{ $stats['sent_email'] }} <span class="nt-unit">email</span></div>
            <div class="kpi__sub">{{ $stats['sent_breakdown'] }}</div>
        </section>

        <section class="kpi kpi--danger" aria-labelledby="nt-failed-today">
            <h2 id="nt-failed-today" class="kpi__label">Failed today</h2>
            <div class="kpi__value nt-failed">{{ $stats['failed'] }}</div>
            <div class="kpi__sub">{{ $stats['failed_note'] }}</div>
        </section>
    </div>

    {{-- Event × channel matrix --}}
    <x-admin.card id="events" class="nt-section" title="Event settings"
        subtitle="Choose who gets notified, on which channel, for each event. OTP messages are always sent by SMS.">
        <x-slot:action>
            <span class="fs-13 fw-600 text-2" aria-live="polite"><span x-text="onCount">{{ $onCount }}</span> of <span x-text="totalCount">{{ $totalCount }}</span> notifications on</span>
        </x-slot:action>
        <div class="table-wrap">
            <table class="table nt-matrix" style="--table-min: 720px">
                <thead>
                    <tr>
                        <th scope="col">Event</th>
                        @foreach ($channels as $ch)
                            <th scope="col" class="text-center">{{ $ch }}</th>
                        @endforeach
                        <th scope="col">Template</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $ri => $e)
                        <tr>
                            <th scope="row" class="nt-matrix__event">
                                <span class="nt-matrix__name">{{ $e['name'] }}</span>
                                <span class="cell-sub">{{ $e['note'] }}</span>
                            </th>
                            @foreach ($e['on'] as $ci => $v)
                                <td class="text-center">
                                    @if ($v === null)
                                        <span class="fs-13 muted">n/a</span>
                                    @else
                                        <button type="button" role="switch" class="switch"
                                            aria-label="{{ $e['name'] }}: {{ $channels[$ci] }}"
                                            aria-checked="{{ $v ? 'true' : 'false' }}"
                                            :aria-checked="ev[{{ $ri }}][{{ $ci }}].toString()"
                                            @click="toggleEvent({{ $ri }}, {{ $ci }})"></button>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if ($e['template'])
                                    <a class="fs-13 fw-600" href="#templates" @click.prevent="editTemplate('{{ $e['template'] }}')"
                                        aria-label="Edit template: {{ $e['name'] }}">Edit</a>
                                @else
                                    <a class="fs-13 fw-600" href="#templates" @click.prevent="jump('templates')"
                                        aria-label="Edit template: {{ $e['name'] }}">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>

    {{-- Template editor + preview --}}
    <div class="row-wrap">
        <section id="templates" class="card card--stack col-main nt-section nt-editor" aria-labelledby="nt-tpl-title">
            <div class="split">
                <div>
                    <h2 id="nt-tpl-title" class="card__title">Template: <span x-text="tpl.title">{{ $tpl['title'] }}</span> (SMS)</h2>
                    <div class="card__subtitle" x-text="tpl.subtitle">{{ $tpl['subtitle'] }}</div>
                </div>
                <div class="segmented nt-lang" role="group" aria-label="Template language">
                    <button type="button" class="segmented__btn" aria-pressed="true" :aria-pressed="(lang === 'en').toString()" @click="lang = 'en'">English</button>
                    <button type="button" class="segmented__btn" lang="bn" aria-pressed="false" :aria-pressed="(lang === 'bn').toString()" @click="lang = 'bn'">বাংলা</button>
                </div>
            </div>

            <label class="field" for="nt-tpl-select">Template
                <select id="nt-tpl-select" class="select" x-model="tplKey" @change="caret = null">
                    @foreach ($templates as $key => $t)
                        <option value="{{ $key }}" @selected($key === $activeTemplate)>{{ $t['option'] }}</option>
                    @endforeach
                </select>
            </label>

            <div class="stack" style="--gap: 6px">
                <span class="field__label" id="nt-ph-label">Insert placeholder</span>
                <div class="cluster" role="group" aria-labelledby="nt-ph-label">
                    @foreach ($placeholders as $p)
                        @php($token = sprintf('{%s}', $p))
                        <button type="button" class="nt-ph" @click="insert('{{ $p }}')" aria-label="Insert placeholder {{ $token }}">{{ $token }}</button>
                    @endforeach
                </div>
            </div>

            <div class="stack" style="--gap: 6px">
                <label class="field__label" for="nt-tpl-text">Message (<span x-text="lang === 'en' ? 'English' : 'Bangla'">English</span>)</label>
                <textarea id="nt-tpl-text" class="textarea nt-textarea" rows="5" x-ref="tpl"
                    :lang="lang" :value="text" @input="setText($event.target.value)"
                    @blur="rememberCaret($event.target)" @keyup="rememberCaret($event.target)" @click="rememberCaret($event.target)"
                    aria-describedby="nt-counter nt-tip">{{ $tpl['en'] }}</textarea>
                <div id="nt-counter" class="nt-counter" aria-live="polite">
                    <span><strong x-text="chars">{{ $sms['chars'] }}</strong> characters (with sample data)</span>
                    <span><strong x-text="parts">{{ $sms['parts'] }}</strong> SMS part(s) per message</span>
                    <span>Encoding: <strong x-text="unicode ? 'Unicode' : 'GSM 7-bit'">{{ $sms['unicode'] ? 'Unicode' : 'GSM 7-bit' }}</strong> · <span x-text="perPart">{{ $sms['per_part'] }}</span> chars per part</span>
                    <button type="button" class="btn btn--outline-strong nt-reset" @click="resetTemplate()">Reset to default</button>
                </div>
                <div id="nt-tip" class="alert alert--warn fs-12 nt-tip">Tip: Bangla text and the ৳ sign switch the SMS to Unicode (70 characters per part). In English templates write "Tk" to keep 160 characters per part.</div>
            </div>
        </section>

        <section class="card card--stack col-side" aria-labelledby="nt-preview-title">
            <h2 id="nt-preview-title" class="card__title">Preview</h2>
            <div class="nt-phone">
                <div class="nt-phone__from">YOURBRAND · Text message</div>
                <div class="nt-bubble" :lang="lang" x-text="preview">{{ $sms['preview'] }}</div>
                <div class="nt-phone__time">Today 2:14 PM</div>
            </div>
            <p class="fs-13 muted nt-sample">Sample data: order #{{ $sample['order_id'] }}, ৳{{ $sample['amount'] }}, {{ $sample['courier'] }}, tracking {{ $sample['tracking_id'] }}. Cost ≈ <span x-text="parts">{{ $sms['parts'] }}</span> SMS credit(s) per customer.</p>
            <form class="cluster nt-test" @submit.prevent>
                <label for="nt-test-phone" class="visually-hidden">Test phone number</label>
                <input id="nt-test-phone" x-ref="testPhone" class="input" type="tel" inputmode="tel" placeholder="01XXXXXXXXX" autocomplete="off">
                <button type="submit" class="btn btn--primary">Send test</button>
            </form>
        </section>
    </div>

    {{-- Gateway + SMTP --}}
    <div class="row-wrap">
        <section id="gateway" class="card card--stack nt-half nt-section" aria-labelledby="nt-gw-title">
            <div class="split">
                <h2 id="nt-gw-title" class="card__title">SMS gateway</h2>
                <x-admin.status-chip status="active" label="Connected" />
            </div>
            <label class="field">Provider
                <select class="select" name="sms_provider">
                    @foreach ($gateway['providers'] as $p)
                        <option>{{ $p }}</option>
                    @endforeach
                </select>
            </label>
            <div class="field">
                <label class="field__label" for="nt-api-key">API key</label>
                <div class="nt-inline">
                    <input id="nt-api-key" x-ref="apiKey" class="input" type="password" name="sms_api_key" value="{{ $gateway['api_key'] }}" autocomplete="off">
                    <button type="button" class="btn btn--outline-strong" @click="$refs.apiKey.value = ''; $refs.apiKey.focus()">Replace</button>
                </div>
            </div>
            <div class="form-grid" style="--min: 160px">
                <label class="field">Sender ID / masking
                    <input class="input" type="text" name="sms_sender_id" value="{{ $gateway['sender_id'] }}">
                </label>
                <label class="field">Type
                    <select class="select" name="sms_type">
                        @foreach ($gateway['types'] as $t)
                            <option>{{ $t }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="field">Low balance alert (SMS)
                <input class="input" type="number" name="sms_low_balance" value="{{ $gateway['low_balance'] }}" min="0">
            </label>
            <div class="nt-form-foot">
                <label class="field nt-grow">Test send to
                    <input class="input" type="tel" inputmode="tel" placeholder="01XXXXXXXXX" autocomplete="off">
                </label>
                <button type="button" class="btn btn--outline-strong">Send test SMS</button>
                <button type="button" class="btn btn--brand">Save gateway</button>
            </div>
        </section>

        <section class="card card--stack nt-half" aria-labelledby="nt-smtp-title">
            <div class="split">
                <h2 id="nt-smtp-title" class="card__title">Email (SMTP)</h2>
                <x-admin.status-chip status="active" />
            </div>
            <div class="form-grid" style="--min: 160px">
                <label class="field">SMTP host
                    <input class="input" type="text" name="smtp_host" value="{{ $smtp['host'] }}">
                </label>
                <label class="field">Port
                    <input class="input" type="text" inputmode="numeric" name="smtp_port" value="{{ $smtp['port'] }}">
                </label>
                <label class="field">Encryption
                    <select class="select" name="smtp_encryption">
                        @foreach ($smtp['encryptions'] as $enc)
                            <option>{{ $enc }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">Username
                    <input class="input" type="text" name="smtp_username" value="{{ $smtp['username'] }}" autocomplete="off">
                </label>
            </div>
            <label class="field">Password
                <input class="input" type="password" name="smtp_password" value="{{ $smtp['password'] }}" autocomplete="off">
            </label>
            <div class="form-grid" style="--min: 160px">
                <label class="field">From name
                    <input class="input" type="text" name="smtp_from_name" value="{{ $smtp['from_name'] }}">
                </label>
                <label class="field">From email
                    <input class="input" type="email" name="smtp_from_email" value="{{ $smtp['from_email'] }}">
                </label>
            </div>
            <div class="nt-form-foot">
                <button type="button" class="btn btn--outline-strong">Send test email</button>
                <button type="button" class="btn btn--brand">Save SMTP</button>
            </div>
        </section>
    </div>

    {{-- Send log --}}
    <section id="logs" class="card nt-section" aria-labelledby="nt-log-title">
        <div class="card__head nt-log-head">
            <h2 id="nt-log-title" class="card__title">Recent log</h2>
            <div class="cluster">
                <label for="nt-log-ch" class="visually-hidden">Channel</label>
                <select id="nt-log-ch" class="select select--sm nt-filter" x-model="logCh">
                    <option value="">All channels</option>
                    <option value="SMS">SMS</option>
                    <option value="Email">Email</option>
                </select>
                <label for="nt-log-st" class="visually-hidden">Status</label>
                <select id="nt-log-st" class="select select--sm nt-filter" x-model="logSt">
                    <option value="">All statuses</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                    <option value="queued">Queued</option>
                </select>
                <button type="button" class="btn btn--outline-strong btn--sm nt-export">
                    <x-admin.icon name="download" :size="16" />Export CSV
                </button>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table" style="--table-min: 760px">
                <thead>
                    <tr>
                        <th scope="col">Time</th>
                        <th scope="col">Channel</th>
                        <th scope="col">To</th>
                        <th scope="col">Event</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="num">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $i => $l)
                        <tr x-show="showLog({{ $i }})">
                            <td class="text-2 nowrap">{{ $l['time'] }}</td>
                            <td><span class="tag fw-700">{{ $l['channel'] }}</span></td>
                            <td class="nowrap">{{ $l['to'] }}</td>
                            <td>{{ $l['event'] }}<span class="cell-sub">{{ $l['detail'] }}</span></td>
                            <td>
                                <span class="chip chip--{{ $logTone[$l['status']] }}"
                                    :class="toneClass(logStatus[{{ $i }}])"
                                    x-text="label(logStatus[{{ $i }}])">{{ Status::label($l['status']) }}</span>
                            </td>
                            <td class="num">
                                @if ($l['status'] === 'failed')
                                    <button type="button" class="btn btn--danger btn--xs" x-show="logStatus[{{ $i }}] === 'failed'"
                                        @click="retry({{ $i }})" aria-label="Retry {{ $l['event'] }} to {{ $l['to'] }}">Retry</button>
                                    <span class="fs-13 muted" x-show="logStatus[{{ $i }}] !== 'failed'" x-cloak aria-label="No action">—</span>
                                @else
                                    <span class="fs-13 muted" aria-label="No action">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr x-show="visibleLogs === 0" x-cloak>
                        <td colspan="6">
                            <x-admin.empty-state icon="mail" title="No messages" message="No messages match these filters." />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="visually-hidden" aria-live="polite" x-text="announce"></p>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const cfg = @js($alpineConfig);

        // Template texts: { key: { en, bn } } (copied so "Reset to default" can restore cfg.templates).
        const freshTexts = () => Object.fromEntries(
            Object.entries(cfg.templates).map(([key, t]) => [key, { en: t.en, bn: t.bn }])
        );

        Alpine.data('notifPage', () => ({
            section: 'templates',
            ev: cfg.events.map((row) => row.slice()),
            tplKey: cfg.tplKey,
            lang: 'en',
            texts: freshTexts(),
            caret: null,
            logStatus: cfg.logs.map((l) => l.status),
            logTone: cfg.logTone,
            logCh: '',
            logSt: '',
            announce: '',

            /* Jump tabs */
            jump(id) {
                this.section = id;
                const el = document.getElementById(id);
                if (el) el.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
            },

            /* Event matrix */
            toggleEvent(r, c) { this.ev[r][c] = !this.ev[r][c]; },
            get onCount() { return this.ev.flat().filter((v) => v === true).length; },
            get totalCount() { return this.ev.flat().filter((v) => v !== null).length; },
            editTemplate(key) { this.tplKey = key; this.caret = null; this.jump('templates'); },

            /* Template editor */
            get tpl() { return cfg.templates[this.tplKey]; },
            get text() { return this.texts[this.tplKey][this.lang]; },
            setText(value) { this.texts[this.tplKey][this.lang] = value; },
            rememberCaret(el) { this.caret = [el.selectionStart, el.selectionEnd]; },
            insert(name) {
                const token = '{' + name + '}';
                const text = this.text;
                const start = this.caret ? this.caret[0] : text.length;
                const end = this.caret ? this.caret[1] : text.length;
                const before = text.slice(0, start);
                const pad = before && !/\s$/.test(before) ? ' ' : '';
                const head = before + pad + token;
                this.setText(head + text.slice(end));
                this.caret = [head.length, head.length];
                this.$nextTick(() => {
                    const el = this.$refs.tpl;
                    if (el) { el.focus(); el.setSelectionRange(head.length, head.length); }
                });
            },
            resetTemplate() {
                const t = cfg.templates[this.tplKey];
                this.texts[this.tplKey] = { en: t.en, bn: t.bn };
                this.caret = null;
            },

            /* Preview + SMS counter (GSM 7-bit: 160 / 153 per part, Unicode: 70 / 67 per part) */
            get preview() {
                return this.text.replace(/\{(\w+)\}/g, (match, key) => (key in cfg.sample ? cfg.sample[key] : match));
            },
            get unicode() { return /[^\x00-\x7F]/.test(this.preview); },
            get chars() { return Array.from(this.preview).length; },
            get parts() {
                const len = this.chars;
                return this.unicode ? (len <= 70 ? 1 : Math.ceil(len / 67)) : (len <= 160 ? 1 : Math.ceil(len / 153));
            },
            get perPart() { return this.unicode ? (this.parts > 1 ? 67 : 70) : (this.parts > 1 ? 153 : 160); },

            /* Send log */
            toneClass(status) {
                // Object syntax, so the server-rendered tone class is removed when the status changes.
                return Object.fromEntries(Object.entries(this.logTone).map(([key, tone]) => ['chip--' + tone, key === status]));
            },
            label(status) { return status.charAt(0).toUpperCase() + status.slice(1); },
            showLog(i) {
                return (!this.logCh || cfg.logs[i].channel === this.logCh) && (!this.logSt || this.logStatus[i] === this.logSt);
            },
            get visibleLogs() { return cfg.logs.filter((l, i) => this.showLog(i)).length; },
            retry(i) {
                this.logStatus[i] = 'queued';
                this.announce = 'Message queued for retry.';
            },
        }));
    });
</script>
@endpush
