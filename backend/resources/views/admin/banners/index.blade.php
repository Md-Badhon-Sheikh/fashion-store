{{--
    Banners & pages — Banners.dc.html.
    Alpine component `bannersPage` (script at the bottom):
      section            current item of the in-page section nav (anchor links; pill row on phones)
      slides[] / sel     home slider list: visibility switch, select to edit, drag, or focus the grip and press ↑/↓, to reorder,
                         add (max 6) / delete; the edit form works on a draft copy (Save / Cancel)
      ann                announcement bar: on/off, message + quick templates, colour, live preview
--}}
@extends('admin.layouts.app')

@section('title', 'Banners & pages')

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/banners.css') }}">
@endpush

@php
    $ann = $announcement;
    $annColour = $ann['colours'][$ann['colour']];
    $annText = $ann['templates'][0]['text'];
    $store = DemoData::storeUrl();
@endphp

@section('content')
<div class="bnr" x-data="bannersPage">

    <x-admin.page-header title="Banners & pages" subtitle="Manage the store's home sliders, promo banners, announcement bar, static pages and menus">
        <x-slot:actions>
            <a class="btn btn--outline" href="{{ $store }}" target="_blank" rel="noopener">
                <x-admin.icon name="eye" :size="18" />Preview store
            </a>
            <button type="button" class="btn btn--brand">Publish changes</button>
        </x-slot:actions>
    </x-admin.page-header>

    <nav class="bnr-nav" aria-label="Content sections">
        @foreach ($sections as $key => $s)
            <a href="#{{ $key }}" class="bnr-nav__link"
                aria-current="{{ $key === 'sliders' ? 'true' : 'false' }}" :aria-current="(section === '{{ $key }}').toString()"
                @click="section = '{{ $key }}'">
                {{ $s['label'] }}
                @if ($s['count'] !== '')<span class="bnr-nav__count">{{ $s['count'] }}</span>@endif
            </a>
        @endforeach
    </nav>

    {{-- Home sliders + slide editor --}}
    <div id="sliders" class="row-wrap bnr-anchor">
        <section class="card bnr-slides" aria-labelledby="bnr-slides-title">
            <div class="card__head bnr-head">
                <div>
                    <h2 id="bnr-slides-title" class="card__title">Home sliders</h2>
                    <div class="card__subtitle">Drag to change the order shown on the home page · max 6 slides</div>
                </div>
                <button type="button" class="btn btn--sm btn--outline-brand bnr-add" @click="addSlide()" :disabled="slides.length >= 6">
                    <x-admin.icon name="plus" :size="16" />Add slide
                </button>
            </div>
            <p id="bnr-reorder-hint" class="visually-hidden">Drag a slide, or focus its handle and press the up or down arrow key to move it.</p>
            <p class="visually-hidden" role="status" aria-live="polite" x-text="announce"></p>
            <ol class="bnr-slide-list" role="list">
                <template x-for="(s, i) in slides" :key="s.id">
                    <li class="bnr-slide" :class="{ 'is-selected': sel === s.id, 'is-dragging': dragId === s.id, 'is-over': overId === s.id && dragId !== s.id }"
                        draggable="true"
                        @dragstart="dragId = s.id; $event.dataTransfer.effectAllowed = 'move'"
                        @dragend="dragId = null; overId = null"
                        @dragover.prevent="overId = s.id"
                        @drop.prevent="dropOn(s.id)">
                        <button type="button" class="bnr-grip" :aria-label="'Reorder ' + s.title + ', position ' + (i + 1) + ' of ' + slides.length"
                            aria-describedby="bnr-reorder-hint"
                            @keydown.arrow-up.prevent="move(s.id, -1)" @keydown.arrow-down.prevent="move(s.id, 1)">
                            <svg width="16" height="20" viewBox="0 0 16 20" fill="currentColor" aria-hidden="true"><circle cx="5" cy="4" r="1.6"/><circle cx="11" cy="4" r="1.6"/><circle cx="5" cy="10" r="1.6"/><circle cx="11" cy="10" r="1.6"/><circle cx="5" cy="16" r="1.6"/><circle cx="11" cy="16" r="1.6"/></svg>
                        </button>
                        <span class="bnr-slide__pos" x-text="i + 1"></span>
                        <span class="bnr-slide__img" :style="'--tone:' + s.tone">
                            <span>Slide photo</span>
                            <template x-if="s.img"><img :src="s.img" :alt="s.title" loading="lazy"></template>
                        </span>
                        <span class="bnr-slide__text">
                            <span class="bnr-slide__title" x-text="s.title"></span>
                            <span class="bnr-slide__link" x-text="s.link"></span>
                            <span class="bnr-slide__schedule" x-text="s.schedule"></span>
                        </span>
                        <span class="chip" :class="'chip--' + tone(statusOf(s))" x-text="label(statusOf(s))"></span>
                        <button type="button" role="switch" class="switch switch--lg" :aria-checked="s.enabled.toString()" :aria-label="'Show ' + s.title"
                            @click="s.enabled = !s.enabled"></button>
                        <button type="button" class="btn btn--xs btn--outline-strong bnr-slide__edit" @click="pick(s.id)"
                            :aria-pressed="(sel === s.id).toString()" x-text="sel === s.id ? 'Editing' : 'Edit'"></button>
                    </li>
                </template>
            </ol>
            <x-admin.empty-state x-show="!slides.length" x-cloak icon="image" title="No slides" message="Add a slide to show it on the home page." />
        </section>

        <section class="card bnr-edit" aria-labelledby="bnr-edit-title" x-show="draft">
            <div class="split split--baseline">
                <h2 id="bnr-edit-title" class="card__title">Edit slide <span x-text="position(sel)"></span></h2>
                <button type="button" class="bnr-delete" @click="deleteSlide()">Delete slide</button>
            </div>
            <template x-if="draft">
                <form class="stack" style="--gap: 14px" @submit.prevent="saveSlide()">
                    <div class="bnr-edit__images">
                        <div class="bnr-edit__desktop">
                            <span class="field__label text-2">Desktop image · 1920 × 800</span>
                            <span class="bnr-shot bnr-shot--desktop" :style="'--tone:' + draft.tone">
                                <span>Desktop slide photo</span>
                                <template x-if="draft.img"><img :src="draft.img" :alt="draft.title + ', desktop'"></template>
                            </span>
                            <button type="button" class="btn btn--xs bnr-upload">Upload desktop image</button>
                        </div>
                        <div class="bnr-edit__mobile">
                            <span class="field__label text-2">Mobile · 800 × 1000</span>
                            <span class="bnr-shot bnr-shot--mobile" :style="'--tone:' + draft.tone">
                                <span>Mobile photo</span>
                                <template x-if="draft.img"><img :src="draft.img" :alt="draft.title + ', mobile'"></template>
                            </span>
                            <button type="button" class="btn btn--xs bnr-upload">Upload mobile</button>
                        </div>
                    </div>
                    <label class="field text-2">Heading
                        <input type="text" class="input bnr-input" x-model="draft.title">
                    </label>
                    <label class="field text-2">Sub text
                        <textarea class="textarea" rows="2" x-model="draft.sub"></textarea>
                    </label>
                    <div class="row-wrap" style="--gap: 12px">
                        <label class="field text-2 bnr-f1">Button text
                            <input type="text" class="input bnr-input" x-model="draft.btn">
                        </label>
                        <label class="field text-2 bnr-f2">Button link
                            <input type="text" class="input bnr-input bnr-mono" x-model="draft.link">
                        </label>
                    </div>
                    <div class="row-wrap" style="--gap: 12px">
                        <label class="field text-2 bnr-f1">Start
                            <input type="datetime-local" class="input bnr-input" x-model="draft.start">
                        </label>
                        <label class="field text-2 bnr-f1">End (blank = no end)
                            <input type="datetime-local" class="input bnr-input" x-model="draft.end">
                        </label>
                    </div>
                    <div class="cluster bnr-edit__actions">
                        <button type="button" class="btn btn--outline" @click="pick(sel)">Cancel</button>
                        <button type="submit" class="btn btn--brand">Save slide</button>
                    </div>
                </form>
            </template>
        </section>
    </div>

    {{-- Promo banners --}}
    <section id="promos" class="card bnr-anchor" aria-labelledby="bnr-promos-title">
        <div class="card__head bnr-head">
            <div>
                <h2 id="bnr-promos-title" class="card__title">Promo banners</h2>
                <div class="card__subtitle">Fixed banner spots on the home page and category pages</div>
            </div>
            <button type="button" class="btn btn--sm btn--outline-brand bnr-add"><x-admin.icon name="plus" :size="16" />Add banner</button>
        </div>
        <ul class="bnr-promos" role="list">
            @foreach ($promos as $p)
                <li class="bnr-promo">
                    <span class="bnr-promo__img" style="--tone: {{ $p['tone'] }}">
                        <span>Banner photo</span>
                        <img src="{{ $p['img'] }}" alt="{{ $p['title'] }}" loading="lazy">
                    </span>
                    <div class="bnr-promo__row">
                        <div class="bnr-promo__text">
                            <div class="fw-700">{{ $p['title'] }}</div>
                            <div class="fs-12 muted">{{ $p['place'] }} · {{ $p['schedule'] }}</div>
                        </div>
                        <x-admin.status-chip :status="$p['status']" size="sm" />
                    </div>
                    <div class="bnr-promo__actions">
                        <a href="#promos" class="fw-600">Edit</a>
                        <a href="#promos" class="muted">Replace image</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Announcement bar + live preview --}}
    <section id="announcement" class="card bnr-anchor bnr-ann" aria-labelledby="bnr-ann-title">
        <div class="bnr-ann__form">
            <div class="split">
                <h2 id="bnr-ann-title" class="card__title">Announcement bar</h2>
                <span class="bnr-ann__state">
                    <span id="bnr-ann-state" x-text="ann.on ? 'Showing' : 'Off'">{{ $ann['enabled'] ? 'Showing' : 'Off' }}</span>
                    <button type="button" role="switch" class="switch switch--lg" aria-label="Show announcement bar"
                        aria-checked="{{ $ann['enabled'] ? 'true' : 'false' }}" :aria-checked="ann.on.toString()" @click="ann.on = !ann.on"></button>
                </span>
            </div>
            <label class="field text-2">Message text
                <input type="text" class="input bnr-input" maxlength="{{ $ann['max'] }}" value="{{ $annText }}" x-model="ann.text" @input="ann.tpl = templateIndex()">
                <span class="field__hint"><span x-text="ann.text.length">{{ mb_strlen($annText) }}</span> / {{ $ann['max'] }} characters</span>
            </label>
            <div class="stack" style="--gap: 6px">
                <span class="field__label text-2" id="bnr-tpl-l">Quick templates</span>
                <div class="cluster" style="--gap: 6px" role="group" aria-labelledby="bnr-tpl-l">
                    @foreach ($ann['templates'] as $i => $t)
                        <button type="button" class="bnr-tpl" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" :aria-pressed="(ann.tpl === {{ $i }}).toString()"
                            @click="useTemplate({{ $i }})">{{ $t['label'] }}</button>
                    @endforeach
                </div>
            </div>
            <div class="row-wrap" style="--gap: 12px">
                <label class="field text-2 bnr-f2">Link (optional)
                    <input type="text" class="input bnr-input bnr-mono" value="{{ $ann['link'] }}">
                </label>
                <div class="field bnr-f1">
                    <span id="bnr-colour-l" class="field__label text-2">Bar colour</span>
                    <div role="radiogroup" aria-labelledby="bnr-colour-l" class="bnr-colours">
                        @foreach ($ann['colours'] as $key => $c)
                            <button type="button" role="radio" class="bnr-colour" aria-label="{{ $c['name'] }}" style="--swatch: {{ $c['bg'] }}"
                                aria-checked="{{ $key === $ann['colour'] ? 'true' : 'false' }}" :aria-checked="(ann.colour === '{{ $key }}').toString()"
                                tabindex="{{ $key === $ann['colour'] ? '0' : '-1' }}" :tabindex="ann.colour === '{{ $key }}' ? 0 : -1"
                                @click="ann.colour = '{{ $key }}'"
                                @keydown.arrow-right.prevent="cycleColour(1)" @keydown.arrow-down.prevent="cycleColour(1)"
                                @keydown.arrow-left.prevent="cycleColour(-1)" @keydown.arrow-up.prevent="cycleColour(-1)"></button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="cluster bnr-ann__checks" style="--gap: 0 20px">
                <label class="check"><input type="checkbox" checked>Show on all pages</label>
                <label class="check"><input type="checkbox" checked>Customer can close it</label>
                <label class="check"><input type="checkbox">Schedule start / end</label>
            </div>
        </div>

        <div class="bnr-ann__preview">
            <span class="field__label text-2">Live preview</span>
            <div class="bnr-store" role="group" aria-label="Store header preview">
                <div class="bnr-store__bar" x-show="ann.on" style="--bar-bg: {{ $annColour['bg'] }}; --bar-fg: {{ $annColour['fg'] }}"
                    :style="{ '--bar-bg': colours[ann.colour].bg, '--bar-fg': colours[ann.colour].fg }">
                    <span x-text="ann.text">{{ $annText }}</span>
                    <span class="bnr-store__close" aria-hidden="true">×</span>
                </div>
                <div class="bnr-store__head">
                    <span class="bnr-store__logo">YOUR BRAND</span>
                    <span class="bnr-store__nav">
                        @foreach ($ann['store_nav'] as $n)<span>{{ $n }}</span>@endforeach
                        <span class="bnr-store__sale">Sale</span>
                    </span>
                </div>
                <div class="bnr-store__hero">Home slider area</div>
            </div>
            <span class="fs-12 muted" x-show="!ann.on" x-cloak>The bar is switched off and will not appear on the store.</span>
        </div>
    </section>

    {{-- Static pages --}}
    <section id="pages" class="card bnr-anchor" aria-labelledby="bnr-pages-title">
        <div class="card__head bnr-head">
            <div>
                <h2 id="bnr-pages-title" class="card__title">Static pages</h2>
                <div class="card__subtitle">Linked from the store footer and checkout</div>
            </div>
            <button type="button" class="btn btn--sm btn--outline-brand bnr-add"><x-admin.icon name="plus" :size="16" />New page</button>
        </div>
        <div class="table-wrap">
            <table class="table bnr-pages" style="--table-min: 720px">
                <thead>
                    <tr>
                        <th scope="col">Page</th>
                        <th scope="col">URL</th>
                        <th scope="col">Last updated</th>
                        <th scope="col">Updated by</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $p)
                        <tr>
                            <td class="cell-strong">{{ $p['name'] }}</td>
                            <td class="mono bnr-url">{{ $p['url'] }}</td>
                            <td class="tabular nowrap">{{ $p['updated'] }}</td>
                            <td class="muted">{{ $p['by'] }}</td>
                            <td><x-admin.status-chip :status="$p['status']" /></td>
                            <td class="cell-actions">
                                <a href="#pages" class="fw-600 bnr-row-link">Edit</a>
                                <a href="{{ $store.$p['url'] }}" class="muted bnr-row-link" target="_blank" rel="noopener">View<span class="visually-hidden"> {{ $p['name'] }} on the store</span></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Menus --}}
    <section id="menus" class="card bnr-anchor" aria-labelledby="bnr-menus-title">
        <div class="card__head bnr-head">
            <div>
                <h2 id="bnr-menus-title" class="card__title">Menus</h2>
                <div class="card__subtitle">Drag items to reorder; indent to make a sub-menu</div>
            </div>
        </div>
        <div class="row-wrap" style="--gap: 16px">
            @foreach ($menus as $m)
                <div class="bnr-menu">
                    <div class="split split--baseline">
                        <h3 class="bnr-menu__name">{{ $m['name'] }}</h3>
                        <span class="fs-12 muted">{{ $m['where'] }}</span>
                    </div>
                    <ul class="bnr-menu__list" role="list">
                        @foreach ($m['items'] as $it)
                            <li @class(['bnr-menu__item', 'is-sub' => $it['level'] > 0, 'is-top' => $m['bold_top'] && $it['level'] === 0])>
                                <svg width="12" height="16" viewBox="0 0 16 20" fill="currentColor" aria-hidden="true" class="bnr-menu__grip"><circle cx="5" cy="4" r="1.6"/><circle cx="11" cy="4" r="1.6"/><circle cx="5" cy="10" r="1.6"/><circle cx="11" cy="10" r="1.6"/><circle cx="5" cy="16" r="1.6"/><circle cx="11" cy="16" r="1.6"/></svg>
                                <span class="bnr-menu__label">{{ $it['label'] }}</span>
                                <span class="bnr-menu__link">{{ $it['link'] }}</span>
                                <a href="#menus" class="bnr-menu__edit">Edit<span class="visually-hidden"> {{ $it['label'] }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn btn--sm bnr-menu__add"><x-admin.icon name="plus" :size="16" />Add menu item</button>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const SLIDES = @js($slides);
    const ANN = @js($ann);
    const TONES = @js(Status::toneMap());
    const COLOUR_KEYS = Object.keys(ANN.colours);

    Alpine.data('bannersPage', () => ({
        section: 'sliders',
        slides: SLIDES.map((s) => ({ ...s })),
        sel: SLIDES[0] ? SLIDES[0].id : null,
        draft: SLIDES[0] ? { ...SLIDES[0] } : null,
        dragId: null,
        overId: null,
        announce: '',
        nextId: SLIDES.length + 1,
        colours: ANN.colours,
        ann: { on: ANN.enabled, text: ANN.templates[0].text, tpl: 0, colour: ANN.colour },

        // ----- slides -----
        statusOf(s) { return s.enabled ? s.status : 'inactive'; },
        tone(st) { return TONES[st] || 'gray'; },
        label(st) { return st.charAt(0).toUpperCase() + st.slice(1); },
        position(id) { return this.slides.findIndex((s) => s.id === id) + 1; },
        pick(id) {
            this.sel = id;
            const s = this.slides.find((x) => x.id === id);
            this.draft = s ? { ...s } : null;
        },
        saveSlide() {
            const s = this.slides.find((x) => x.id === this.sel);
            if (s && this.draft) Object.assign(s, this.draft);
            this.announce = 'Slide saved.';
        },
        deleteSlide() {
            this.slides = this.slides.filter((x) => x.id !== this.sel);
            this.pick(this.slides[0] ? this.slides[0].id : null);
            this.announce = 'Slide deleted.';
        },
        addSlide() {
            if (this.slides.length >= 6) return;
            const id = 's' + this.nextId++;
            this.slides.push({ id, title: 'New slide', sub: '', btn: 'Shop now', link: '/', schedule: 'Not scheduled', start: '', end: '', status: 'draft', img: null, tone: '#E5E7EA', enabled: false });
            this.pick(id);
        },
        move(id, dir) {
            const i = this.slides.findIndex((s) => s.id === id);
            const j = i + dir;
            if (i < 0 || j < 0 || j >= this.slides.length) return;
            const list = this.slides.slice();
            [list[i], list[j]] = [list[j], list[i]];
            this.slides = list;
            this.announce = list[j].title + ' moved to position ' + (j + 1) + ' of ' + list.length + '.';
            this.$nextTick(() => {
                const handles = this.$root.querySelectorAll('.bnr-grip');
                if (handles[j]) handles[j].focus();
            });
        },
        dropOn(targetId) {
            const from = this.slides.findIndex((s) => s.id === this.dragId);
            const to = this.slides.findIndex((s) => s.id === targetId);
            if (from >= 0 && to >= 0 && from !== to) {
                const list = this.slides.slice();
                const [moved] = list.splice(from, 1);
                list.splice(to, 0, moved);
                this.slides = list;
                this.announce = moved.title + ' moved to position ' + (to + 1) + '.';
            }
            this.dragId = null;
            this.overId = null;
        },

        // ----- announcement bar -----
        templateIndex() { return ANN.templates.findIndex((t) => t.text === this.ann.text); },
        useTemplate(i) { this.ann.text = ANN.templates[i].text; this.ann.tpl = i; },
        cycleColour(dir) {
            const i = (COLOUR_KEYS.indexOf(this.ann.colour) + dir + COLOUR_KEYS.length) % COLOUR_KEYS.length;
            this.ann.colour = COLOUR_KEYS[i];
            this.$nextTick(() => { const el = this.$root.querySelector('.bnr-colour[aria-checked="true"]'); if (el) el.focus(); });
        },
    }));
});
</script>
@endpush
