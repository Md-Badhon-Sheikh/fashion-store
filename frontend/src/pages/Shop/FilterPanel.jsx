import { useId, useState } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { formatBDT } from '../../utils/format.js';
import PriceRange from './PriceRange.jsx';
import { isLightHex, toggleIn, toPriceDraft, toPricePatch } from './shopFilters.js';

/**
 * Desktop filter sidebar (Shop.dc.html). Every control writes straight to the
 * URL through onChange(patch); price applies on "Apply price".
 *
 * Props: filters, facets, tree (category tree from Shop), onChange, onClearAll
 */
export default function FilterPanel({ filters, facets, tree, onChange, onClearAll }) {
  return (
    <aside className="shop-filters" aria-label="Filters">
      <div className="shop-filters__head">
        <h2 className="shop-filters__title">
          <Icon name="filter" size={18} strokeWidth={2} />
          Filters
        </h2>
        <button type="button" className="shop-link-btn" onClick={onClearAll}>
          Clear all
        </button>
      </div>

      <CategoryTree tree={tree} />

      {facets.price.max > 0 && (
        <FilterSection title="Price (৳)">
          <PriceFilter
            key={`${filters.min}-${filters.max}`}
            filters={filters}
            bounds={facets.price}
            onApply={(patch) => onChange(patch)}
          />
        </FilterSection>
      )}

      {facets.sizes.length > 0 && (
        <FilterSection title="Size" list>
          {(labelId) => (
            <div role="group" aria-labelledby={labelId} className="shop-filters__checks">
              {facets.sizes.map((s) => {
                const on = filters.sizes.includes(s.label);
                return (
                  <button
                    key={s.label}
                    type="button"
                    role="checkbox"
                    aria-checked={on}
                    className={`shop-check${on ? ' is-on' : ''}`}
                    onClick={() => onChange({ sizes: toggleIn(filters.sizes, s.label) })}
                  >
                    <span className="shop-check__box" aria-hidden="true">
                      {on && <Icon name="check" size={14} strokeWidth={3} />}
                    </span>
                    <span className="shop-check__label">{s.label}</span>
                    <span className="shop-check__count">({s.count})</span>
                  </button>
                );
              })}
            </div>
          )}
        </FilterSection>
      )}

      {facets.colours.length > 0 && (
        <FilterSection title="Colour">
          {(labelId) => (
            <div role="group" aria-labelledby={labelId} className="shop-filters__swatches">
              {facets.colours.map((c) => {
                const on = filters.colours.includes(c.name);
                return (
                  <button
                    key={c.name}
                    type="button"
                    aria-pressed={on}
                    aria-label={`${c.name} (${c.count})`}
                    className={`shop-swatch${on ? ' is-on' : ''}`}
                    onClick={() => onChange({ colours: toggleIn(filters.colours, c.name) })}
                  >
                    <span className="shop-swatch__ring">
                      <span className="shop-swatch__dot" style={{ background: c.hex }}>
                        {on && (
                          <Icon
                            name="check"
                            size={14}
                            strokeWidth={3}
                            style={{ color: isLightHex(c.hex) ? 'var(--ink)' : '#fff' }}
                          />
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
          )}
        </FilterSection>
      )}

      {facets.fabrics.length > 0 && (
        <FilterSection title="Fabric" list>
          {facets.fabrics.map((f) => (
            <CheckRow
              key={f.name}
              label={f.name}
              count={f.count}
              checked={filters.fabrics.includes(f.name)}
              onToggle={() => onChange({ fabrics: toggleIn(filters.fabrics, f.name) })}
            />
          ))}
        </FilterSection>
      )}

      {facets.brands.length > 0 && (
        <FilterSection title="Brand" list>
          {facets.brands.map((b) => (
            <CheckRow
              key={b.slug}
              label={b.name}
              count={b.count}
              checked={filters.brands.includes(b.slug)}
              onToggle={() => onChange({ brands: toggleIn(filters.brands, b.slug) })}
            />
          ))}
        </FilterSection>
      )}

      <FilterSection title="Availability" last>
        <button
          type="button"
          role="switch"
          aria-checked={filters.inStock}
          className="shop-switch-row"
          onClick={() => onChange({ inStock: !filters.inStock })}
        >
          In stock only
          <span className={`shop-switch${filters.inStock ? ' is-on' : ''}`} aria-hidden="true">
            <span />
          </span>
        </button>
      </FilterSection>
    </aside>
  );
}

/** Section with an h3; children may be a render function receiving the heading id. */
function FilterSection({ title, list = false, last = false, children }) {
  const id = useId();
  return (
    <div className={`shop-filters__section${list ? ' shop-filters__section--list' : ''}${last ? ' shop-filters__section--last' : ''}`}>
      <h3 id={id} className="shop-filters__heading">
        {title}
      </h3>
      {typeof children === 'function' ? children(id) : children}
    </div>
  );
}

function CheckRow({ label, count, checked, onToggle }) {
  return (
    <label className="shop-check-row">
      <input type="checkbox" className="checkbox" checked={checked} onChange={onToggle} />
      <span className="shop-check-row__label">{label}</span>
      <span className="shop-check__count">({count})</span>
    </label>
  );
}

function PriceFilter({ filters, bounds, onApply }) {
  const [draft, setDraft] = useState(() => toPriceDraft(filters));
  return (
    <form
      className="shop-filters__price"
      onSubmit={(e) => {
        e.preventDefault();
        onApply(toPricePatch(draft));
      }}
    >
      <PriceRange bounds={bounds} value={draft} onChange={setDraft} />
      <button type="submit" className="btn btn--outline btn--block shop-filters__apply">
        Apply price
      </button>
      <p className="shop-filters__note">
        Range in this category: {formatBDT(bounds.min)} – {formatBDT(bounds.max)}
      </p>
    </form>
  );
}

/** Department → category tree with live counts; the current department starts open. */
function CategoryTree({ tree }) {
  const [open, setOpen] = useState(() => (tree.find((d) => d.current) ?? tree[0])?.slug ?? null);
  return (
    <div className="shop-filters__section">
      <h3 className="shop-filters__heading">Category</h3>
      <ul className="shop-tree" role="list">
        {tree.map((dept) => {
          const expanded = open === dept.slug;
          const panelId = `shop-tree-${dept.slug}`;
          return (
            <li key={dept.slug}>
              <button
                type="button"
                className="shop-tree__dept"
                aria-expanded={expanded}
                aria-controls={panelId}
                onClick={() => setOpen(expanded ? null : dept.slug)}
              >
                {dept.name}
                <Icon name={expanded ? 'chevron-up' : 'chevron-down'} size={16} strokeWidth={2} />
              </button>
              {expanded && (
                <ul id={panelId} className="shop-tree__items" role="list">
                  {dept.items.map((item) => (
                    <li key={item.to}>
                      <Link
                        to={item.to}
                        className={`shop-tree__link${item.current ? ' is-current' : ''}`}
                        aria-current={item.current ? 'page' : undefined}
                      >
                        {item.label}
                        <span className="shop-tree__count">{item.count}</span>
                      </Link>
                    </li>
                  ))}
                </ul>
              )}
            </li>
          );
        })}
      </ul>
    </div>
  );
}
