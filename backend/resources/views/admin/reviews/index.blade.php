{{--
    Reviews moderation — Reviews.dc.html.
    Alpine component `reviewsPage` (pushed script below): status tabs (Pending / Published / Hidden),
    filters (text, rating, product, sort, verified only, with photos), publish / hide / reply / delete
    (with undo), photo preview. Tabs become a pill row on phones.
    Data: ReviewController (App\Support\Demo\ReviewData).
--}}
@extends('admin.layouts.app')

@section('title', 'Reviews')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/reviews.css') }}">
@endpush

@php
    $tabs = ['pending' => 'Pending', 'published' => 'Published', 'hidden' => 'Hidden'];
    $serverCounts = [];
    foreach ($tabs as $key => $label) {
        $serverCounts[$key] = \App\Support\DemoData::number(collect($reviews)->where('status', $key)->count() + ($archived[$key] ?? 0));
    }
    $starPath = 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z';
@endphp

@section('content')
<div class="rv" x-data="reviewsPage">

    <x-admin.page-header title="Reviews" subtitle="New reviews wait here until a staff member publishes them on the product page">
        <x-slot:actions>
            <label class="rv-auto">
                <input type="checkbox" class="checkbox" x-model="autoPublish">
                Auto-publish 4–5 star verified reviews
            </label>
            <button type="button" class="btn btn--outline">
                <x-admin.icon name="download" :size="18" />Export
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Summary tiles --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi__label">Average rating</div>
            <div class="rv-avg">
                <span class="kpi__value">{{ $summary['average'] }}</span>
                <span class="rv-stars" role="img" aria-label="{{ $summary['average'] }} out of 5 stars">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $starPath }}" @class(['rv-star', 'is-on' => $i <= floor($summary['average']), 'is-part' => $i > floor($summary['average'])])></path></svg>
                    @endfor
                </span>
            </div>
            <div class="kpi__sub">From {{ \App\Support\DemoData::number($summary['total']) }} reviews</div>
        </div>
        <div class="kpi kpi--pending">
            <div class="kpi__label">Waiting for moderation</div>
            <div class="kpi__value" x-text="counts.pending">{{ collect($reviews)->where('status', 'pending')->count() }}</div>
            <div class="kpi__sub">Oldest from {{ $summary['oldest_pending'] }}</div>
        </div>
        <x-admin.kpi label="This month" :value="(string) $summary['this_month']" :sub="$summary['verified_share'].' from verified purchases'" />
        <x-admin.kpi label="Low ratings (1–2 star)" :value="$summary['low_share']" sub="Reply within 24 h to each one" />
    </div>

    {{-- Status tabs: underlined on desktop, pill row on phones --}}
    <div role="tablist" aria-label="Review status" class="tabs hide-sm">
        @foreach ($tabs as $key => $label)
            <button type="button" role="tab" class="tab" aria-controls="rv-list" aria-selected="{{ $key === 'pending' ? 'true' : 'false' }}"
                :aria-selected="(tab === '{{ $key }}').toString()" @click="setTab('{{ $key }}')">
                {{ $label }}
                <span @class(['tab__count', 'rv-count--pending' => $key === 'pending']) x-text="fmt(counts.{{ $key }})">{{ $serverCounts[$key] }}</span>
            </button>
        @endforeach
    </div>
    <div role="tablist" aria-label="Review status" class="pill-tabs show-sm">
        @foreach ($tabs as $key => $label)
            <button type="button" role="tab" class="pill-tab" aria-controls="rv-list" aria-selected="{{ $key === 'pending' ? 'true' : 'false' }}"
                :aria-selected="(tab === '{{ $key }}').toString()" @click="setTab('{{ $key }}')">
                {{ $label }}<span class="pill-tab__count" x-text="fmt(counts.{{ $key }})">{{ $serverCounts[$key] }}</span>
            </button>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="filter-bar rv-filters" role="search" aria-label="Filter reviews">
        <label class="field field--wide">Search review text or phone
            <span class="input-group">
                <x-admin.icon name="search" :size="16" :stroke="2" />
                <input type="search" x-model.debounce.200ms="q" placeholder="e.g. fabric, size, colour" autocomplete="off">
            </span>
        </label>
        <label class="field">Rating
            <select class="select" x-model="rating">
                <option value="">All ratings</option>
                @for ($s = 5; $s >= 1; $s--)
                    <option value="{{ $s }}">{{ $s }} {{ $s === 1 ? 'star' : 'stars' }}</option>
                @endfor
            </select>
        </label>
        <label class="field field--wide">Product
            <select class="select" x-model="product">
                <option value="">All products</option>
                @foreach ($productOptions as $name)
                    <option>{{ $name }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">Sort
            <select class="select" x-model="sort">
                <option value="newest">Newest first</option>
                <option value="lowest">Lowest rating first</option>
                <option value="highest">Highest rating first</option>
            </select>
        </label>
        <label class="check rv-filters__check"><input type="checkbox" x-model="verifiedOnly">Verified only</label>
        <label class="check rv-filters__check"><input type="checkbox" x-model="withPhotos">With photos</label>
    </div>

    <p class="rv-flash" role="status" aria-live="polite" x-show="flash" x-cloak>
        <span x-text="flash"></span>
        <button type="button" class="link-btn" x-show="undoable" @click="undo()">Undo</button>
    </p>

    {{-- Review cards --}}
    <div id="rv-list" class="rv-grid" role="tabpanel" :aria-label="tabLabel + ' reviews'">
        <template x-for="r in visible" :key="r.id">
            <article class="rv-card" :class="{ 'is-replying': replyOpen === r.id }" :aria-label="'Review of ' + r.product.name">
                <div class="rv-card__head">
                    <span class="thumb rv-card__thumb" :style="'--tone:' + r.product.tone">
                        <img :src="r.product.image_url" :alt="r.product.name" loading="lazy">
                    </span>
                    <div class="rv-card__product">
                        <a class="rv-card__name" :href="r.product.edit_url" x-text="r.product.name"></a>
                        <div class="rv-card__meta" x-text="r.variant + ' · Order ' + r.order"></div>
                    </div>
                    <span class="chip" :class="'chip--' + tone(r.status)" x-text="label(r.status)"></span>
                </div>

                <div class="rv-card__info">
                    <span class="rv-stars" role="img" :aria-label="r.rating + ' out of 5 stars'">
                        <template x-for="i in 5" :key="i">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $starPath }}" class="rv-star" :class="{ 'is-on': i <= r.rating }"></path></svg>
                        </template>
                    </span>
                    <span class="chip chip--green chip--sm rv-verified" x-show="r.verified"><x-admin.icon name="check" :size="12" :stroke="3" />Verified purchase</span>
                    <span class="chip chip--gray chip--sm" x-show="!r.verified">Not verified</span>
                    <span class="fs-12 muted" x-text="r.date + ' · ' + r.phone"></span>
                </div>

                <p class="rv-card__text" x-text="r.text"></p>

                <div class="rv-photos" x-show="r.photos > 0">
                    <template x-for="n in r.photos" :key="n">
                        <button type="button" class="rv-photo" :style="'--tone:' + photoTone(n)" :aria-label="'Open photo ' + n + ' of review by ' + r.phone"
                            @click="openPhoto(r, n)" x-text="'Photo ' + n"></button>
                    </template>
                </div>

                <div class="rv-card__hidden" x-show="r.status === 'hidden'" x-text="'Hidden: ' + (r.hidden_reason || 'Hidden by staff')"></div>

                <div class="rv-reply" x-show="r.reply">
                    <div class="fw-700">Reply from YOUR BRAND</div>
                    <div class="text-2" x-text="r.reply"></div>
                </div>

                <div class="rv-card__actions">
                    <button type="button" class="btn btn--brand btn--sm" x-show="r.status !== 'published'" @click="setStatus(r, 'published')">Publish</button>
                    <button type="button" class="btn btn--outline-strong btn--sm" x-show="r.status !== 'hidden'" @click="setStatus(r, 'hidden')">Hide</button>
                    <button type="button" class="btn btn--outline-strong btn--sm rv-reply-btn" x-show="r.status !== 'hidden'"
                        :aria-expanded="(replyOpen === r.id).toString()" :aria-controls="'rp-panel-' + r.id" @click="toggleReply(r)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14L4 9l5-5M4 9h10a6 6 0 0 1 6 6v5"></path></svg>
                        <span x-text="r.reply ? 'Edit reply' : 'Reply'"></span>
                    </button>
                    <button type="button" class="rv-delete" @click="remove(r)" :aria-label="'Delete review of ' + r.product.name">
                        <x-admin.icon name="trash" :size="14" :stroke="2" />Delete
                    </button>
                </div>

                <div class="rv-reply-form" :id="'rp-panel-' + r.id" x-show="replyOpen === r.id">
                    <label :for="'rp-' + r.id" class="fs-13 fw-700">Public reply (shown under the review)</label>
                    <textarea :id="'rp-' + r.id" class="textarea" rows="4" x-model="r.draft"></textarea>
                    <div class="rv-reply-form__foot">
                        <label class="rv-reply-form__sms"><input type="checkbox" class="checkbox" x-model="replySms">Also send as SMS to customer</label>
                        <div class="cluster">
                            <button type="button" class="btn btn--outline-strong btn--sm" @click="replyOpen = null">Cancel</button>
                            <button type="button" class="btn btn--primary btn--sm" @click="postReply(r)" :disabled="!(r.draft || '').trim()">Post reply &amp; publish</button>
                        </div>
                    </div>
                </div>
            </article>
        </template>
    </div>

    <div class="rv-empty" x-show="!visible.length" x-cloak>
        <template x-if="filtersActive">
            <span>No reviews in this tab match the filters. <button type="button" class="link-btn" @click="clearFilters()">Clear filters</button></span>
        </template>
        <template x-if="!filtersActive">
            <span>Nothing here. All reviews in this tab have been handled.</span>
        </template>
    </div>

    <p class="rv-showing">
        <span x-text="showing"></span>
        <template x-if="tab !== 'pending'">
            <span> · <button type="button" class="link-btn" @click="loadMore()">Load more</button></span>
        </template>
    </p>

    {{-- Photo preview --}}
    <div class="rv-lightbox" x-show="photo" x-cloak @keydown.escape.window="photo = null">
        <div class="rv-lightbox__backdrop" @click="photo = null"></div>
        <div class="rv-lightbox__box" role="dialog" aria-modal="true" aria-label="Customer photo">
            <div class="split">
                <div class="fw-700" x-text="photo ? photo.label + ' · ' + photo.product : ''"></div>
                <button type="button" class="icon-btn icon-btn--ghost" x-ref="photoClose" @click="photo = null" aria-label="Close photo"><x-admin.icon name="close" :size="20" /></button>
            </div>
            <div class="rv-lightbox__img" :style="photo ? '--tone:' + photo.tone : ''">Customer photo</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const REVIEWS = @js($reviews);
    const ARCHIVED = @js($archived);
    const TONES = { pending: 'amber', published: 'green', hidden: 'gray' };
    const LABELS = { pending: 'Pending', published: 'Published', hidden: 'Hidden' };

    window.Alpine.data('reviewsPage', () => ({
        reviews: REVIEWS.map((r) => ({ ...r, draft: r.draft || r.reply || '' })),
        tab: 'pending',
        replyOpen: 'rv3',
        replySms: true,
        autoPublish: false,
        q: '',
        rating: '',
        product: '',
        sort: 'newest',
        verifiedOnly: false,
        withPhotos: false,
        photo: null,
        flash: '',
        undoable: null,
        flashTimer: null,

        fmt: (n) => Number(n).toLocaleString('en-IN'),
        tone: (s) => TONES[s] || 'gray',
        label: (s) => LABELS[s] || s,
        photoTone: (n) => ['#E2DED6', '#D9DEE2'][n - 1] || '#E2DED6',

        get counts() {
            const n = (s) => this.reviews.filter((r) => r.status === s).length;
            return { pending: n('pending') + ARCHIVED.pending, published: n('published') + ARCHIVED.published, hidden: n('hidden') + ARCHIVED.hidden };
        },
        get tabLabel() { return LABELS[this.tab]; },
        get filtersActive() { return !!(this.q.trim() || this.rating || this.product || this.verifiedOnly || this.withPhotos); },
        get visible() {
            const t = this.q.trim().toLowerCase();
            const digits = t.replace(/\D/g, '');
            const list = this.reviews.filter((r) => r.status === this.tab
                && (!t || r.text.toLowerCase().includes(t) || r.product.name.toLowerCase().includes(t) || (digits && r.phone.replace(/\D/g, '').includes(digits)))
                && (!this.rating || r.rating === Number(this.rating))
                && (!this.product || r.product.name === this.product)
                && (!this.verifiedOnly || r.verified)
                && (!this.withPhotos || r.photos > 0));
            const by = {
                newest: (a, b) => b.sort_date.localeCompare(a.sort_date),
                lowest: (a, b) => a.rating - b.rating || b.sort_date.localeCompare(a.sort_date),
                highest: (a, b) => b.rating - a.rating || b.sort_date.localeCompare(a.sort_date),
            }[this.sort];
            return list.slice().sort(by);
        },
        get showing() {
            const n = this.visible.length;
            if (this.tab === 'pending') return 'Showing all ' + n + ' pending ' + (n === 1 ? 'review' : 'reviews');
            return 'Showing ' + n + ' of ' + this.fmt(this.counts[this.tab]) + ' ' + this.tab + ' reviews';
        },

        setTab(t) { this.tab = t; this.replyOpen = null; },
        clearFilters() { this.q = ''; this.rating = ''; this.product = ''; this.verifiedOnly = false; this.withPhotos = false; },
        notify(text, undoable = null) {
            this.flash = text;
            this.undoable = undoable;
            clearTimeout(this.flashTimer);
            this.flashTimer = setTimeout(() => { this.flash = ''; this.undoable = null; }, 6000);
        },

        setStatus(r, status) {
            const before = { id: r.id, status: r.status, reply: r.reply };
            r.status = status;
            if (this.replyOpen === r.id) this.replyOpen = null;
            this.notify('Review of ' + r.product.name + ' ' + (status === 'published' ? 'published on the product page.' : 'hidden from the product page.'), before);
        },
        toggleReply(r) {
            this.replyOpen = this.replyOpen === r.id ? null : r.id;
            if (this.replyOpen) this.$nextTick(() => { const el = document.getElementById('rp-' + r.id); el && el.focus(); });
        },
        postReply(r) {
            const text = (r.draft || '').trim();
            if (!text) return;
            const before = { id: r.id, status: r.status, reply: r.reply };
            r.reply = text;
            r.status = 'published';
            this.replyOpen = null;
            this.notify('Reply posted and review published' + (this.replySms ? ' · SMS sent to ' + r.phone : '') + '.', before);
        },
        remove(r) {
            const index = this.reviews.indexOf(r);
            this.reviews.splice(index, 1);
            if (this.replyOpen === r.id) this.replyOpen = null;
            this.notify('Review of ' + r.product.name + ' deleted.', { deleted: r, index });
        },
        undo() {
            const u = this.undoable;
            if (!u) return;
            if (u.deleted) {
                this.reviews.splice(u.index, 0, u.deleted);
            } else {
                const r = this.reviews.find((x) => x.id === u.id);
                if (r) { r.status = u.status; r.reply = u.reply; }
            }
            this.flash = '';
            this.undoable = null;
        },
        openPhoto(r, n) {
            this.photo = { label: 'Photo ' + n, tone: this.photoTone(n), product: r.product.name };
            this.$nextTick(() => this.$refs.photoClose.focus());
        },
        loadMore() { this.notify('Older reviews load from the database once it is connected (sample data in this export).'); },
    }));
});
</script>
@endpush
