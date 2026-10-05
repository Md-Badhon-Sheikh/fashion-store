import { useCallback, useMemo, useRef, useState } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import Breadcrumbs from '../../components/ui/Breadcrumbs.jsx';
import Icon from '../../components/ui/Icon.jsx';
import { categories, getCategoriesByParent, parents, resolveShopSlug } from '../../data/categories.js';
import { getProductsByCategory } from '../../data/products.js';
import { SHOP_PAGE_SIZE, listingCopy, sortOptions } from '../../data/shop.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { PHONE_QUERY, useMediaQuery } from '../../hooks/useMediaQuery.js';
import NotFound from '../NotFound/NotFound.jsx';
import FilterPanel from './FilterPanel.jsx';
import { FilterSheet, SortSheet } from './FilterSheet.jsx';
import Pagination from './Pagination.jsx';
import ShopCard from './ShopCard.jsx';
import {
  CLEAR_PATCH,
  applyFilters,
  buildChips,
  buildFacets,
  filterBySub,
  getBaseProducts,
  readFilters,
  sortProducts,
  writeFilters,
} from './shopFilters.js';
import './Shop.css';

/**
 * Shop listing — Shop.dc.html (desktop) / M-Shop.dc.html (≤ 768px).
 *   /shop, /shop/:category (category or department), ?q=, ?filter=new|flash-sale
 * Filters, sort and page live in the query string (see shopFilters.js).
 */
export default function Shop() {
  const { category: slug } = useParams();
  const resolved = slug ? resolveShopSlug(slug) : null;
  if (slug && !resolved) return <NotFound />;
  return <ShopListing key={slug || 'all'} slug={slug || null} resolved={resolved} />;
}

/* ------------------------------------------------------------- helpers */

/** Title, breadcrumbs and intro copy for the current listing. */
function describeListing(slug, resolved, f) {
  const crumbs = [{ label: 'Home', to: '/' }];
  let copy;
  let dept = null;

  if (resolved?.type === 'category') {
    const { category, parent } = resolved;
    if (parent && parent.slug !== category.slug) {
      crumbs.push({ label: parent.name, to: `/shop/${parent.slug}` });
      dept = parent.name;
    }
    crumbs.push({ label: category.name, to: `/shop/${category.slug}` });
    copy = { title: category.title, short: category.name, description: category.description };
  } else if (resolved?.type === 'parent') {
    const { parent } = resolved;
    copy = listingCopy[parent.slug] || { title: parent.name, short: parent.name, description: '' };
    crumbs.push({ label: parent.name, to: `/shop/${slug}` });
  } else if (f.filter && listingCopy[f.filter]) {
    copy = listingCopy[f.filter];
    crumbs.push({ label: 'Shop', to: '/shop' }, { label: copy.short });
  } else if (f.q) {
    copy = { title: `Results for “${f.q}”`, short: 'Search results', description: '' };
    crumbs.push({ label: 'Shop', to: '/shop' }, { label: 'Search' });
  } else {
    copy = listingCopy.all;
    crumbs.push({ label: 'Shop' });
  }

  const searchNote = f.q && (resolved || f.filter) ? `Showing results for “${f.q}”` : '';
  return { ...copy, crumbs, dept, searchNote };
}

/** Department → category tree for the sidebar, with live product counts. */
function buildTree(slug, sub) {
  return parents.map((dept) => {
    const cats = getCategoriesByParent(dept.slug);
    const single = cats.length === 1 && cats[0].slug === dept.slug ? cats[0] : null;
    const items = [
      {
        label: `All ${dept.name.toLowerCase()}`,
        to: `/shop/${dept.slug}`,
        count: getProductsByCategory(dept.slug).length,
        current: slug === dept.slug && !sub,
      },
    ];
    if (single) {
      single.subcategories.forEach((s) => {
        const count = getProductsByCategory(single.slug).filter((p) => p.subcategory === s.slug).length;
        if (count) items.push({ label: s.name, to: `/shop/${single.slug}?sub=${s.slug}`, count, current: slug === single.slug && sub === s.slug });
      });
    } else {
      cats.forEach((c) =>
        items.push({ label: c.menuName || c.name, to: `/shop/${c.slug}`, count: getProductsByCategory(c.slug).length, current: slug === c.slug }),
      );
    }
    const inDept = slug === dept.slug || categories.some((c) => c.slug === slug && c.parent === dept.slug);
    return { slug: dept.slug, name: dept.name, items, current: inDept };
  });
}

/* ------------------------------------------------------------- listing */

function ShopListing({ slug, resolved }) {
  const [params, setParams] = useSearchParams();
  const navigate = useNavigate();
  const isPhone = useMediaQuery(PHONE_QUERY);
  const resultsRef = useRef(null);
  const [sheet, setSheet] = useState(null); // 'filter' | 'sort' | null

  const filters = useMemo(() => readFilters(params), [params]);
  const listing = describeListing(slug, resolved, filters);
  useDocumentTitle(listing.title);

  const base = useMemo(() => getBaseProducts(slug, filters.q, filters.filter), [slug, filters.q, filters.filter]);
  const scope = useMemo(() => filterBySub(base, filters.sub), [base, filters.sub]);
  const facets = useMemo(() => buildFacets(scope), [scope]);
  const results = useMemo(() => sortProducts(applyFilters(scope, filters), filters.sort), [scope, filters]);
  const tree = useMemo(() => buildTree(slug, filters.sub), [slug, filters.sub]);
  const chips = buildChips(filters);

  const total = results.length;
  const pageCount = Math.max(1, Math.ceil(total / SHOP_PAGE_SIZE));
  const page = Math.min(filters.page, pageCount);
  const start = (page - 1) * SHOP_PAGE_SIZE;
  const visible = isPhone ? results.slice(0, page * SHOP_PAGE_SIZE) : results.slice(start, start + SHOP_PAGE_SIZE);
  const sortLabel = sortOptions.find((o) => o.value === filters.sort)?.label ?? sortOptions[0].label;

  /** Merge a patch into the URL. Filter changes reset to page 1. */
  const update = useCallback(
    (patch, { push = false } = {}) => {
      setParams((prev) => writeFilters({ ...readFilters(prev), page: 1, ...patch }), {
        replace: !push,
        preventScrollReset: true,
      });
    },
    [setParams],
  );
  const clearAll = useCallback(() => update(CLEAR_PATCH), [update]);
  const closeSheet = useCallback(() => setSheet(null), []);

  const goToPage = (n) => {
    update({ page: n }, { push: true });
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    requestAnimationFrame(() => resultsRef.current?.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }));
  };

  const goBack = () => {
    if (window.history.state?.idx > 0) navigate(-1);
    else navigate(resolved?.type === 'category' && resolved.parent ? `/shop/${resolved.parent.slug}` : '/');
  };

  // Sub-category pills (category pages) / category links (department pages)
  const subOptions =
    resolved?.type === 'category'
      ? [
          { slug: '', name: `All ${resolved.category.name.toLowerCase()}`, count: base.length },
          ...resolved.category.subcategories
            .map((s) => ({ slug: s.slug, name: s.name, count: base.filter((p) => p.subcategory === s.slug).length }))
            .filter((s) => s.count > 0),
        ]
      : null;
  const deptLinks =
    resolved?.type === 'parent'
      ? getCategoriesByParent(resolved.parent.slug).map((c) => ({
          to: `/shop/${c.slug}`,
          name: c.menuName || c.name,
          count: getProductsByCategory(c.slug).length,
        }))
      : null;

  const scopeName = resolved?.type === 'category' ? resolved.category.name.toLowerCase() : 'products';
  const shownOnPhone = Math.min(page * SHOP_PAGE_SIZE, total);

  return (
    <div className="shop">
      {/* ---------- desktop intro ---------- */}
      <div className="container shop__crumbs only-desktop">
        <Breadcrumbs items={listing.crumbs} />
      </div>
      <section className="container shop__intro only-desktop" aria-labelledby="shop-title">
        <div className="shop__title-row">
          <h1 id="shop-title" className="shop__title">
            {listing.title}
          </h1>
          <span className="shop__count">
            {base.length} {base.length === 1 ? 'item' : 'items'}
          </span>
        </div>
        {listing.searchNote && <p className="shop__search-note">{listing.searchNote}</p>}
        {listing.description && <p className="shop__desc">{listing.description}</p>}
        {subOptions && subOptions.length > 1 && (
          <div role="group" aria-label="Sub-categories" className="shop__subs">
            {subOptions.map((s) => (
              <button
                key={s.slug || 'all'}
                type="button"
                className="chip"
                aria-pressed={filters.sub === s.slug}
                onClick={() => update({ sub: s.slug })}
              >
                {s.name} · {s.count}
              </button>
            ))}
          </div>
        )}
        {deptLinks && (
          <nav aria-label="Categories" className="shop__subs">
            {deptLinks.map((c) => (
              <Link key={c.to} to={c.to} className="chip">
                {c.name} · {c.count}
              </Link>
            ))}
          </nav>
        )}
      </section>

      {/* ---------- phone header + filter/sort bar ---------- */}
      <div className="shop-m-head only-mobile">
        <button type="button" className="icon-btn" aria-label="Back" onClick={goBack}>
          <Icon name="chevron-left" size={22} strokeWidth={2} />
        </button>
        <div className="shop-m-head__text">
          <h1 className="shop-m-head__title">{listing.short}</h1>
          <span className="shop-m-head__sub">
            {total} {total === 1 ? 'item' : 'items'}
            {listing.dept ? ` · ${listing.dept}` : ''}
          </span>
        </div>
      </div>
      <div className="shop-m-bar only-mobile">
        <button
          type="button"
          className="shop-m-bar__btn"
          aria-haspopup="dialog"
          aria-expanded={sheet === 'filter'}
          onClick={() => setSheet('filter')}
        >
          <Icon name="filter" size={18} strokeWidth={1.9} />
          Filter
          {chips.length > 0 && (
            <span className="shop-m-bar__badge">
              {chips.length}
              <span className="visually-hidden"> active</span>
            </span>
          )}
        </button>
        <button
          type="button"
          className="shop-m-bar__btn"
          aria-haspopup="dialog"
          aria-expanded={sheet === 'sort'}
          onClick={() => setSheet('sort')}
        >
          <Icon name="sort" size={18} strokeWidth={1.9} />
          Sort<span className="shop-m-bar__value">: {sortLabel}</span>
        </button>
      </div>
      {chips.length > 0 && (
        <div className="shop-m-chips h-scroll only-mobile">
          {chips.map((c) => (
            <button key={c.key} type="button" className="shop-m-chip" aria-label={c.aria} onClick={() => update(c.patch)}>
              {c.short}
              <Icon name="close" size={14} strokeWidth={2.4} />
            </button>
          ))}
          <button type="button" className="shop-link-btn shop-link-btn--ink" onClick={clearAll}>
            Clear all
          </button>
        </div>
      )}
      {total > 0 && (
        <p className="shop-m-showing only-mobile" aria-live="polite">
          Showing {shownOnPhone} of {total} results
        </p>
      )}

      {/* ---------- sidebar + results ---------- */}
      <div className="container shop__layout">
        <div className="shop__sidebar only-desktop">
          <FilterPanel filters={filters} facets={facets} tree={tree} onChange={update} onClearAll={clearAll} />
        </div>

        <section className="shop__results" aria-label="Products" ref={resultsRef}>
          <div className="shop-toolbar only-desktop">
            <div className="shop-toolbar__chips">
              <span className="shop-toolbar__label">Active filters:</span>
              {chips.map((c) => (
                <button key={c.key} type="button" className="shop-chip" aria-label={c.aria} onClick={() => update(c.patch)}>
                  {c.label}
                  <Icon name="close" size={14} strokeWidth={2.4} />
                </button>
              ))}
              {chips.length === 0 ? (
                <span className="shop-toolbar__none">None — showing all {scopeName}</span>
              ) : (
                <button type="button" className="shop-link-btn" onClick={clearAll}>
                  Clear all
                </button>
              )}
            </div>
            <div className="shop-toolbar__row">
              <p className="shop-toolbar__showing" aria-live="polite">
                {total === 0
                  ? 'No products found'
                  : `Showing ${start + 1}–${Math.min(start + SHOP_PAGE_SIZE, total)} of ${total} ${total === 1 ? 'product' : 'products'}`}
              </p>
              <div className="shop-toolbar__sort">
                <label htmlFor="shop-sort" className="shop-toolbar__sort-label">
                  Sort by
                </label>
                <select
                  id="shop-sort"
                  className="select shop-toolbar__select"
                  value={filters.sort}
                  onChange={(e) => update({ sort: e.target.value })}
                >
                  {sortOptions.map((o) => (
                    <option key={o.value} value={o.value}>
                      {o.label}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {total === 0 ? (
            <EmptyState hasFilters={chips.length > 0 || Boolean(filters.sub)} onClear={() => update({ ...CLEAR_PATCH, sub: '' })} />
          ) : (
            <>
              <ul className="shop-grid" role="list">
                {visible.map((p, i) => (
                  <li key={p.id}>
                    <ShopCard product={p} loading={i < 4 ? 'eager' : 'lazy'} />
                  </li>
                ))}
              </ul>

              <div className="only-desktop">
                <Pagination page={page} count={pageCount} onChange={goToPage} />
              </div>

              <div className="shop-more only-mobile">
                <div
                  className="shop-more__bar"
                  role="progressbar"
                  aria-label="Products viewed"
                  aria-valuemin={0}
                  aria-valuemax={total}
                  aria-valuenow={shownOnPhone}
                >
                  <span style={{ width: `${Math.round((shownOnPhone / total) * 100)}%` }} />
                </div>
                <span className="shop-more__text">
                  You’ve viewed {shownOnPhone} of {total} products
                </span>
                {shownOnPhone < total && (
                  <button type="button" className="btn btn--outline btn--block shop-more__btn" onClick={() => update({ page: page + 1 })}>
                    Load more
                  </button>
                )}
              </div>
            </>
          )}
        </section>
      </div>

      {isPhone && sheet === 'filter' && (
        <FilterSheet filters={filters} base={base} subOptions={subOptions} onApply={update} onClose={closeSheet} />
      )}
      {isPhone && sheet === 'sort' && (
        <SortSheet value={filters.sort} onChange={(sort) => update({ sort })} onClose={closeSheet} />
      )}
    </div>
  );
}

function EmptyState({ hasFilters, onClear }) {
  return (
    <div className="shop-empty">
      <span className="shop-empty__icon" aria-hidden="true">
        <Icon name="search" size={28} />
      </span>
      <h2 className="shop-empty__title">No products match your selection</h2>
      <p className="shop-empty__text">
        {hasFilters
          ? 'Try removing a filter or widening the price range.'
          : 'We couldn’t find anything here yet. Try another category or search term.'}
      </p>
      <div className="shop-empty__actions">
        {hasFilters && (
          <button type="button" className="btn btn--primary" onClick={onClear}>
            Clear all filters
          </button>
        )}
        <Link to="/shop" className="btn btn--outline">
          Browse all products
        </Link>
      </div>
    </div>
  );
}
