/**
 * Shop filter logic: URL <-> filter state, filtering, sorting and facets.
 * All filter state lives in the query string so listings are shareable and
 * survive reloads:
 *
 *   ?q=        search term            ?filter=new | flash-sale
 *   ?sub=      sub-category slug      ?size=M,L   ?colour=Navy,Olive
 *   ?fabric=   fabric names           ?brand=your-brand,signature
 *   ?min= ?max= price (BDT)           ?stock=1    in stock only
 *   ?sort=     see data/shop.js       ?page=      1-based page
 *
 * When the API is wired in, send the same params to GET /api/products and
 * use the response's facets instead of buildFacets().
 */

import { getProductsByCategory, isOnSale, searchProducts } from '../../data/products.js';
import { DEFAULT_SORT, brands, getBrand, sizeOrder, sortOptions } from '../../data/shop.js';
import { formatBDT } from '../../utils/format.js';

const LIST_KEYS = { sizes: 'size', colours: 'colour', fabrics: 'fabric', brands: 'brand' };

/* ------------------------------------------------------------ URL state */

export function readFilters(params) {
  const list = (key) =>
    (params.get(key) || '')
      .split(',')
      .map((v) => v.trim())
      .filter(Boolean);
  const num = (key) => {
    const raw = params.get(key);
    if (raw === null || raw === '') return null;
    const n = Number(raw);
    return Number.isFinite(n) && n >= 0 ? Math.round(n) : null;
  };
  const sort = params.get('sort');
  return {
    q: params.get('q') || '',
    filter: params.get('filter') || '',
    sub: params.get('sub') || '',
    sizes: list(LIST_KEYS.sizes),
    colours: list(LIST_KEYS.colours),
    fabrics: list(LIST_KEYS.fabrics),
    brands: list(LIST_KEYS.brands),
    min: num('min'),
    max: num('max'),
    inStock: params.get('stock') === '1',
    sort: sortOptions.some((o) => o.value === sort) ? sort : DEFAULT_SORT,
    page: Math.max(1, parseInt(params.get('page'), 10) || 1),
  };
}

export function writeFilters(f) {
  const p = new URLSearchParams();
  if (f.q) p.set('q', f.q);
  if (f.filter) p.set('filter', f.filter);
  if (f.sub) p.set('sub', f.sub);
  Object.entries(LIST_KEYS).forEach(([field, key]) => {
    if (f[field]?.length) p.set(key, f[field].join(','));
  });
  if (f.min !== null && f.min !== undefined) p.set('min', String(f.min));
  if (f.max !== null && f.max !== undefined) p.set('max', String(f.max));
  if (f.inStock) p.set('stock', '1');
  if (f.sort && f.sort !== DEFAULT_SORT) p.set('sort', f.sort);
  if (f.page > 1) p.set('page', String(f.page));
  return p;
}

export const toggleIn = (list, value) => (list.includes(value) ? list.filter((v) => v !== value) : [...list, value]);

/* ------------------------------------------------------------ filtering */

/** Products for the route + search + ?filter, before facet filters. */
export function getBaseProducts(slug, q, filter) {
  let list = getProductsByCategory(slug || null);
  if (q) {
    const hits = new Set(searchProducts(q).map((p) => p.id));
    list = list.filter((p) => hits.has(p.id));
  }
  if (filter === 'new') list = list.filter((p) => p.tags.includes('new'));
  if (filter === 'flash-sale') list = list.filter((p) => isOnSale(p) || p.flashSale);
  return list;
}

export const filterBySub = (list, sub) => (sub ? list.filter((p) => p.subcategory === sub) : list);

/** "Cotton · Embroidered" → "Cotton" */
export const fabricOf = (product) => (product.fabric || '').split(' · ')[0];

export function applyFilters(list, f) {
  return list.filter((p) => {
    if (f.inStock && p.soldOut) return false;
    if (f.sizes.length && !p.sizes.some((s) => f.sizes.includes(s.label) && (!f.inStock || s.stock > 0))) return false;
    if (f.colours.length && !p.colours.some((c) => f.colours.includes(c.name))) return false;
    if (f.fabrics.length && !f.fabrics.includes(fabricOf(p))) return false;
    if (f.brands.length && !f.brands.includes(getBrand(p).slug)) return false;
    if (f.min !== null && p.price < f.min) return false;
    if (f.max !== null && p.price > f.max) return false;
    return true;
  });
}

export function sortProducts(list, sort) {
  const indexed = list.map((p, i) => ({ p, i }));
  const by = {
    newest: (a, b) => Number(b.p.tags.includes('new')) - Number(a.p.tags.includes('new')) || a.i - b.i,
    'price-asc': (a, b) => a.p.price - b.p.price || a.i - b.i,
    'price-desc': (a, b) => b.p.price - a.p.price || a.i - b.i,
    'best-selling': (a, b) =>
      Number(b.p.tags.includes('bestseller')) - Number(a.p.tags.includes('bestseller')) ||
      b.p.reviewsCount - a.p.reviewsCount ||
      a.i - b.i,
  };
  return indexed.sort(by[sort] || by.newest).map((x) => x.p);
}

/* --------------------------------------------------------------- facets */

const sizeRank = (label) => {
  const i = sizeOrder.indexOf(label);
  return i === -1 ? sizeOrder.length : i;
};

/** Option lists + counts for the filter UI, computed over `list`. */
export function buildFacets(list) {
  const sizes = new Map();
  const colours = new Map();
  const fabrics = new Map();
  const brandCounts = new Map();
  let min = Infinity;
  let max = 0;

  list.forEach((p) => {
    p.sizes.forEach((s) => sizes.set(s.label, (sizes.get(s.label) || 0) + 1));
    p.colours.forEach((c) => {
      const entry = colours.get(c.name) || { name: c.name, hex: c.hex, count: 0 };
      entry.count += 1;
      colours.set(c.name, entry);
    });
    const fabric = fabricOf(p);
    if (fabric) fabrics.set(fabric, (fabrics.get(fabric) || 0) + 1);
    const brand = getBrand(p).slug;
    brandCounts.set(brand, (brandCounts.get(brand) || 0) + 1);
    min = Math.min(min, p.price);
    max = Math.max(max, p.price);
  });

  return {
    sizes: [...sizes.entries()]
      .map(([label, count]) => ({ label, count }))
      .sort((a, b) => sizeRank(a.label) - sizeRank(b.label)),
    colours: [...colours.values()].sort((a, b) => b.count - a.count),
    fabrics: [...fabrics.entries()].map(([name, count]) => ({ name, count })).sort((a, b) => b.count - a.count),
    brands: brands.map((b) => ({ ...b, count: brandCounts.get(b.slug) || 0 })).filter((b) => b.count > 0),
    price: list.length ? { min, max } : { min: 0, max: 0 },
  };
}

/** True when a swatch is light enough to need a dark tick mark. */
export function isLightHex(hex) {
  const v = hex.replace('#', '');
  const [r, g, b] = [0, 2, 4].map((i) => parseInt(v.slice(i, i + 2), 16));
  return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.6;
}

/* ---------------------------------------------------------------- price */

/** Filter values → PriceRange draft strings. */
export const toPriceDraft = (f) => ({
  min: f.min === null ? '' : String(f.min),
  max: f.max === null ? '' : String(f.max),
});

/** PriceRange draft strings → { min, max } filter patch (swapped if reversed). */
export function toPricePatch(draft) {
  let min = draft.min === '' ? null : Number(draft.min);
  let max = draft.max === '' ? null : Number(draft.max);
  if (min !== null && max !== null && min > max) [min, max] = [max, min];
  return { min, max };
}

/* ---------------------------------------------------------------- chips */

export function priceLabel(min, max) {
  if (min !== null && max !== null) return `${formatBDT(min)} – ${formatBDT(max)}`;
  if (min !== null) return `from ${formatBDT(min)}`;
  return `up to ${formatBDT(max)}`;
}

/**
 * Active filter chips. Each chip has a desktop `label`, a phone `short`
 * label, an aria label and the filter `patch` that removes it.
 */
export function buildChips(f) {
  const chips = [];
  f.sizes.forEach((s) =>
    chips.push({ key: `size-${s}`, label: `Size: ${s}`, short: `Size ${s}`, aria: `Remove filter size ${s}`, patch: { sizes: f.sizes.filter((x) => x !== s) } }),
  );
  f.colours.forEach((c) =>
    chips.push({ key: `colour-${c}`, label: `Colour: ${c}`, short: c, aria: `Remove filter colour ${c}`, patch: { colours: f.colours.filter((x) => x !== c) } }),
  );
  f.fabrics.forEach((x) =>
    chips.push({ key: `fabric-${x}`, label: `Fabric: ${x}`, short: x, aria: `Remove filter fabric ${x}`, patch: { fabrics: f.fabrics.filter((y) => y !== x) } }),
  );
  f.brands.forEach((slug) => {
    const name = brands.find((b) => b.slug === slug)?.name || slug;
    chips.push({ key: `brand-${slug}`, label: `Brand: ${name}`, short: name, aria: `Remove filter brand ${name}`, patch: { brands: f.brands.filter((y) => y !== slug) } });
  });
  if (f.min !== null || f.max !== null) {
    const range = priceLabel(f.min, f.max);
    chips.push({ key: 'price', label: `Price: ${range}`, short: range, aria: `Remove price filter ${range}`, patch: { min: null, max: null } });
  }
  if (f.inStock) chips.push({ key: 'stock', label: 'In stock only', short: 'In stock', aria: 'Remove in stock only filter', patch: { inStock: false } });
  return chips;
}

/** Patch that clears every facet filter (keeps route, search, sub-category, sort). */
export const CLEAR_PATCH = {
  sizes: [],
  colours: [],
  fabrics: [],
  brands: [],
  min: null,
  max: null,
  inStock: false,
};
