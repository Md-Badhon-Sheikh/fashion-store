{{--
    Categories & brands — Categories.dc.html.
    Category tree (filter, collapse, show-on-menu switches, status) + edit form bound to the
    selected row (Alpine `categoriesPage`). Other tabs: brands, attributes, size charts.
    Data: $tabs, $rows (CategoryData::rows(), keyed by id), $parents, $attrs, $brands, $sizeCharts, $selected.
--}}
@extends('admin.layouts.app')

@section('title', 'Categories & brands')

@php
    $cur = $rows[$selected];
    $flags = fn (string $key) => array_map(fn ($r) => $r[$key], $rows);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/categories.css') }}">
@endpush

@section('content')
<div class="page-stack" x-data="categoriesPage">
    <x-admin.page-header title="Categories & brands" subtitle="4 main categories · 16 sub-categories · 6 brands · drag rows to change menu order">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">Export</button>
            <button type="button" class="btn btn--brand" @click="addNew()">+ Add category</button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tabs class="hide-sm" model="tab" label="Catalog setup" :tabs="$tabs" />
    <x-admin.tabs class="show-sm" variant="pill" model="tab" label="Catalog setup" :tabs="$tabs" />

    {{-- ===== Categories ===== --}}
    <div class="cat-cols" x-show="tab === 'categories'">
        <section class="card card--flush cat-tree" aria-labelledby="h-tree">
            <div class="panel-head">
                <h2 id="h-tree" class="card__title">Category tree</h2>
                <div class="tools">
                    <div class="input-group">
                        <x-admin.icon name="search" :size="16" :stroke="2" />
                        <label for="catq" class="visually-hidden">Filter categories</label>
                        <input id="catq" type="search" placeholder="Filter categories" x-model="q">
                    </div>
                    <button type="button" class="btn btn--sm btn--outline-strong" style="height: 40px" @click="toggleAll()"
                        x-text="anyCollapsed ? 'Expand all' : 'Collapse all'">Expand all</button>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table cat-table" style="--table-min: 720px">
                    <caption class="visually-hidden">Category tree. Main categories with their sub-categories, product counts, menu visibility and status.</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="cat-table__grip"><span class="visually-hidden">Reorder</span></th>
                            <th scope="col">Category</th>
                            <th scope="col" class="num">Products</th>
                            <th scope="col">Show on menu</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $id => $r)
                            @php($isParent = $r['level'] === 0)
                            <tr @class(['cat-row', 'cat-row--parent' => $isParent, 'is-selected' => $id === $selected])
                                :class="{ 'is-selected': sel === '{{ $id }}' }" x-show="showRow('{{ $id }}')">
                                <td class="cat-table__grip">
                                    <button type="button" class="grip-btn" aria-label="Drag to reorder {{ $r['name'] }}">
                                        <x-admin.icon name="grip" :size="16" :stroke="3" />
                                    </button>
                                </td>
                                <td @class(['cat-cell', 'cat-cell--child' => ! $isParent])>
                                    <div class="media-row">
                                        @if ($isParent)
                                            @if ($r['children'])
                                                <button type="button" class="tree-caret" aria-label="Show or hide sub-categories of {{ $r['name'] }}"
                                                    aria-expanded="true" :aria-expanded="(!collapsed['{{ $id }}']).toString()" @click="collapsed['{{ $id }}'] = !collapsed['{{ $id }}']">
                                                    <x-admin.icon name="chevron-down" :size="14" :stroke="2.4" />
                                                </button>
                                            @else
                                                <span class="tree-caret tree-caret--empty" aria-hidden="true"></span>
                                            @endif
                                        @else
                                            <span class="tree-elbow" aria-hidden="true"></span>
                                        @endif
                                        <span class="photo photo--34" style="--tone: {{ $r['tone'] }}">
                                            @if ($r['image_url'])<img src="{{ $r['image_url'] }}" alt="" loading="lazy">@endif
                                        </span>
                                        <span class="media-row__text">
                                            <span @class(['media-row__name', 'fw-500' => ! $isParent])>{{ $r['name'] }}</span>
                                            <span class="media-row__meta">/{{ $r['slug'] }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="num cell-strong">{{ $r['product_count'] }}</td>
                                <td>
                                    <button type="button" role="switch" class="tswitch" aria-label="Show {{ $r['name'] }} on menu"
                                        aria-checked="{{ $r['menu'] ? 'true' : 'false' }}" :aria-checked="menu['{{ $id }}'].toString()"
                                        @click="menu['{{ $id }}'] = !menu['{{ $id }}']">
                                        <span class="tswitch__track"></span><span x-text="menu['{{ $id }}'] ? 'Shown' : 'Hidden'">{{ $r['menu'] ? 'Shown' : 'Hidden' }}</span>
                                    </button>
                                </td>
                                <td>
                                    <span @class(['chip', 'chip--green' => $r['active'], 'chip--gray' => ! $r['active']])
                                        :class="{ 'chip--green': active['{{ $id }}'], 'chip--gray': !active['{{ $id }}'] }"
                                        x-text="active['{{ $id }}'] ? 'Active' : 'Inactive'">{{ $r['active'] ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="cell-actions text-right">
                                    <button type="button" class="btn btn--xs btn--outline-strong text-brand" aria-label="Edit {{ $r['name'] }}" @click="edit('{{ $id }}')">Edit</button>
                                    @if ($isParent)
                                        <button type="button" class="btn btn--xs btn--outline-strong" aria-label="Add sub-category to {{ $r['name'] }}" @click="addNew('{{ $id }}')">+ Sub</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr x-show="noMatch" x-cloak>
                            <td colspan="6" class="text-center muted">No categories match “<span x-text="q"></span>”.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="panel-foot fs-13">Categories with products cannot be deleted. Move products first or set the category to Inactive.</div>
        </section>

        <div class="cat-side">
            {{-- Edit / add form --}}
            <section class="card card--stack form-lg" aria-labelledby="h-form" id="category-form">
                <div class="card-title-row">
                    <h2 id="h-form" class="card__title" x-text="sel === 'new' ? 'Add category' : 'Edit category'">Edit category</h2>
                    <span class="fs-12 muted" x-text="sel === 'new' ? 'New' : form.count + ' products · ID ' + form.id.toUpperCase()">{{ $cur['product_count'] }} products · ID {{ strtoupper($cur['id']) }}</span>
                </div>
                <div class="alert alert--success" role="status" x-show="flash" x-cloak x-text="flash"></div>
                <label class="field">Name
                    <input class="input" type="text" name="name" x-model="form.name" x-ref="name" value="{{ $cur['name'] }}">
                </label>
                <label class="field">Parent category
                    <select class="select" name="parent" x-model="form.parent">
                        <option>None (main category)</option>
                        <optgroup label="Move to">
                            @foreach ($parents as $p)
                                <option @selected($p === $cur['parent_name'])>{{ $p }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </label>
                <div class="field">
                    <span>Image <span class="field__hint">· used on home page category tiles</span></span>
                    <div class="media-row" style="gap: 12px">
                        <span class="photo photo--72 cat-photo" :style="'--tone: ' + form.tone" style="--tone: {{ $cur['tone'] }}">
                            <span class="cat-photo__ph">Category photo</span>
                            <img src="{{ $cur['image_url'] }}" :src="form.img" x-show="form.img" :alt="form.name" alt="{{ $cur['name'] }}">
                        </span>
                        <span class="stack stack--sm">
                            <label class="file-btn">Replace image
                                <input type="file" accept="image/*" @change="pickImage($event)">
                            </label>
                            <span class="fs-12 muted fw-500">600 × 800 px, JPG or WebP</span>
                        </span>
                    </div>
                </div>
                <label class="field">Slug
                    <span class="input-group"><span class="input-group__prefix">/category/</span>
                        <input type="text" name="slug" x-model="form.slug" value="{{ $cur['slug'] }}" style="font-size: 13px">
                    </span>
                </label>
                <div class="cat-form-row">
                    <label class="field">Sort order
                        <input class="input" type="number" min="1" name="sort" x-model="form.sort" value="{{ $cur['sort'] }}">
                    </label>
                    <div class="field">
                        <span id="lbl-active">Active</span>
                        <button type="button" role="switch" class="tswitch tswitch--lg" aria-labelledby="lbl-active"
                            aria-checked="{{ $cur['active'] ? 'true' : 'false' }}" :aria-checked="form.active.toString()" @click="toggleActive()">
                            <span class="tswitch__track"></span><span x-text="form.active ? 'Active' : 'Inactive'">{{ $cur['active'] ? 'Active' : 'Inactive' }}</span>
                        </button>
                    </div>
                </div>
                <label class="field">
                    <span>Meta description <span class="field__hint">Optional, for search engines</span></span>
                    <textarea class="textarea" name="meta_description" rows="2" x-model="form.meta">{{ $cur['meta'] }}</textarea>
                </label>
                <div class="alert alert--warn" role="alert" x-show="deleteBlocked" x-cloak>
                    <span x-text="form.name"></span> has <strong x-text="form.count"></strong> products. Move them to another category first, or set it to Inactive.
                </div>
                <div class="cat-form-actions">
                    <button type="button" class="btn btn--delete" @click="remove()" x-show="sel !== 'new'">Delete</button>
                    <div class="cluster ml-auto">
                        <button type="button" class="btn btn--outline-strong" @click="cancel()">Cancel</button>
                        <button type="button" class="btn btn--brand" @click="save()">Save category</button>
                    </div>
                </div>
            </section>

            @include('admin.categories.partials.attributes', ['headingId' => 'h-attr', 'showManage' => true])
        </div>
    </div>

    {{-- ===== Brands ===== --}}
    <section class="card card--flush" aria-labelledby="h-brands" x-show="tab === 'brands'" x-cloak>
        <div class="panel-head">
            <h2 id="h-brands" class="card__title">Brands</h2>
            <button type="button" class="btn btn--sm btn--brand">+ Add brand</button>
        </div>
        <div class="table-wrap">
            <table class="table" style="--table-min: 600px">
                <thead>
                    <tr><th scope="col">Brand</th><th scope="col" class="num">Products</th><th scope="col">Brand filter on website</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($brands as $b)
                        <tr>
                            <td>
                                <span class="media-row"><span class="avatar avatar--sm">{{ $b['initials'] }}</span>
                                    <span><span class="media-row__name">{{ $b['name'] }}</span><span class="media-row__meta">{{ $b['note'] }}</span></span>
                                </span>
                            </td>
                            <td class="num cell-strong">{{ $b['products'] }}</td>
                            <td>
                                <button type="button" role="switch" class="tswitch" aria-label="Show {{ $b['name'] }} in the brand filter"
                                    x-data="{ on: {{ $b['menu'] ? 'true' : 'false' }} }" aria-checked="{{ $b['menu'] ? 'true' : 'false' }}"
                                    :aria-checked="on.toString()" @click="on = !on">
                                    <span class="tswitch__track"></span><span x-text="on ? 'Shown' : 'Hidden'">{{ $b['menu'] ? 'Shown' : 'Hidden' }}</span>
                                </button>
                            </td>
                            <td><x-admin.status-chip :status="$b['status']" /></td>
                            <td class="text-right"><button type="button" class="btn btn--xs btn--outline-strong text-brand" aria-label="Edit {{ $b['name'] }}">Edit</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ===== Attributes ===== --}}
    <div x-show="tab === 'attributes'" x-cloak>
        @include('admin.categories.partials.attributes', ['headingId' => 'h-attr-full', 'showManage' => false])
    </div>

    {{-- ===== Size charts ===== --}}
    <section class="card card--flush" aria-labelledby="h-charts" x-show="tab === 'charts'" x-cloak>
        <div class="panel-head">
            <h2 id="h-charts" class="card__title">Size charts</h2>
            <button type="button" class="btn btn--sm btn--brand">+ New size chart</button>
        </div>
        <div class="table-wrap">
            <table class="table" style="--table-min: 640px">
                <thead>
                    <tr><th scope="col">Chart</th><th scope="col">Sizes</th><th scope="col">Measurements</th><th scope="col" class="num">Used by</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($sizeCharts as $c)
                        <tr>
                            <td class="cell-strong">{{ $c['name'] }}</td>
                            <td>{{ $c['sizes'] }}</td>
                            <td class="text-2">{{ $c['measures'] }}</td>
                            <td class="num">{{ $c['products'] }} products</td>
                            <td class="text-right"><button type="button" class="btn btn--xs btn--outline-strong text-brand" aria-label="Edit {{ $c['name'] }}">Edit</button></td>
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
        const slugify = (s) => s.toLowerCase().replace(/&/g, ' ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

        Alpine.data('categoriesPage', () => ({
            tab: 'categories',
            rows: @js($rows),
            sel: @js($selected),
            q: '',
            collapsed: {},
            menu: @js($flags('menu')),
            active: @js($flags('active')),
            form: {},
            flash: '',
            deleteBlocked: false,

            init() {
                this.load(this.sel);
                this.$watch('form.name', (v) => {
                    if (this.sel === 'new') this.form.slug = this.slugFor(v, this.form.parent);
                });
            },
            slugFor(name, parent) {
                const p = Object.values(this.rows).find((r) => r.level === 0 && r.name === parent);
                return (p ? p.slug + '/' : '') + slugify(name || '');
            },
            load(id) {
                const r = this.rows[id];
                this.form = { id: r.id, name: r.name, parent: r.parent_name, slug: r.slug, sort: r.sort, active: this.active[id], meta: r.meta, img: r.image_url || '', tone: r.tone, count: r.product_count };
                this.flash = '';
                this.deleteBlocked = false;
            },
            edit(id) {
                this.sel = id;
                this.load(id);
                this.focusForm();
            },
            addNew(parentId = 'men') {
                const parent = this.rows[parentId];
                this.sel = 'new';
                this.form = { id: 'new', name: '', parent: parent.name, slug: '', sort: parent.children + 1, active: true, meta: '', img: '', tone: '#EEF0F2', count: 0 };
                this.flash = '';
                this.deleteBlocked = false;
                this.tab = 'categories';
                this.focusForm();
            },
            focusForm() {
                this.$nextTick(() => {
                    const el = document.getElementById('category-form');
                    if (el && window.innerWidth < 1200) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    this.$refs.name && this.$refs.name.focus({ preventScroll: true });
                });
            },
            toggleActive() {
                this.form.active = !this.form.active;
                if (this.sel !== 'new') this.active[this.sel] = this.form.active;
            },
            pickImage(e) {
                const f = e.target.files && e.target.files[0];
                if (f) this.form.img = URL.createObjectURL(f);
            },
            cancel() { this.sel === 'new' ? this.edit('pnj') : this.load(this.sel); },
            save() {
                this.flash = (this.form.name || 'Category') + ' saved. This demo does not store changes yet.';
                this.deleteBlocked = false;
            },
            remove() {
                this.flash = '';
                if (this.form.count > 0) { this.deleteBlocked = true; return; }
                this.flash = this.form.name + ' would be deleted. This demo does not store changes yet.';
            },

            /* Tree filtering & collapsing */
            matches(id) {
                const r = this.rows[id];
                const q = this.q.trim().toLowerCase();
                return !q || r.name.toLowerCase().includes(q) || r.slug.toLowerCase().includes(q);
            },
            showRow(id) {
                const r = this.rows[id];
                if (this.q.trim()) {
                    // A parent stays visible when one of its subs matches.
                    return this.matches(id) || (r.level === 0 && Object.values(this.rows).some((c) => c.parent_id === id && this.matches(c.id)));
                }
                return r.level === 0 || !this.collapsed[r.parent_id];
            },
            get noMatch() { return this.q.trim() !== '' && !Object.keys(this.rows).some((id) => this.showRow(id)); },
            get anyCollapsed() { return Object.values(this.collapsed).some(Boolean); },
            toggleAll() {
                const collapse = !this.anyCollapsed;
                Object.values(this.rows).filter((r) => r.level === 0 && r.children).forEach((r) => { this.collapsed[r.id] = collapse; });
            },
        }));
    });
</script>
@endpush
