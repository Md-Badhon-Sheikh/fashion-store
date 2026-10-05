{{--
    Products — Products.dc.html.
    Status tabs + filters work client-side on the rows of this page (Alpine `productsList`).
    Each product is its own <tbody> so its variant matrix (grouped by colour) can expand under it.
    Phones (< 640px): tabs become a pill row and the table becomes product cards.
    Data: $products (ProductData::listRows()), $meta, $tabs, $filters.
--}}
@extends('admin.layouts.app')

@section('title', 'Products')

@use('App\Support\DemoData')
@use('App\Support\Status')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/products.css') }}">
@endpush

@section('content')
<div class="page-stack" x-data="productsList">
    <x-admin.page-header title="Products" subtitle="412 products · 1,986 size/colour variants · each variant has its own SKU and barcode">
        <x-slot:actions>
            <button type="button" class="btn btn--outline">Import CSV</button>
            <button type="button" class="btn btn--outline">Export</button>
            <a class="btn btn--outline" href="{{ route('admin.barcodes') }}">Print barcode labels</a>
            <a class="btn btn--brand" href="{{ route('admin.products.create') }}">+ Add product</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tabs class="hide-sm" model="tab" label="Stock status" :tabs="$tabs" />
    <x-admin.tabs class="show-sm" variant="pill" model="tab" label="Stock status" :tabs="$tabs" />

    {{-- Filters --}}
    <form class="filter-grid" role="search" aria-label="Filter products" @submit.prevent>
        @foreach ($filters as $key => $filter)
            <label class="field">{{ $filter['label'] }}
                <select class="select select--sm" name="{{ $key }}" x-model="f.{{ $key }}">
                    @foreach ($filter['options'] as $value => $text)
                        <option value="{{ $value }}">{{ $text }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
    </form>

    {{-- Bulk actions (appear when rows are ticked) --}}
    <div class="bulk-bar" x-show="selected.length" x-cloak role="region" aria-label="Bulk actions">
        <span class="bulk-bar__count" x-text="selected.length + (selected.length === 1 ? ' product selected' : ' products selected')"></span>
        <a class="btn btn--sm btn--accent" href="{{ route('admin.barcodes') }}">Print barcode labels</a>
        <label class="visually-hidden" for="bulk-visibility">Change visibility of selected products</label>
        <select id="bulk-visibility" class="select">
            <option>Change visibility…</option>
            <option>Online + POS</option>
            <option>POS only</option>
            <option>Hidden</option>
        </select>
        <button type="button" class="bulk-bar__clear" @click="selected = []">Clear selection</button>
    </div>

    {{-- Desktop / tablet: table with expandable variant matrix --}}
    <div class="table-card hide-sm">
        <table class="table products-table" style="--table-min: 980px">
            <caption class="visually-hidden">Products with prices, stock and visibility. Expand the variants of a product to see each size and colour.</caption>
            <thead>
                <tr>
                    <th scope="col" class="cell-check">
                        <input type="checkbox" class="checkbox" aria-label="Select all products on this page"
                            :checked="allSelected" @change="toggleAll($event.target.checked)">
                    </th>
                    <th scope="col">Product</th>
                    <th scope="col">Category / brand</th>
                    <th scope="col">Variants</th>
                    <th scope="col" class="num">Purchase</th>
                    <th scope="col" class="num">Selling</th>
                    <th scope="col" class="num">Stock</th>
                    <th scope="col">Status</th>
                    <th scope="col">Shown on</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            @foreach ($products as $i => $p)
                @php($open = $i === 0)
                <tbody class="products-table__group"
                    x-data="{ open: {{ $open ? 'true' : 'false' }}, colour: @js($p['colour_groups'][0]['code']) }"
                    x-show="visible({{ $p['id'] }})">
                    <tr @class(['is-expanded' => $open]) :class="{ 'is-expanded': open }">
                        <td class="cell-check">
                            <input type="checkbox" class="checkbox" value="{{ $p['id'] }}" x-model.number="selected" aria-label="Select {{ $p['name'] }}">
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="thumb" style="--tone: {{ $p['tone'] }}">
                                    @if ($p['image_url'])<img src="{{ $p['image_url'] }}" alt="{{ $p['name'] }}" @if ($i > 2) loading="lazy" @endif>@endif
                                </span>
                                <span>
                                    <a class="product-cell__name products-table__name" href="{{ route('admin.products.edit', $p['id']) }}">{{ $p['name'] }}</a>
                                    <span class="product-cell__meta">SKU {{ $p['sku'] }}</span>
                                </span>
                            </div>
                        </td>
                        <td>{{ $p['category'] }}<span class="cell-sub">{{ $p['brand'] }}</span></td>
                        <td>
                            <button type="button" class="variants-toggle" aria-controls="variants-{{ $p['id'] }}"
                                aria-expanded="{{ $open ? 'true' : 'false' }}" :aria-expanded="open.toString()" @click="open = !open">
                                {{ $p['variants_label'] }}
                                <x-admin.icon name="chevron-down" :size="16" :stroke="2.2" class="variants-toggle__caret" />
                            </button>
                        </td>
                        <td class="num muted">{{ DemoData::money($p['purchase_price']) }}</td>
                        <td class="num cell-strong">{{ DemoData::money($p['selling_price']) }}</td>
                        <td class="num cell-strong">{{ $p['stock'] }}</td>
                        <td><x-admin.status-chip :status="$p['status']" /></td>
                        <td class="fs-13 text-2">{{ $p['visibility'] }}</td>
                        <td class="cell-actions">
                            <div class="row-actions">
                                <a class="fw-600" href="{{ route('admin.products.edit', $p['id']) }}" aria-label="Edit {{ $p['name'] }}">Edit</a>
                                <div class="row-menu" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
                                    <button type="button" class="row-menu__btn" :aria-expanded="menu.toString()" aria-expanded="false"
                                        aria-haspopup="true" aria-label="More actions for {{ $p['name'] }}" @click="menu = !menu">More</button>
                                    <div class="row-menu__list" x-show="menu" x-cloak x-transition.opacity>
                                        <a href="{{ DemoData::storeUrl() }}/product/{{ $p['slug'] }}" target="_blank" rel="noopener">View on store</a>
                                        <a href="{{ route('admin.barcodes', ['sku' => $p['variants'][0]['sku']]) }}">Print barcode labels</a>
                                        <a href="{{ route('admin.inventory') }}">Stock history</a>
                                        <button type="button" @click="menu = false">Duplicate</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Variant matrix for this product, one colour at a time --}}
                    <tr class="variants-row" id="variants-{{ $p['id'] }}" x-show="open" @unless ($open) x-cloak @endunless>
                        <td colspan="10" class="variants-row__cell">
                            @if (count($p['colour_groups']) > 1)
                                <div class="colour-switch" role="radiogroup" aria-label="Colour of {{ $p['name'] }} variants">
                                    @foreach ($p['colour_groups'] as $gi => $g)
                                        <button type="button" role="radio" class="colour-switch__btn"
                                            aria-checked="{{ $gi === 0 ? 'true' : 'false' }}"
                                            :aria-checked="(colour === @js($g['code'])).toString()"
                                            @click="colour = @js($g['code'])">
                                            <span class="swatch" style="--swatch: {{ $g['hex'] }}"></span>{{ $g['name'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @foreach ($p['colour_groups'] as $gi => $g)
                                <div x-show="colour === @js($g['code'])" @if ($gi > 0) x-cloak @endif>
                                    <div class="section-label variants-row__title">Variants · colour {{ $g['name'] }} ({{ count($g['variants']) }} of {{ $p['variant_count'] }})</div>
                                    <div class="table-wrap">
                                        <table class="table table--compact table--inner" style="--table-min: 760px">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Size</th>
                                                    <th scope="col">Colour</th>
                                                    <th scope="col">Variant SKU</th>
                                                    <th scope="col">Barcode</th>
                                                    <th scope="col" class="num">Selling</th>
                                                    <th scope="col" class="num">Stock</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col"><span class="visually-hidden">Label</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($g['variants'] as $v)
                                                    @php($vs = DemoData::stockStatus($v['stock']))
                                                    <tr x-data="{ stock: {{ $v['stock'] }} }">
                                                        <td class="fw-700">{{ $v['size'] }}</td>
                                                        <td>{{ $g['name'] }}</td>
                                                        <td class="mono nowrap">{{ $v['sku'] }}</td>
                                                        <td>
                                                            <div class="barcode-strip" aria-hidden="true"></div>
                                                            <div class="barcode-digits">{{ $v['barcode'] }}</div>
                                                        </td>
                                                        <td class="num">{{ DemoData::money($v['selling_price']) }}</td>
                                                        <td class="num">
                                                            <input type="text" inputmode="numeric" class="input-cell input-cell--w56 input-cell--strong"
                                                                value="{{ $v['stock'] }}" x-model.number="stock"
                                                                aria-label="Stock for size {{ $v['size'] }}, {{ $g['name'] }}">
                                                        </td>
                                                        <td>
                                                            <span class="chip chip--sm chip--{{ Status::tone($vs) }}" :class="stockTone(stock)" x-text="stockLabel(stock)">{{ Status::label($vs) }}</span>
                                                        </td>
                                                        <td>
                                                            <a class="btn btn--xs btn--outline-strong" href="{{ route('admin.barcodes', ['sku' => $v['sku']]) }}"
                                                                aria-label="Print label for {{ $v['sku'] }}">Print label</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            @endforeach
        </table>
    </div>

    {{-- Phones: product cards --}}
    <div class="product-cards show-sm">
        @foreach ($products as $p)
            <article class="list-card product-card" x-data="{ open: false }" x-show="visible({{ $p['id'] }})">
                <div class="product-card__top">
                    <span class="thumb" style="--tone: {{ $p['tone'] }}">
                        @if ($p['image_url'])<img src="{{ $p['image_url'] }}" alt="{{ $p['name'] }}" loading="lazy">@endif
                    </span>
                    <div class="product-card__text">
                        <a class="product-card__name" href="{{ route('admin.products.edit', $p['id']) }}">{{ $p['name'] }}</a>
                        <span class="list-card__meta">SKU {{ $p['sku'] }} · {{ $p['category'] }}</span>
                        <x-admin.status-chip :status="$p['status']" size="sm" />
                    </div>
                </div>
                <div class="list-card__foot">
                    <span class="fs-13 text-2">Stock <strong>{{ $p['stock'] }}</strong> · {{ $p['visibility'] }}</span>
                    <span class="list-card__amount">{{ DemoData::money($p['selling_price']) }}</span>
                </div>
                <div class="product-card__actions">
                    <button type="button" class="btn btn--sm btn--outline" :aria-expanded="open.toString()" aria-expanded="false"
                        aria-controls="variants-sm-{{ $p['id'] }}" @click="open = !open">Variants ({{ $p['variant_count'] }})</button>
                    <a class="btn btn--sm btn--outline" href="{{ route('admin.products.edit', $p['id']) }}">Edit</a>
                </div>
                <ul class="variant-list" id="variants-sm-{{ $p['id'] }}" x-show="open" x-cloak>
                    @foreach ($p['variants'] as $v)
                        <li class="variant-list__row">
                            <span><strong>{{ $v['size'] }}</strong> · {{ $v['colour'] }}<span class="variant-list__sku mono">{{ $v['sku'] }}</span></span>
                            <x-admin.status-chip :status="$v['status']" :label="$v['stock'].' pcs'" size="sm" />
                        </li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </div>

    {{-- No match --}}
    <div class="card" x-show="visibleCount === 0" x-cloak>
        <x-admin.empty-state icon="products" title="No products match" message="Try another tab or clear the filters.">
            <button type="button" class="btn btn--outline btn--sm" @click="reset()">Clear filters</button>
        </x-admin.empty-state>
    </div>

    <div class="table-footer">
        <span x-text="filtered ? 'Showing ' + visibleCount + ' matching products on this page' : 'Showing 1–6 of 412'">Showing 1–6 of 412</span>
        <nav class="pagination" aria-label="Products pages">
            <button type="button" class="pagination__btn" aria-label="Previous page" :disabled="page === 1" disabled @click="page = Math.max(1, page - 1)">‹</button>
            @foreach ([1, 2, 3] as $n)
                <button type="button" class="pagination__btn" aria-label="Page {{ $n }}"
                    @if ($n === 1) aria-current="page" @endif :aria-current="page === {{ $n }} ? 'page' : null" @click="page = {{ $n }}">{{ $n }}</button>
            @endforeach
            <button type="button" class="pagination__btn" aria-label="Next page" @click="page = Math.min(3, page + 1)">›</button>
        </nav>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productsList', () => ({
            tab: 'all',
            page: 1,
            selected: [],
            f: { category: '', brand: '', size: '', colour: '', status: '', fabric: '', added: 'd90' },
            meta: @js($meta),
            ids: @js(array_column($products, 'id')),

            visible(id) {
                const m = this.meta[id];
                if (!m) return false;
                if (this.tab === 'hidden' && !m.hidden) return false;
                if (!['all', 'hidden'].includes(this.tab) && m.status !== this.tab) return false;
                const f = this.f;
                return (!f.category || m.main === f.category)
                    && (!f.brand || m.brand === f.brand)
                    && (!f.size || m.sizes.includes(f.size))
                    && (!f.colour || m.colours.includes(f.colour))
                    && (!f.status || m.status === f.status)
                    && (!f.fabric || m.fabric === f.fabric);
            },
            get visibleCount() { return this.ids.filter((id) => this.visible(id)).length; },
            get filtered() {
                return this.tab !== 'all' || ['category', 'brand', 'size', 'colour', 'status', 'fabric'].some((k) => this.f[k] !== '');
            },
            get allSelected() { return this.ids.length > 0 && this.selected.length === this.ids.length; },
            toggleAll(on) { this.selected = on ? [...this.ids] : []; },
            reset() {
                this.tab = 'all';
                Object.assign(this.f, { category: '', brand: '', size: '', colour: '', status: '', fabric: '' });
            },
            // Variant stock status (low-stock threshold 5), live while editing the stock cell.
            stockTone(n) {
                n = Number(n) || 0;
                return { 'chip--red': n <= 0, 'chip--amber': n > 0 && n <= 5, 'chip--green': n > 5 };
            },
            stockLabel(n) {
                n = Number(n) || 0;
                return n <= 0 ? 'Out of stock' : (n <= 5 ? 'Low stock' : 'Available');
            },
        }));
    });
</script>
@endpush
