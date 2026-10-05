{{--
    Settings — Settings.dc.html.
    Alpine component `settingsPage` (script at the bottom):
      section      active entry of the left settings nav; set on click (smooth scroll to the
                   section) and by a scroll spy (IntersectionObserver) while scrolling
      paper        POS receipt paper width '80' | '58' (resizes the receipt preview)
      opts.r_vat   "Show VAT line on receipt" (also toggles the VAT line in the preview)
    Every other switch is a self-contained <x-admin.toggle>. Phase 2 gateways / courier APIs are disabled.
    < 768px: the section nav becomes a sticky, horizontally scrolling pill row.
--}}
@extends('admin.layouts.app')

@section('title', 'Settings')

@use('App\Support\DemoData')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/settings.css') }}">
@endpush

@php
    $firstSection = array_key_first($sections);
    $alpineConfig = [
        'sections' => array_keys($sections),
        'paper' => $invoice['paper'],
        'opts' => array_map(fn ($o) => $o[2], $receiptOptions),
    ];
@endphp

@section('content')
<div class="st" x-data="settingsPage">

    <x-admin.page-header title="Settings" subtitle="Store, delivery, payments, receipts and security · changes are recorded in the activity log">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">Discard</button>
            <button type="button" class="btn btn--brand">Save all changes</button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="st-layout">
        <nav class="st-nav side-nav" aria-label="Settings sections" x-ref="nav">
            @foreach ($sections as $id => $label)
                <a class="side-nav__link" href="#{{ $id }}" data-section="{{ $id }}"
                    aria-current="{{ $id === $firstSection ? 'true' : 'false' }}"
                    :aria-current="(section === '{{ $id }}').toString()"
                    @click.prevent="jump('{{ $id }}')">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="st-main">

            {{-- Store info --}}
            <x-admin.card id="store" class="st-section" title="Store info" subtitle="Shown on website footer, invoices and POS receipts">
                <div class="stack" style="--gap: 14px">
                    <div class="st-store">
                        <div class="st-logo">
                            <div class="upload-box st-logo__box">
                                <x-admin.icon name="image-add" :size="28" :stroke="1.6" />
                                <span>Logo<br>512 × 512 PNG</span>
                            </div>
                            <label class="btn btn--outline-strong btn--sm st-logo__btn" for="st-logo-file">Upload logo</label>
                            <input id="st-logo-file" class="visually-hidden" type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml">
                        </div>
                        <div class="form-grid st-store__fields">
                            <label class="field">Store name
                                <input class="input" type="text" name="store_name" value="{{ $store['name'] }}">
                            </label>
                            <label class="field">Phone (hotline)
                                <input class="input" type="tel" name="store_phone" value="{{ $store['phone'] }}">
                            </label>
                            <label class="field">Email
                                <input class="input" type="email" name="store_email" value="{{ $store['email'] }}">
                            </label>
                            <label class="field">Currency
                                <select class="select" name="currency">
                                    @foreach ($store['currencies'] as $o)<option>{{ $o }}</option>@endforeach
                                </select>
                            </label>
                            <label class="field">Number format
                                <select class="select" name="number_format">
                                    @foreach ($store['number_formats'] as $o)<option>{{ $o }}</option>@endforeach
                                </select>
                            </label>
                            <label class="field">Time zone
                                <select class="select" name="timezone">
                                    @foreach ($store['time_zones'] as $o)<option>{{ $o }}</option>@endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                    <label class="field">Shop address
                        <textarea class="textarea" rows="2" name="store_address">{{ $store['address'] }}</textarea>
                    </label>
                </div>
            </x-admin.card>

            {{-- Shipping zones --}}
            <x-admin.card id="shipping" class="st-section" title="Shipping zones" subtitle="Customer picks a zone at checkout; charge is added to the order">
                <x-slot:action>
                    <button type="button" class="btn btn--outline-strong st-btn-md">+ Add zone</button>
                </x-slot:action>
                <div class="stack" style="--gap: 14px">
                    <div class="table-wrap">
                        <table class="table" style="--table-min: 640px">
                            <thead>
                                <tr>
                                    <th scope="col">Zone</th>
                                    <th scope="col">Areas</th>
                                    <th scope="col" class="num">Charge</th>
                                    <th scope="col">Delivery time</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="num">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($zones as $z)
                                    <tr>
                                        <td class="fw-700">{{ $z['name'] }}</td>
                                        <td class="text-2">{{ $z['areas'] }}</td>
                                        <td class="num fw-700">{{ DemoData::money($z['fee']) }}</td>
                                        <td class="nowrap">{{ $z['days'] }}</td>
                                        <td><x-admin.status-chip :status="$z['status']" /></td>
                                        <td class="num"><button type="button" class="btn btn--outline-strong btn--xs" aria-label="Edit zone {{ $z['name'] }}">Edit</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="st-free">
                        <div class="st-free__switch">
                            <x-admin.toggle name="free_delivery" :checked="$freeDelivery['enabled']" label="Free delivery over threshold" :show-label="false" />
                            <span class="fs-14"><strong>Free delivery</strong> on orders over</span>
                        </div>
                        <label class="st-free__amount">
                            <span class="visually-hidden">Free delivery threshold</span>
                            <span class="fw-700" aria-hidden="true">৳</span>
                            <input class="input input--sm st-free__input" type="text" inputmode="numeric" name="free_delivery_threshold" value="{{ $freeDelivery['threshold'] }}">
                            <span class="muted">all zones</span>
                        </label>
                    </div>
                </div>
            </x-admin.card>

            {{-- Payment methods --}}
            <x-admin.card id="payment" class="st-section" title="Payment methods">
                <div class="st-rows">
                    @foreach ($payments as $p)
                        <div class="st-pay">
                            <div class="st-pay__main">
                                <span class="tag tag--caps st-pay__where">{{ $p['where'] }}</span>
                                <div class="st-pay__text">
                                    <div @class(['st-pay__name', 'muted' => $p['phase2']])>{{ $p['name'] }}</div>
                                    <div class="fs-12 muted">{{ $p['note'] }}</div>
                                </div>
                            </div>
                            <div class="st-pay__side">
                                @if ($p['phase2'])
                                    <span class="chip chip--phase">Phase 2</span>
                                @endif
                                <x-admin.toggle :name="'pay_'.$p['key']" :checked="$p['on']" :label="$p['name'].($p['phase2'] ? ' (available in Phase 2)' : '')" :show-label="false" :disabled="$p['phase2']" />
                            </div>
                        </div>
                    @endforeach
                </div>
                <label class="field st-merchant">bKash / Nagad merchant number (shown at POS for Send Money)
                    <input class="input" type="text" name="merchant_number" value="{{ $merchantNumber }}">
                </label>
            </x-admin.card>

            {{-- Courier --}}
            <section id="courier" class="card card--stack st-section" aria-labelledby="st-courier-title">
                <div class="split">
                    <div class="cluster" style="--gap: 10px">
                        <h2 id="st-courier-title" class="card__title">Courier</h2>
                        <span class="chip chip--phase">API: Phase 2</span>
                    </div>
                    <div class="cluster" style="--gap: 10px">
                        <span class="fs-14 fw-600" aria-hidden="true">Manual mode</span>
                        <x-admin.toggle name="courier_manual" :checked="true" label="Manual courier mode" :show-label="false" />
                    </div>
                </div>
                <p class="note-box st-note">Manual mode: staff pick the courier and type the consignment / tracking ID on each order. The tracking ID is sent to the customer in the Shipped SMS.</p>
                <label class="field st-narrow">Default courier
                    <select class="select" name="default_courier">
                        @foreach ($couriers as $c)<option>{{ $c }}</option>@endforeach
                    </select>
                </label>
                <div class="grid-auto st-couriers">
                    @foreach ($courierApis as $i => $c)
                        <div class="st-courier" role="group" aria-labelledby="st-courier-{{ $i }}">
                            <div class="split">
                                <span id="st-courier-{{ $i }}" class="fw-700">{{ $c['name'] }}</span>
                                <span class="chip chip--phase chip--sm st-courier__chip">Phase 2</span>
                            </div>
                            <label class="field field--muted-label">{{ $c['key1'] }}
                                <input class="input input--sm st-disabled" type="text" disabled placeholder="Not connected">
                            </label>
                            <label class="field field--muted-label">{{ $c['key2'] }}
                                <input class="input input--sm st-disabled" type="password" disabled placeholder="Not connected">
                            </label>
                            <button type="button" class="btn btn--sm" disabled>Connect</button>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Invoice & receipt --}}
            <section id="invoice" class="card st-section st-invoice" aria-labelledby="st-invoice-title">
                <div class="st-invoice__form stack stack--md">
                    <h2 id="st-invoice-title" class="card__title">Invoice &amp; receipt</h2>
                    <div class="form-grid" style="--min: 150px">
                        <label class="field">Online order prefix
                            <input class="input st-mono" type="text" name="online_prefix" value="{{ $invoice['online_prefix'] }}">
                        </label>
                        <label class="field">POS sale prefix
                            <input class="input st-mono" type="text" name="pos_prefix" value="{{ $invoice['pos_prefix'] }}">
                        </label>
                        <label class="field">Next online no.
                            <input class="input" type="text" inputmode="numeric" name="next_online" value="{{ $invoice['next_online'] }}">
                        </label>
                        <label class="field">Next POS no.
                            <input class="input" type="text" inputmode="numeric" name="next_pos" value="{{ $invoice['next_pos'] }}">
                        </label>
                    </div>
                    <label class="field">Footer note
                        <textarea class="textarea" rows="2" name="receipt_footer">{{ $invoice['footer'] }}</textarea>
                    </label>
                    <div class="stack" style="--gap: 6px">
                        <span class="field__label" id="st-paper-label">POS receipt paper width</span>
                        <div class="segmented st-paper" role="group" aria-labelledby="st-paper-label">
                            @foreach (['80', '58'] as $w)
                                <button type="button" class="segmented__btn" aria-pressed="{{ $w === $invoice['paper'] ? 'true' : 'false' }}"
                                    :aria-pressed="(paper === '{{ $w }}').toString()" @click="paper = '{{ $w }}'">{{ $w }} mm</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="st-rows">
                        @foreach ($receiptOptions as $key => [$label, $note, $on])
                            <div class="switch-row st-row">
                                <span class="fs-14">{{ $label }}</span>
                                <x-admin.toggle :name="$key" :checked="$on" :label="$label" :show-label="false" model="opts.{{ $key }}" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="st-preview" role="figure" aria-labelledby="st-preview-label">
                    <span id="st-preview-label" class="st-preview__label">Receipt preview · <span x-text="paper + ' mm'">{{ $invoice['paper'] }} mm</span></span>
                    <div class="st-receipt" :class="{ 'st-receipt--58': paper === '58' }">
                        <div class="st-receipt__brand">{{ $store['name'] }}</div>
                        <div class="st-receipt__center">{{ $store['address'] }} · {{ $store['phone'] }}</div>
                        <div class="st-receipt__rule"></div>
                        <div class="st-receipt__line"><span>{{ $receipt['number'] }}</span><span>{{ $receipt['date'] }}</span></div>
                        <div class="st-receipt__rule"></div>
                        @foreach ($receipt['lines'] as [$item, $amount])
                            <div class="st-receipt__line"><span>{{ $item }}</span><span>{{ $amount }}</span></div>
                        @endforeach
                        <div class="st-receipt__rule"></div>
                        <div class="st-receipt__line fw-700"><span>TOTAL</span><span>{{ $receipt['total'] }}</span></div>
                        <div class="st-receipt__line" x-show="opts.r_vat" @unless ($receiptOptions['r_vat'][2]) x-cloak @endunless><span>VAT {{ $tax['rate'] }}% (incl.)</span><span>{{ $receipt['vat'] }}</span></div>
                        <div class="st-receipt__line"><span>Cash</span><span>{{ $receipt['cash'] }}</span></div>
                        <div class="st-receipt__line"><span>Change</span><span>{{ $receipt['change'] }}</span></div>
                        <div class="st-receipt__rule"></div>
                        <div class="st-receipt__center">{{ $receipt['footer'] }}</div>
                    </div>
                </div>
            </section>

            {{-- Tax / VAT --}}
            <x-admin.card id="tax" class="st-section" title="Tax / VAT" subtitle="Off by default. Turn on if VAT is shown separately on invoices.">
                <x-slot:action>
                    <x-admin.toggle name="vat_enabled" :checked="$tax['enabled']" label="Enable VAT" :show-label="false" />
                </x-slot:action>
                <div class="form-grid" style="--min: 200px">
                    <label class="field">VAT rate (%)
                        <input class="input" type="text" inputmode="decimal" name="vat_rate" value="{{ $tax['rate'] }}">
                    </label>
                    <label class="field">Prices are
                        <select class="select" name="vat_mode">
                            @foreach ($tax['modes'] as $o)<option>{{ $o }}</option>@endforeach
                        </select>
                    </label>
                    <label class="field">BIN / VAT reg. no.
                        <input class="input" type="text" name="bin" placeholder="[BIN]">
                    </label>
                </div>
            </x-admin.card>

            {{-- Inventory --}}
            <x-admin.card id="inventory" class="st-section" title="Inventory">
                <div class="stack stack--md">
                    <div class="form-grid">
                        <label class="field">Default low-stock threshold (pcs per variant)
                            <input class="input" type="number" min="0" name="low_stock_threshold" value="{{ $inventory['threshold'] }}">
                        </label>
                        <label class="field">Reserve stock when order is
                            <select class="select" name="reserve_on">
                                @foreach ($inventory['reserve_on'] as $o)<option>{{ $o }}</option>@endforeach
                            </select>
                        </label>
                        <label class="field">Release reserved stock when
                            <select class="select" name="release_on">
                                @foreach ($inventory['release_on'] as $o)<option>{{ $o }}</option>@endforeach
                            </select>
                        </label>
                    </div>
                    <div class="st-rows">
                        @foreach ($inventoryOptions as $key => [$label, $note, $on])
                            <div class="switch-row st-row st-row--note">
                                <div>
                                    <div class="fs-14 fw-600">{{ $label }}</div>
                                    <div class="fs-12 muted">{{ $note }}</div>
                                </div>
                                <x-admin.toggle :name="$key" :checked="$on" :label="$label" :show-label="false" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>

            {{-- SEO & analytics --}}
            <x-admin.card id="seo" class="st-section" title="SEO & analytics" subtitle="Tracking fires purchase events from the order confirmation page">
                <div class="stack stack--md">
                    <label class="field">Default meta title
                        <input class="input" type="text" name="meta_title" value="{{ $seo['meta_title'] }}">
                    </label>
                    <label class="field">Default meta description
                        <textarea class="textarea" rows="2" name="meta_description">{{ $seo['meta_description'] }}</textarea>
                    </label>
                    <div class="form-grid" style="--min: 200px">
                        <label class="field">Facebook Pixel ID
                            <input class="input st-mono" type="text" name="fb_pixel" placeholder="e.g. 1234567890123456" inputmode="numeric">
                        </label>
                        <label class="field">Google Analytics 4 ID
                            <input class="input st-mono" type="text" name="ga4" placeholder="G-XXXXXXXXXX">
                        </label>
                        <label class="field">Google Tag Manager ID
                            <input class="input st-mono" type="text" name="gtm" placeholder="GTM-XXXXXXX">
                        </label>
                    </div>
                    <p class="fs-12 muted">Events sent: {{ $seo['events'] }}. Sitemap: /sitemap.xml (auto-updated).</p>
                </div>
            </x-admin.card>

            {{-- Security & backup --}}
            <x-admin.card id="security" class="st-section" title="Security & backup">
                <div class="stack stack--md">
                    <div class="st-rows">
                        @foreach ($securityOptions as $key => [$label, $note, $on])
                            <div class="switch-row st-row st-row--note">
                                <div>
                                    <div class="fs-14 fw-600">{{ $label }}</div>
                                    <div class="fs-12 muted">{{ $note }}</div>
                                </div>
                                <x-admin.toggle :name="$key" :checked="$on" :label="$label" :show-label="false" />
                            </div>
                        @endforeach
                    </div>
                    <div class="form-grid" style="--min: 180px">
                        <label class="field">Backup time
                            <select class="select" name="backup_time">
                                @foreach ($security['backup_times'] as $o)<option>{{ $o }}</option>@endforeach
                            </select>
                        </label>
                        <label class="field">Keep backups for
                            <select class="select" name="backup_retention">
                                @foreach ($security['retention'] as $o)<option>{{ $o }}</option>@endforeach
                            </select>
                        </label>
                        <label class="field">Admin session timeout
                            <select class="select" name="session_timeout">
                                @foreach ($security['timeouts'] as $o)<option>{{ $o }}</option>@endforeach
                            </select>
                        </label>
                        <label class="field">Lock after failed logins
                            <select class="select" name="lockout">
                                @foreach ($security['lockouts'] as $o)<option>{{ $o }}</option>@endforeach
                            </select>
                        </label>
                    </div>
                    <div class="st-backup">
                        <div class="st-backup__info">
                            <svg class="st-backup__icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><ellipse cx="12" cy="5.5" rx="7" ry="2.5"></ellipse><path d="M5 5.5v6c0 1.4 3.1 2.5 7 2.5s7-1.1 7-2.5v-6"></path><path d="M5 11.5v6c0 1.4 3.1 2.5 7 2.5s7-1.1 7-2.5v-6"></path></svg>
                            <div>
                                <div class="fw-700 fs-14">Last backup: {{ $security['last_backup'] }} <x-admin.status-chip status="completed" label="Successful" size="sm" class="st-backup__chip" /></div>
                                <div class="fs-12 muted">{{ $security['backup_meta'] }}</div>
                            </div>
                        </div>
                        <div class="cluster">
                            <button type="button" class="btn btn--outline-strong st-btn-md">Backup now</button>
                            <button type="button" class="btn btn--primary st-btn-md"><x-admin.icon name="download" :size="16" :stroke="2" />Download backup</button>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const cfg = @js($alpineConfig);
        const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        Alpine.data('settingsPage', () => ({
            section: cfg.sections[0],
            paper: cfg.paper,
            opts: cfg.opts,
            spyPaused: false,

            init() {
                // Scroll spy: highlight the section crossing the upper part of the viewport.
                if (!('IntersectionObserver' in window)) return;
                const observer = new IntersectionObserver((entries) => {
                    if (this.spyPaused) return;
                    entries.filter((e) => e.isIntersecting).forEach((e) => {
                        this.section = e.target.id;
                        this.revealPill(e.target.id);
                    });
                }, { rootMargin: '-20% 0px -70% 0px' });
                cfg.sections.forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) observer.observe(el);
                });
            },

            jump(id) {
                const el = document.getElementById(id);
                if (!el) return;
                this.section = id;
                this.spyPaused = true;
                el.scrollIntoView({ behavior: reduceMotion() ? 'auto' : 'smooth', block: 'start' });
                history.replaceState(null, '', '#' + id);
                clearTimeout(this.spyTimer);
                this.spyTimer = setTimeout(() => { this.spyPaused = false; }, 800);
                this.revealPill(id);
            },

            // Phone nav is a horizontal pill row: keep the active pill in view (no page scroll).
            revealPill(id) {
                const nav = this.$refs.nav;
                const link = nav && nav.querySelector('[data-section="' + id + '"]');
                if (link && nav.scrollWidth > nav.clientWidth) {
                    nav.scrollLeft += link.getBoundingClientRect().left - nav.getBoundingClientRect().left - 16;
                }
            },
        }));
    });
</script>
@endpush
