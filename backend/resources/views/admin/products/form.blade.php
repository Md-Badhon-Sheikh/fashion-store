{{--
    Add / edit product — ProductForm.dc.html.
    One view for create ($product === null) and edit ($product from DemoData::findProduct()).
    All interactive state lives in the Alpine component `productForm`, seeded from $state
    (ProductData::formState()): pricing with live margin, photos (add / reorder / remove),
    size × colour variant generator that (re)builds the variant table (existing stock and
    barcodes are kept), status, visibility, labels, related products and SEO counters.
    The form does not submit anywhere yet (static design export).
--}}
@extends('admin.layouts.app')

@section('title', $product ? 'Edit product' : 'Add product')

@use('App\Support\DemoData')

@php
    $num = fn ($v) => (int) preg_replace('/\D/', '', (string) $v);
    $cost = $num($state['purchase']);
    $sell = $num($state['selling']);
    $sale = $num($state['sale']);
    $pct = fn ($a, $b) => $b > 0 ? number_format($a / $b * 100, 1).'% margin' : '';
    $isEdit = $product !== null;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/catalog-kit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/products.css') }}">
@endpush

@section('content')
<form class="page-stack page-stack--lg form-lg" x-data="productForm" @submit.prevent="save('publish')" novalidate>
    <x-admin.page-header class="pf-head" :title="$isEdit ? 'Edit product' : 'Add product'"
        :subtitle="$isEdit
            ? $product['name'].' · SKU '.$product['sku'].'. Change details, prices or variants; each variant keeps its own SKU and barcode.'
            : 'Fill basic info, add photos, set prices, then generate size × colour variants. Each variant gets its own SKU and barcode.'">
        <x-slot:breadcrumb>
            <a href="{{ route('admin.products.index') }}">Products</a><span aria-hidden="true">›</span><span aria-current="page">{{ $isEdit ? $product['name'] : 'Add product' }}</span>
        </x-slot:breadcrumb>
        <x-slot:actions>
            <a class="btn btn--cancel" href="{{ route('admin.products.index') }}">Cancel</a>
            <button type="button" class="btn btn--outline-strong" @click="save('draft')">Save draft</button>
            <button type="submit" class="btn btn--brand">{{ $isEdit ? 'Save changes' : 'Publish' }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="alert alert--success flash" role="status" x-show="flash" x-cloak>
        <x-admin.icon name="check" :size="18" /><span x-text="flash"></span>
    </div>

    <div class="pf-cols">
        <div class="pf-main">

            {{-- Basic info --}}
            <section class="card card--stack" aria-labelledby="h-basic" style="gap: 14px">
                <h2 id="h-basic" class="card__title">Basic info</h2>
                <label class="field">
                    <span>Product name <span class="field__hint">Shown on website, POS and receipts</span></span>
                    <input class="input pf-name" type="text" name="name" x-model="name" value="{{ $state['name'] }}" placeholder="e.g. Embroidered Cotton Panjabi" required>
                </label>

                <div class="pf-row">
                    <label class="field">Category
                        <select class="select" name="category" x-model="main" @change="onMainChange()">
                            @foreach ($tree as $m)
                                <option value="{{ $m['id'] }}" @selected($m['id'] === $state['main'])>{{ $m['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">Sub-category
                        <select class="select" name="sub_category" x-model="sub" @change="onSubChange()">
                            <template x-for="s in subs" :key="s.slug">
                                <option :value="s.slug" x-text="s.name" :selected="s.slug === sub"></option>
                            </template>
                        </select>
                    </label>
                    <label class="field">Brand
                        <select class="select" name="brand" x-model="brand">
                            @foreach (['YOUR BRAND', 'Partner brand'] as $b)
                                <option @selected($b === $state['brand'])>{{ $b }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="pf-row" style="--basis: 220px">
                    <div class="field">
                        <label for="pf-sku">Product code / SKU <span class="field__hint">Auto-generated from sub-category, editable</span></label>
                        <span class="cluster" style="--gap: 6px; flex-wrap: nowrap">
                            <input id="pf-sku" class="input input--mono input--soft" type="text" name="sku" x-model="sku" value="{{ $state['sku'] }}">
                            <button type="button" class="btn btn--outline-strong btn--sm" style="height: 44px" @click="regenerateSku()">Regenerate</button>
                        </span>
                    </div>
                    <label class="field">
                        <span>Fabric / material <span class="field__hint">Used for filters on the website</span></span>
                        <select class="select" name="fabric" x-model="fabric">
                            @foreach ($state['fabrics'] as $fab)
                                <option @selected($fab === $state['fabric'])>{{ $fab }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="field">
                    <span>Short description <span class="field__hint">1–2 lines under the product name</span></span>
                    <input class="input" type="text" name="short_description" x-model="shortDesc" value="{{ $state['shortDesc'] }}" placeholder="One or two lines about fabric, fit and occasion">
                </label>

                <div class="field">
                    <label for="pf-longdesc">Full description</label>
                    <div class="editor">
                        <div class="editor__toolbar" role="toolbar" aria-label="Text formatting" x-data="{ b: false, i: false, u: false }">
                            <button type="button" class="editor__btn editor__btn--wide" aria-label="Paragraph style">Paragraph ▾</button>
                            <button type="button" class="editor__btn" aria-label="Bold" :aria-pressed="b.toString()" aria-pressed="false" @click="b = !b"><span class="fw-800">B</span></button>
                            <button type="button" class="editor__btn" aria-label="Italic" :aria-pressed="i.toString()" aria-pressed="false" @click="i = !i"><em>I</em></button>
                            <button type="button" class="editor__btn" aria-label="Underline" :aria-pressed="u.toString()" aria-pressed="false" @click="u = !u"><span style="text-decoration: underline">U</span></button>
                            <span class="editor__sep" aria-hidden="true"></span>
                            <button type="button" class="editor__btn" aria-label="Bulleted list">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11"></path><circle cx="4.5" cy="6" r="1"></circle><circle cx="4.5" cy="12" r="1"></circle><circle cx="4.5" cy="18" r="1"></circle></svg>
                            </button>
                            <button type="button" class="editor__btn" aria-label="Numbered list">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M10 6h10M10 12h10M10 18h10M4 5l1.5-1V9M3.5 13.5a1.5 1.5 0 1 1 2.5 1L3.5 17H6.5"></path></svg>
                            </button>
                            <button type="button" class="editor__btn" aria-label="Insert link">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"></path><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"></path></svg>
                            </button>
                            <button type="button" class="editor__btn" aria-label="Insert table">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1.5"></rect><path d="M3 10h18M3 14.5h18M9 5v14M15 5v14"></path></svg>
                            </button>
                            <button type="button" class="editor__btn" aria-label="Insert image">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="10" r="1.8"></circle><path d="M3 17l5-4 4 3 3-2 6 4"></path></svg>
                            </button>
                        </div>
                        <textarea id="pf-longdesc" class="editor__area" name="description" rows="6" x-model="longDesc" placeholder="Fit, length, details, what the model wears…">{{ $state['longDesc'] }}</textarea>
                    </div>
                </div>

                <label class="field">Care instructions
                    <textarea class="textarea" name="care" rows="2" x-model="care">{{ $state['care'] }}</textarea>
                </label>
            </section>

            {{-- Media --}}
            <section class="card card--stack" aria-labelledby="h-media">
                <div class="card-title-row">
                    <h2 id="h-media" class="card__title">Media</h2>
                    <span class="card-meta">First image is the cover. Drag to reorder. JPG/PNG/WebP, min 1000 × 1333 px.</span>
                </div>
                <div class="dropzone" :class="{ 'is-over': over }"
                    @dragover.prevent="over = true" @dragleave="over = false" @drop.prevent="over = false; addFiles($event.dataTransfer.files)">
                    <x-admin.icon name="upload" :size="32" :stroke="1.7" />
                    <div class="dropzone__title">Drag &amp; drop photos here</div>
                    <div class="dropzone__or">or</div>
                    <label class="file-btn">Browse files
                        <input type="file" multiple accept="image/*" @change="addFiles($event.target.files); $event.target.value = ''">
                    </label>
                    <div class="dropzone__hint">Up to 8 photos, max 4 MB each. Tag a photo with a colour to switch it when that colour is picked.</div>
                </div>
                <div class="photo-grid" x-show="photos.length" @if (! $state['photos']) x-cloak @endif>
                    <template x-for="(p, i) in photos" :key="p.src + '-' + i">
                        <div class="photo-tile" draggable="true" :class="{ 'is-dragging': dragFrom === i }"
                            @dragstart="dragFrom = i" @dragend="dragFrom = null" @dragover.prevent @drop.prevent.stop="dropOn(i)">
                            <div class="photo-tile__img" :style="'--tone: ' + (p.tone || '#E5E7EA')">
                                Product photo
                                <img :src="p.src" :alt="p.alt">
                                <span class="photo-tile__cover" x-show="i === 0">Cover</span>
                                <button type="button" class="photo-tile__btn photo-tile__btn--move" :aria-label="'Move ' + photoLabel(p, i) + ' earlier (or drag to reorder)'"
                                    :disabled="i === 0" @click="movePhoto(i)">
                                    <x-admin.icon name="grip" :size="16" :stroke="3" />
                                </button>
                                <button type="button" class="photo-tile__btn photo-tile__btn--remove" :aria-label="'Remove ' + photoLabel(p, i)" @click="photos.splice(i, 1)">
                                    <x-admin.icon name="close" :size="16" :stroke="2" />
                                </button>
                            </div>
                            <div class="photo-tile__meta">
                                <span class="fw-600" x-text="photoLabel(p, i)"></span>
                                <span class="swatch-label" x-show="p.colour">
                                    <span class="swatch" :style="'--s: 10px; --swatch: ' + p.hex"></span><span x-text="p.colour"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
                <label class="field">
                    <span>Product video URL <span class="field__hint">YouTube or Facebook link, shown after the photos</span></span>
                    <input class="input" type="url" name="video_url" x-model="video" value="{{ $state['video'] }}" placeholder="https://youtube.com/watch?v=...">
                </label>
            </section>

            {{-- Pricing --}}
            <section class="card card--stack" aria-labelledby="h-price">
                <div class="card-title-row">
                    <h2 id="h-price" class="card__title">Pricing</h2>
                    <span class="card-meta">Default for all variants, override per variant below</span>
                </div>
                <div class="pf-row" style="--basis: 160px">
                    <label class="field">Purchase price (cost)
                        <span class="input-group input-group--44"><span class="input-group__prefix" aria-hidden="true">৳</span>
                            <input class="input-group__num" type="text" inputmode="numeric" name="purchase_price" x-model="purchase" @blur="tidy('purchase')" value="{{ $state['purchase'] }}" placeholder="0">
                        </span>
                    </label>
                    <label class="field">Selling price (MRP)
                        <span class="input-group input-group--44"><span class="input-group__prefix" aria-hidden="true">৳</span>
                            <input class="input-group__num fw-700" type="text" inputmode="numeric" name="selling_price" x-model="selling" @blur="tidy('selling')" value="{{ $state['selling'] }}" placeholder="0">
                        </span>
                    </label>
                    <label class="field">
                        <span>Sale price <span class="field__hint">Optional</span></span>
                        <span class="input-group input-group--44"><span class="input-group__prefix" aria-hidden="true">৳</span>
                            <input class="input-group__num input-group__num--sale" type="text" inputmode="numeric" name="sale_price" x-model="sale" @blur="tidy('sale')" value="{{ $state['sale'] }}" placeholder="—">
                        </span>
                    </label>
                </div>
                <div class="price-tiles" aria-live="polite">
                    <div class="price-tile price-tile--profit">
                        <div class="price-tile__label">Profit at selling price</div>
                        <div class="price-tile__value"><span x-text="profitText(n(selling))">{{ $sell && $cost ? DemoData::money($sell - $cost) : '—' }}</span>
                            <span class="price-tile__extra" x-text="marginText(n(selling))">{{ $sell && $cost ? '· '.$pct($sell - $cost, $sell) : '' }}</span></div>
                    </div>
                    <div class="price-tile price-tile--sale">
                        <div class="price-tile__label">Profit at sale price</div>
                        <div class="price-tile__value"><span x-text="profitText(n(sale))">{{ $sale && $cost ? DemoData::money($sale - $cost) : '—' }}</span>
                            <span class="price-tile__extra" x-text="marginText(n(sale))">{{ $sale && $cost ? '· '.$pct($sale - $cost, $sale) : '' }}</span></div>
                    </div>
                    <div class="price-tile price-tile--plain">
                        <div class="price-tile__label">Discount shown to customer</div>
                        <div class="price-tile__value"><span x-text="discountText()">{{ $sale && $sell > $sale ? round(($sell - $sale) / $sell * 100).'% off' : 'No discount' }}</span>
                            <span class="price-tile__extra" x-text="saveText()">{{ $sale && $sell > $sale ? '· save '.DemoData::money($sell - $sale) : '' }}</span></div>
                    </div>
                </div>
            </section>

            {{-- Variants --}}
            <section class="card card--stack" aria-labelledby="h-var" style="gap: 16px">
                <div class="card-title-row">
                    <h2 id="h-var" class="card__title">Variants</h2>
                    <span class="card-meta">Pick sizes and colours, then generate one row per combination</span>
                </div>

                <div class="gen-group">
                    <div id="lbl-sizes" class="gen-label">Sizes <span>· <span x-text="sizes.length">{{ count($state['sizes']) }}</span> selected</span></div>
                    <div class="chip-picks" role="group" aria-labelledby="lbl-sizes">
                        <template x-for="s in sizeOptions" :key="s">
                            <button type="button" class="pick" :aria-pressed="sizes.includes(s).toString()" @click="toggle(sizes, s)">
                                <x-admin.icon name="check" :size="14" :stroke="3" /><span x-text="s"></span>
                            </button>
                        </template>
                        <button type="button" class="pick pick--add" x-show="!addingSize" @click="addingSize = true; $nextTick(() => $refs.newSize.focus())">+ Size</button>
                        <span class="pick-add" x-show="addingSize" x-cloak>
                            <label class="visually-hidden" for="pf-new-size">New size</label>
                            <input id="pf-new-size" class="input" type="text" x-ref="newSize" x-model="newSize" placeholder="e.g. 3XL"
                                @keydown.enter.prevent="addSize()" @keydown.escape.prevent="addingSize = false">
                            <button type="button" class="btn btn--sm btn--primary" @click="addSize()">Add</button>
                        </span>
                    </div>
                </div>

                <div class="gen-group">
                    <div id="lbl-colours" class="gen-label">Colours <span>· <span x-text="colours.length">{{ count($state['colours']) }}</span> selected</span></div>
                    <div class="chip-picks" role="group" aria-labelledby="lbl-colours">
                        <template x-for="c in colourOptions" :key="c.code">
                            <button type="button" class="pick pick--colour" :aria-pressed="colours.includes(c.code).toString()" @click="toggle(colours, c.code)">
                                <span class="swatch" :style="'--s: 26px; --swatch: ' + c.hex"></span>
                                <span x-text="c.name"></span>
                                <span class="pick__state" x-text="colours.includes(c.code) ? 'Selected' : 'Not used'"></span>
                            </button>
                        </template>
                        <button type="button" class="pick pick--add" x-show="!addingColour" @click="addingColour = true; $nextTick(() => $refs.newColour.focus())">+ Add colour</button>
                        <span class="pick-add" x-show="addingColour" x-cloak>
                            <label class="visually-hidden" for="pf-new-colour">New colour name</label>
                            <input id="pf-new-colour" class="input" type="text" x-ref="newColour" x-model="newColour" placeholder="e.g. Black"
                                @keydown.enter.prevent="addColour()" @keydown.escape.prevent="addingColour = false">
                            <button type="button" class="btn btn--sm btn--primary" @click="addColour()">Add</button>
                        </span>
                    </div>
                </div>

                <div class="gen-bar">
                    <div class="gen-bar__text">
                        <strong x-text="sizes.length + ' sizes × ' + colours.length + ' colours = ' + (sizes.length * colours.length) + ' variants.'"></strong>
                        SKU and EAN-13 barcode are created for each. Existing stock is kept when you re-generate.
                        <span class="gen-bar__pending" x-show="pending" x-cloak role="status">Selection changed. Generate to update the table below.</span>
                    </div>
                    <button type="button" class="btn btn--primary" @click="generate()"
                        x-text="sizes.length * colours.length === 1 ? 'Generate 1 variant' : 'Generate ' + (sizes.length * colours.length) + ' variants'">Generate variants</button>
                </div>

                <div class="apply-row">
                    <span class="apply-row__label">Apply to all rows:</span>
                    <button type="button" class="btn btn--sm btn--outline-strong" @click="applyAll('purchase')" title="Copy the default purchase price to every row">Purchase price</button>
                    <button type="button" class="btn btn--sm btn--outline-strong" @click="applyAll('selling')" title="Copy the default selling price to every row">Selling price</button>
                    <button type="button" class="btn btn--sm btn--outline-strong" @click="applyAll('stock')" title="Copy the first row's opening stock to every row">Opening stock</button>
                    <button type="button" class="btn btn--sm btn--outline-strong" @click="applyAll('alert')" title="Copy the first row's alert level to every row">Low-stock alert</button>
                    <span class="apply-row__count" x-text="enabledCount + ' of ' + rows.length + ' enabled'"></span>
                </div>

                <div class="variant-table-wrap" x-show="rows.length" @if (! $state['colours']) x-cloak @endif>
                    <table class="table variant-table" style="--table-min: 1040px">
                        <caption class="visually-hidden">Generated variants with SKU, barcode, prices, opening stock and alert level</caption>
                        <thead>
                            <tr>
                                <th scope="col">Size</th>
                                <th scope="col">Colour</th>
                                <th scope="col">Variant SKU</th>
                                <th scope="col">Barcode (auto)</th>
                                <th scope="col" class="num">Purchase ৳</th>
                                <th scope="col" class="num">Selling ৳</th>
                                <th scope="col" class="num">Opening stock</th>
                                <th scope="col" class="num">Alert at</th>
                                <th scope="col">Enabled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="r in rows" :key="r.key">
                                <tr :class="{ 'is-off': !vals[r.key].on }">
                                    <td class="fw-700 fs-14" x-text="r.size"></td>
                                    <td><span class="swatch-label"><span class="swatch" :style="'--swatch: ' + r.hex"></span><span x-text="r.colour"></span></span></td>
                                    <td class="mono nowrap" x-text="r.sku"></td>
                                    <td><div class="barcode-strip" aria-hidden="true"></div><div class="barcode-digits" x-text="r.barcode"></div></td>
                                    <td class="num"><input class="input-cell input-cell--w72" type="text" inputmode="numeric" x-model="vals[r.key].purchase" :disabled="!vals[r.key].on" :aria-label="'Purchase price for ' + r.label"></td>
                                    <td class="num"><input class="input-cell input-cell--w72 input-cell--strong" type="text" inputmode="numeric" x-model="vals[r.key].selling" :disabled="!vals[r.key].on" :aria-label="'Selling price for ' + r.label"></td>
                                    <td class="num"><input class="input-cell input-cell--w60 input-cell--strong" type="text" inputmode="numeric" x-model="vals[r.key].stock" :disabled="!vals[r.key].on" :aria-label="'Opening stock for ' + r.label"></td>
                                    <td class="num"><input class="input-cell input-cell--w50" type="text" inputmode="numeric" x-model="vals[r.key].alert" :disabled="!vals[r.key].on" :aria-label="'Low-stock alert level for ' + r.label"></td>
                                    <td>
                                        <button type="button" role="switch" class="tswitch" :aria-checked="vals[r.key].on.toString()" :aria-label="'Enable ' + r.label"
                                            @click="vals[r.key].on = !vals[r.key].on">
                                            <span class="tswitch__track"></span><span x-text="vals[r.key].on ? 'On' : 'Off'"></span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="variant-empty" x-show="!rows.length" @if ($state['colours']) x-cloak @endif>Select at least one size and one colour to generate variants.</div>
            </section>
        </div>

        <div class="pf-side">
            {{-- Status --}}
            <section class="card card--stack" aria-labelledby="h-status" style="gap: 10px">
                <h2 id="h-status" class="card__title">Status</h2>
                <div class="seg" role="radiogroup" aria-labelledby="h-status">
                    @foreach (['Published', 'Draft'] as $s)
                        <button type="button" role="radio" class="seg__btn" aria-checked="{{ $state['status'] === $s ? 'true' : 'false' }}"
                            :aria-checked="(status === '{{ $s }}').toString()" @click="status = '{{ $s }}'">{{ $s }}</button>
                    @endforeach
                </div>
                <p class="pf-status-note" x-text="status === 'Published' ? 'Live on the website and POS as soon as you save.' : 'Saved but not visible to customers or POS staff.'">Live on the website and POS as soon as you save.</p>
            </section>

            {{-- Visibility --}}
            <fieldset class="card card--stack" style="gap: 8px">
                <legend class="visually-hidden">Visibility</legend>
                <h2 class="card__title" aria-hidden="true" style="margin-bottom: 4px">Visibility</h2>
                @foreach ([['Online + POS', 'Sold on website and in the shop'], ['POS only', 'Hidden from website, sellable in shop'], ['Hidden', 'Not sellable anywhere, stock kept']] as [$v, $note])
                    <label class="radio-card vis-option">
                        <input type="radio" class="radio" name="visibility" value="{{ $v }}" x-model="visibility" @checked($state['visibility'] === $v)>
                        <span><span class="vis-option__title">{{ $v }}</span><span class="vis-option__note">{{ $note }}</span></span>
                    </label>
                @endforeach
            </fieldset>

            {{-- Labels --}}
            <section class="card" aria-labelledby="h-labels">
                <h2 id="h-labels" class="card__title" style="margin-bottom: 6px">Labels</h2>
                @foreach ([['featured', 'Featured', 'Shown in the home page featured row'], ['newArrival', 'New arrival', 'Badge on card for 30 days'], ['best', 'Best seller', 'Badge on card and in Best sellers list']] as [$key, $name, $note])
                    <div class="label-row">
                        <span><span class="label-row__name">{{ $name }}</span><span class="label-row__note">{{ $note }}</span></span>
                        <button type="button" role="switch" class="tswitch tswitch--lg" aria-label="{{ $name }}"
                            aria-checked="{{ $state['labels'][$key] ? 'true' : 'false' }}" :aria-checked="labels.{{ $key }}.toString()"
                            @click="labels.{{ $key }} = !labels.{{ $key }}">
                            <span class="tswitch__track"></span><span class="fs-12" x-text="labels.{{ $key }} ? 'On' : 'Off'">{{ $state['labels'][$key] ? 'On' : 'Off' }}</span>
                        </button>
                    </div>
                @endforeach
            </section>

            {{-- Size chart --}}
            <section class="card card--stack" aria-labelledby="h-chart" style="gap: 8px">
                <h2 id="h-chart" class="card__title">Size chart</h2>
                <label class="field">Chart shown on product page
                    <select class="select" name="size_chart" x-model="sizeChart">
                        @foreach ($sizeCharts as $chart)
                            <option @selected($chart === $state['sizeChart'])>{{ $chart }}</option>
                        @endforeach
                    </select>
                </label>
                <a class="fs-13 fw-600" href="{{ route('admin.categories') }}">Manage size charts →</a>
            </section>

            {{-- Related products --}}
            <section class="card card--stack" aria-labelledby="h-rel" style="gap: 10px">
                <h2 id="h-rel" class="card__title">Related products</h2>
                <div class="suggest" @click.outside="relatedQuery = ''">
                    <div class="input-group input-group--44">
                        <x-admin.icon name="search" :size="16" :stroke="2" />
                        <label for="pf-relq" class="visually-hidden">Search products to relate</label>
                        <input id="pf-relq" type="search" placeholder="Search by name or SKU" x-model="relatedQuery" @keydown.escape="relatedQuery = ''" autocomplete="off">
                    </div>
                    <ul class="suggest__list" x-show="relatedQuery.trim().length > 0" x-cloak>
                        <template x-for="r in relatedResults" :key="r.sku">
                            <li><button type="button" class="suggest__item" @click="addRelated(r)">
                                <span class="photo photo--34" :style="'--tone: ' + r.tone"><img :src="r.img" alt=""></span>
                                <span><span class="fw-600" x-text="r.name"></span><span class="related-row__meta" style="display: block" x-text="r.sku + ' · ' + r.price"></span></span>
                            </button></li>
                        </template>
                        <li class="suggest__empty" x-show="!relatedResults.length">No products found.</li>
                    </ul>
                </div>
                <template x-for="(r, i) in related" :key="r.sku">
                    <div class="related-row">
                        <span class="photo" :style="'--tone: ' + r.tone"><img :src="r.img" :alt="r.name"></span>
                        <div class="related-row__text">
                            <div class="related-row__name" x-text="r.name"></div>
                            <div class="related-row__meta" x-text="r.sku + ' · ' + r.price"></div>
                        </div>
                        <button type="button" class="icon-btn icon-btn--sm" :aria-label="'Remove ' + r.name" @click="related.splice(i, 1)">
                            <x-admin.icon name="close" :size="14" :stroke="2" />
                        </button>
                    </div>
                </template>
                <p class="fs-12 muted">Shown as "Complete the look" on the product page. Leave empty to auto-pick from the same sub-category.</p>
            </section>

            {{-- SEO --}}
            <section class="card card--stack" aria-labelledby="h-seo">
                <h2 id="h-seo" class="card__title">SEO</h2>
                <label class="field">URL slug
                    <span class="input-group input-group--44"><span class="input-group__prefix">/product/</span>
                        <input type="text" name="slug" x-model="slug" @input="slugTouched = true" value="{{ $state['slug'] }}" style="font-size: 13px">
                    </span>
                </label>
                <label class="field">
                    <span class="field__row">Meta title <span class="counter" :class="{ 'is-over': metaTitle.length > 60 }" x-text="metaTitle.length + ' / 60'">{{ mb_strlen($state['metaTitle']) }} / 60</span></span>
                    <input class="input" type="text" name="meta_title" x-model="metaTitle" @input="metaTitleTouched = true" value="{{ $state['metaTitle'] }}">
                </label>
                <label class="field">
                    <span class="field__row">Meta description <span class="counter" :class="{ 'is-over': metaDesc.length > 160 }" x-text="metaDesc.length + ' / 160'">{{ mb_strlen($state['metaDesc']) }} / 160</span></span>
                    <textarea class="textarea" name="meta_description" rows="4" x-model="metaDesc">{{ $state['metaDesc'] }}</textarea>
                </label>
                <div class="serp" aria-label="Search preview">
                    <div class="serp__label">Search preview</div>
                    <div class="serp__url" x-text="'[YOUR-DOMAIN] › product › ' + (slug || 'product-url')">[YOUR-DOMAIN] › product › {{ $state['slug'] }}</div>
                    <div class="serp__title" x-text="metaTitle || name || 'Product title'">{{ $state['metaTitle'] }}</div>
                    <div class="serp__desc" x-text="metaDesc || shortDesc">{{ $state['metaDesc'] }}</div>
                </div>
            </section>
        </div>
    </div>

    <div class="pf-footer">
        <a class="btn btn--cancel" href="{{ route('admin.products.index') }}">Cancel</a>
        <button type="button" class="btn btn--outline-strong" @click="save('draft')">Save draft</button>
        <button type="submit" class="btn btn--brand">{{ $isEdit ? 'Save changes' : 'Publish product' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const group = (n) => Number(n).toLocaleString('en-IN');
        const money = (n) => (n < 0 ? '−' : '') + '৳' + group(Math.abs(n));
        const slugify = (s) => s.toLowerCase().normalize('NFKD').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

        Alpine.data('productForm', () => ({
            ...@js($state),
            tree: @js($tree),
            skuPrefixes: @js($skuPrefixes),
            nextSku: @js($nextSku),
            relatedPool: @js($relatedPool),

            flash: '',
            over: false,
            dragFrom: null,
            addingSize: false,
            newSize: '',
            addingColour: false,
            newColour: '',
            relatedQuery: '',
            generated: { sizes: [], colours: [] },
            vals: {},

            init() {
                this.generate();
                this.$watch('name', (v) => {
                    if (!this.slugTouched) this.slug = slugify(v);
                    if (!this.metaTitleTouched) this.metaTitle = v ? v + ' | YOUR BRAND' : '';
                });
            },

            /* Numbers */
            n(v) { return parseInt(String(v ?? '').replace(/[^\d]/g, ''), 10) || 0; },
            tidy(key) { this[key] = this[key] === '' ? '' : group(this.n(this[key])); },
            profitText(price) { const c = this.n(this.purchase); return price && c ? money(price - c) : '—'; },
            marginText(price) { const c = this.n(this.purchase); return price && c ? '· ' + ((price - c) / price * 100).toFixed(1) + '% margin' : ''; },
            discountText() {
                const s = this.n(this.selling), sale = this.n(this.sale);
                return sale && s > sale ? Math.round((s - sale) / s * 100) + '% off' : 'No discount';
            },
            saveText() {
                const s = this.n(this.selling), sale = this.n(this.sale);
                return sale && s > sale ? '· save ' + money(s - sale) : '';
            },

            /* Category & SKU */
            get subs() { return (this.tree.find((m) => m.id === this.main) || { subs: [] }).subs; },
            onMainChange() { this.sub = this.subs.length ? this.subs[0].slug : ''; this.onSubChange(); },
            onSubChange() { if (this.mode === 'create') this.regenerateSku(); },
            regenerateSku() {
                const prefix = this.skuPrefixes[this.sub] || 'PRD';
                const next = this.nextSku[prefix] || 1001;
                this.nextSku[prefix] = next + 1;
                this.sku = prefix + '-' + next;
            },

            /* Media */
            photoLabel(p, i) { return (i + 1) + ' · ' + p.title; },
            movePhoto(i) { if (i > 0) this.photos.splice(i - 1, 0, this.photos.splice(i, 1)[0]); },
            dropOn(i) {
                if (this.dragFrom === null || this.dragFrom === i) return;
                const moved = this.photos.splice(this.dragFrom, 1)[0];
                this.photos.splice(i, 0, moved);
                this.dragFrom = null;
            },
            addFiles(files) {
                Array.from(files || []).filter((f) => f.type.startsWith('image/')).forEach((f) => {
                    if (this.photos.length >= 8) return;
                    this.photos.push({ title: f.name.replace(/\.[^.]+$/, '').slice(0, 18), colour: '', hex: '', alt: this.name || 'Product photo', src: URL.createObjectURL(f), tone: '#E5E7EA' });
                });
            },

            /* Variant generator */
            toggle(list, v) { const i = list.indexOf(v); i === -1 ? list.push(v) : list.splice(i, 1); },
            addSize() {
                const s = this.newSize.trim().toUpperCase();
                if (s && !this.sizeOptions.includes(s)) this.sizeOptions.push(s);
                if (s && !this.sizes.includes(s)) this.sizes.push(s);
                this.newSize = ''; this.addingSize = false;
            },
            addColour() {
                const name = this.newColour.trim();
                if (name && !this.colourOptions.some((c) => c.name.toLowerCase() === name.toLowerCase())) {
                    let code = name.replace(/[^A-Za-z]/g, '').slice(0, 2).toUpperCase() || 'CL';
                    while (this.colourOptions.some((c) => c.code === code)) code = code.charAt(0) + String.fromCharCode(65 + Math.floor(Math.random() * 26));
                    this.colourOptions.push({ code, name, hex: '#D5D9DE' });
                    this.colours.push(code);
                }
                this.newColour = ''; this.addingColour = false;
            },
            ordered(list, options) { return options.filter((o) => list.includes(o)); },
            get pending() {
                const a = this.ordered(this.sizes, this.sizeOptions).join() + '|' + this.ordered(this.colours, this.colourOptions.map((c) => c.code)).join();
                const b = this.generated.sizes.join() + '|' + this.generated.colours.join();
                return a !== b;
            },
            generate() {
                const sizes = this.ordered(this.sizes, this.sizeOptions);
                const colours = this.ordered(this.colours, this.colourOptions.map((c) => c.code));
                colours.forEach((code) => sizes.forEach((size) => {
                    const key = code + '-' + size;
                    if (this.vals[key]) return; // keep what was typed / existing stock
                    const ex = this.existing[key];
                    this.vals[key] = {
                        purchase: this.purchase,
                        selling: ex ? group(ex.selling) : this.selling,
                        stock: ex ? ex.stock : 0,
                        alert: 5,
                        on: true,
                    };
                }));
                this.generated = { sizes, colours };
            },
            get rows() {
                const out = [];
                this.generated.colours.forEach((code) => {
                    const c = this.colourOptions.find((o) => o.code === code);
                    const ci = this.colourOptions.indexOf(c);
                    this.generated.sizes.forEach((size) => {
                        const key = code + '-' + size;
                        if (!this.vals[key]) return;
                        const si = this.sizeOptions.indexOf(size);
                        const ex = this.existing[key];
                        out.push({
                            key, size, colour: c.name, hex: c.hex, label: size + ' ' + c.name,
                            sku: (this.sku || 'SKU') + '-' + code + '-' + size,
                            barcode: ex ? ex.barcode : '89012' + String(this.productId).padStart(3, '0') + String(8000 + ci * 50 + si).padStart(4, '0') + '1',
                        });
                    });
                });
                return out;
            },
            get enabledCount() { return this.rows.filter((r) => this.vals[r.key].on).length; },
            applyAll(field) {
                const rows = this.rows;
                if (!rows.length) return;
                const value = field === 'purchase' ? this.purchase
                    : field === 'selling' ? this.selling
                    : this.vals[rows[0].key][field];
                rows.forEach((r) => { this.vals[r.key][field] = value; });
            },

            /* Related products */
            get relatedResults() {
                const q = this.relatedQuery.trim().toLowerCase();
                if (!q) return [];
                return this.relatedPool.filter((r) => r.sku !== this.sku && !this.related.some((x) => x.sku === r.sku)
                    && (r.name.toLowerCase().includes(q) || r.sku.toLowerCase().includes(q))).slice(0, 6);
            },
            addRelated(r) { this.related.push(r); this.relatedQuery = ''; },

            /* Save (demo) */
            save(kind) {
                if (kind === 'draft') this.status = 'Draft';
                this.flash = (kind === 'draft' ? 'Draft saved.' : (this.mode === 'edit' ? 'Product updated.' : 'Product published.'))
                    + ' This demo does not store changes yet.';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        }));
    });
</script>
@endpush
