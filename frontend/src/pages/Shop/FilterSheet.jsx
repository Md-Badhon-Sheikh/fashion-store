import { useMemo, useState } from 'react';
import Icon from '../../components/ui/Icon.jsx';
import { sortOptions } from '../../data/shop.js';
import BottomSheet from './BottomSheet.jsx';
import PriceRange from './PriceRange.jsx';
import {
  CLEAR_PATCH,
  applyFilters,
  buildFacets,
  filterBySub,
  isLightHex,
  toggleIn,
  toPriceDraft,
  toPricePatch,
} from './shopFilters.js';
import { formatBDT } from '../../utils/format.js';

/**
 * Phone filter sheet (M-Shop "Filter sheet open"). Edits a draft; the
 * "Show N results" button shows the live count for the draft and applies it.
 * Closing without applying discards the draft.
 *
 * Props: filters, base (listing before sub-category), subOptions
 *        ([{ slug, name, count }] or null), onApply(patch), onClose
 */
export function FilterSheet({ filters, base, subOptions, onApply, onClose }) {
  const [draft, setDraft] = useState(() => ({
    sub: filters.sub,
    sizes: filters.sizes,
    colours: filters.colours,
    fabrics: filters.fabrics,
    brands: filters.brands,
    inStock: filters.inStock,
  }));
  const [price, setPrice] = useState(() => toPriceDraft(filters));

  const scope = useMemo(() => filterBySub(base, draft.sub), [base, draft.sub]);
  const facets = useMemo(() => buildFacets(scope), [scope]);
  const count = useMemo(() => applyFilters(scope, { ...draft, ...toPricePatch(price) }).length, [scope, draft, price]);

  const set = (patch) => setDraft((d) => ({ ...d, ...patch }));
  const clear = () => {
    setDraft((d) => ({ ...d, ...CLEAR_PATCH, sub: '' }));
    setPrice({ min: '', max: '' });
  };
  const apply = () => {
    onApply({ ...draft, ...toPricePatch(price) });
    onClose();
  };

  const footer = (
    <>
      <button type="button" className="btn btn--outline sheet__clear" onClick={clear}>
        Clear
      </button>
      <button type="button" className="btn btn--brand sheet__apply" onClick={apply}>
        Show {count} {count === 1 ? 'result' : 'results'}
      </button>
    </>
  );

  return (
    <BottomSheet title="Filter" onClose={onClose} footer={footer} className="filter-sheet">
      {subOptions && subOptions.length > 1 && (
        <fieldset className="sheet__group">
          <legend className="sheet__legend">Category</legend>
          <div className="sheet__pills">
            {subOptions.map((s) => (
              <button
                key={s.slug || 'all'}
                type="button"
                aria-pressed={draft.sub === s.slug}
                className="sheet-pill"
                onClick={() => set({ sub: s.slug })}
              >
                {s.name}
              </button>
            ))}
          </div>
        </fieldset>
      )}

      {facets.sizes.length > 0 && (
        <fieldset className="sheet__group">
          <legend className="sheet__legend">
            Size
            <span className="sheet__legend-note">{draft.sizes.length ? `${draft.sizes.join(', ')} selected` : 'Any size'}</span>
          </legend>
          <div className="sheet__pills">
            {facets.sizes.map((s) => (
              <button
                key={s.label}
                type="button"
                aria-pressed={draft.sizes.includes(s.label)}
                className="sheet-pill sheet-pill--size"
                onClick={() => set({ sizes: toggleIn(draft.sizes, s.label) })}
              >
                {s.label}
              </button>
            ))}
          </div>
        </fieldset>
      )}

      {facets.colours.length > 0 && (
        <fieldset className="sheet__group">
          <legend className="sheet__legend">Colour</legend>
          <div className="sheet__swatches">
            {facets.colours.map((c) => {
              const on = draft.colours.includes(c.name);
              return (
                <button
                  key={c.name}
                  type="button"
                  aria-pressed={on}
                  aria-label={`${c.name} (${c.count})`}
                  className={`shop-swatch shop-swatch--sm${on ? ' is-on' : ''}`}
                  onClick={() => set({ colours: toggleIn(draft.colours, c.name) })}
                >
                  <span className="shop-swatch__ring">
                    <span className="shop-swatch__dot" style={{ background: c.hex }}>
                      {on && (
                        <Icon name="check" size={12} strokeWidth={3} style={{ color: isLightHex(c.hex) ? 'var(--ink)' : '#fff' }} />
                      )}
                    </span>
                  </span>
                  <span className="shop-swatch__name" aria-hidden="true">
                    {c.name}
                  </span>
                </button>
              );
            })}
          </div>
        </fieldset>
      )}

      {facets.price.max > 0 && (
        <fieldset className="sheet__group">
          <legend className="sheet__legend">Price range</legend>
          <PriceRange bounds={facets.price} value={price} onChange={setPrice} variant="sheet" />
          <div className="sheet__bounds" aria-hidden="true">
            <span>{formatBDT(facets.price.min)}</span>
            <span>{formatBDT(facets.price.max)}</span>
          </div>
        </fieldset>
      )}

      {facets.fabrics.length > 1 && (
        <fieldset className="sheet__group">
          <legend className="sheet__legend">Fabric</legend>
          <div className="sheet__pills">
            {facets.fabrics.map((f) => (
              <button
                key={f.name}
                type="button"
                aria-pressed={draft.fabrics.includes(f.name)}
                className="sheet-pill"
                onClick={() => set({ fabrics: toggleIn(draft.fabrics, f.name) })}
              >
                {f.name}
                <span className="sheet-pill__count">{f.count}</span>
              </button>
            ))}
          </div>
        </fieldset>
      )}

      {facets.brands.length > 1 && (
        <fieldset className="sheet__group">
          <legend className="sheet__legend">Brand</legend>
          <div className="sheet__pills">
            {facets.brands.map((b) => (
              <button
                key={b.slug}
                type="button"
                aria-pressed={draft.brands.includes(b.slug)}
                className="sheet-pill"
                onClick={() => set({ brands: toggleIn(draft.brands, b.slug) })}
              >
                {b.name}
                <span className="sheet-pill__count">{b.count}</span>
              </button>
            ))}
          </div>
        </fieldset>
      )}

      <div className="sheet__switch">
        <div>
          <div id="filter-sheet-stock" className="sheet__switch-title">
            In stock only
          </div>
          <div className="sheet__switch-text">Hide sold-out sizes and colours</div>
        </div>
        <button
          type="button"
          role="switch"
          aria-checked={draft.inStock}
          aria-labelledby="filter-sheet-stock"
          className="sheet__switch-btn"
          onClick={() => set({ inStock: !draft.inStock })}
        >
          <span className={`shop-switch shop-switch--lg${draft.inStock ? ' is-on' : ''}`} aria-hidden="true">
            <span />
          </span>
        </button>
      </div>
    </BottomSheet>
  );
}

/** Phone sort sheet: a radio list that applies on selection. */
export function SortSheet({ value, onChange, onClose }) {
  return (
    <BottomSheet title="Sort" onClose={onClose} className="sort-sheet">
      <div role="radiogroup" aria-label="Sort products by" className="sort-sheet__list">
        {sortOptions.map((o) => {
          const on = o.value === value;
          return (
            <button
              key={o.value}
              type="button"
              role="radio"
              aria-checked={on}
              className={`sort-sheet__option${on ? ' is-on' : ''}`}
              onClick={() => {
                onChange(o.value);
                onClose();
              }}
            >
              {o.label}
              {on && <Icon name="check" size={18} strokeWidth={2.4} />}
            </button>
          );
        })}
      </div>
    </BottomSheet>
  );
}
