import { useRef } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { sizeCharts } from '../../data/products.js';

/* ------------------------------------------------------------ panels */

function Description({ product }) {
  return (
    <div className="pdp-panel__prose">
      <p>{product.description}</p>
      {product.highlights.length > 0 && (
        <ul>
          {product.highlights.map((h) => (
            <li key={h}>{h}</li>
          ))}
        </ul>
      )}
    </div>
  );
}

function FabricCare({ product }) {
  return (
    <div className="pdp-panel__prose pdp-panel__prose--defs">
      <p>
        <strong>Fabric:</strong> {product.fabricDetail || product.fabric}
      </p>
      <p>
        <strong>Care:</strong> {product.care}
      </p>
    </div>
  );
}

function SizeChart({ product, selectedSize }) {
  const chart = product.sizeChart ? sizeCharts[product.sizeChart] : null;
  if (!chart) {
    return (
      <div className="pdp-panel__prose">
        <p>
          Available sizes: {product.sizes.map((s) => s.label).join(', ')}. Detailed measurements for this style are in our{' '}
          <Link to="/pages/size-guide" className="link-underline">
            size guide
          </Link>
          . Free size exchange within 7 days.
        </p>
      </div>
    );
  }
  return (
    <div className="pdp-chart">
      <div className="pdp-chart__table-wrap">
        <table className="pdp-chart__table">
          <caption className="pdp-chart__caption">All measurements in inches</caption>
          <thead>
            <tr>
              <th scope="col">Size</th>
              {chart.columns.map((c) => (
                <th key={c} scope="col">
                  {c}
                  <span className="pdp-chart__unit"> (in)</span>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {chart.rows.map((r) => (
              <tr key={r.size} className={r.size === selectedSize ? 'is-selected' : undefined}>
                <th scope="row">
                  {r.size}
                  {r.size === selectedSize && <span className="visually-hidden"> (selected)</span>}
                </th>
                {r.values.map((v, i) => (
                  <td key={chart.columns[i]}>{v}</td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="pdp-chart__measure">
        <h3 className="pdp-chart__measure-title">How to measure</h3>
        {chart.howToMeasure.map((t) => (
          <p key={t}>{t}</p>
        ))}
      </div>
    </div>
  );
}

const stars = (n) => '★★★★★'.slice(0, Math.round(n)) + '☆☆☆☆☆'.slice(0, 5 - Math.round(n));

/** Rating summary + review cards. `compact` = phone layout (M-Product). */
export function Reviews({ product, compact = false, headingId }) {
  const reviews = product.reviews || [];
  const breakdown = product.ratingBreakdown;
  const total = breakdown ? breakdown.reduce((n, b) => n + b.count, 0) : 0;

  const summary = (
    <div className="pdp-reviews__summary">
      <div className="pdp-reviews__score">
        <span className="pdp-reviews__avg">{product.rating.toFixed(1)}</span>
        {compact && (
          <span className="pdp-reviews__stars" role="img" aria-label={`${product.rating.toFixed(1)} out of 5 stars`}>
            {stars(product.rating)}
          </span>
        )}
        <span className="pdp-reviews__based">
          {compact ? `${product.reviewsCount} reviews` : `Based on ${product.reviewsCount} verified-purchase reviews`}
        </span>
        {!compact && (
          <Link to="/login" className="btn btn--outline pdp-reviews__write">
            Write a review
          </Link>
        )}
      </div>
      {compact && breakdown && (
        <ul className="pdp-reviews__bars" role="list" aria-label="Rating breakdown">
          {breakdown.map((b) => (
            <li key={b.stars} className="pdp-reviews__bar">
              <span className="pdp-reviews__bar-star">
                {b.stars}
                <span className="visually-hidden"> stars</span>
              </span>
              <span className="pdp-reviews__bar-track" aria-hidden="true">
                <span style={{ width: `${total ? Math.round((b.count / total) * 100) : 0}%` }} />
              </span>
              <span className="pdp-reviews__bar-n">
                {b.count}
                <span className="visually-hidden">{b.count === 1 ? ' review' : ' reviews'}</span>
              </span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );

  return (
    <div className={`pdp-reviews${compact ? ' pdp-reviews--compact' : ''}`}>
      {compact && (
        <div className="pdp-reviews__head">
          <h2 id={headingId} className="pdp-section-title">
            Customer reviews
          </h2>
        </div>
      )}
      <div className="pdp-reviews__grid">
        {summary}
        {reviews.map((r) => (
          <article key={r.date + r.text} className="pdp-review">
            <div className="pdp-review__top">
              <span className="pdp-review__stars" role="img" aria-label={`${r.rating} out of 5 stars`}>
                {stars(r.rating)}
              </span>
              {r.date && <span className="pdp-review__date">{r.date}</span>}
            </div>
            <p className="pdp-review__text">{r.text}</p>
            <p className="pdp-review__meta">{r.meta}</p>
          </article>
        ))}
        {reviews.length === 0 && (
          <p className="pdp-reviews__empty">No written reviews for this style yet. Bought it? Tell other shoppers how it fits.</p>
        )}
      </div>
      {compact && (
        <Link to="/login" className="btn btn--outline btn--block pdp-reviews__write">
          Write a review
        </Link>
      )}
    </div>
  );
}

/* -------------------------------------------------- tabs / accordions */

const INFO_SECTIONS = [
  { id: 'description', title: 'Description' },
  { id: 'fabric', title: 'Fabric & care' },
  { id: 'size-chart', title: 'Size chart' },
  { id: 'reviews', title: 'Reviews' },
];

function PanelContent({ id, product, selectedSize }) {
  if (id === 'description') return <Description product={product} />;
  if (id === 'fabric') return <FabricCare product={product} />;
  if (id === 'size-chart') return <SizeChart product={product} selectedSize={selectedSize} />;
  return <Reviews product={product} />;
}

/** Desktop tabs: Description / Fabric & care / Size chart / Reviews (n). */
export function InfoTabs({ product, tab, onTab, selectedSize, sectionRef }) {
  const tabRefs = useRef({});
  const ids = INFO_SECTIONS.map((s) => s.id);

  const onKeyDown = (e) => {
    const i = ids.indexOf(tab);
    let next = null;
    if (e.key === 'ArrowRight') next = ids[(i + 1) % ids.length];
    if (e.key === 'ArrowLeft') next = ids[(i - 1 + ids.length) % ids.length];
    if (e.key === 'Home') next = ids[0];
    if (e.key === 'End') next = ids[ids.length - 1];
    if (!next) return;
    e.preventDefault();
    onTab(next);
    tabRefs.current[next]?.focus();
  };

  return (
    <section ref={sectionRef} className="container pdp-tabs" aria-label="Product information">
      <div role="tablist" aria-label="Product information" className="pdp-tabs__list" onKeyDown={onKeyDown}>
        {INFO_SECTIONS.map((s) => (
          <button
            key={s.id}
            ref={(el) => {
              tabRefs.current[s.id] = el;
            }}
            type="button"
            role="tab"
            id={`pdp-tab-${s.id}`}
            aria-selected={tab === s.id}
            aria-controls={`pdp-panel-${s.id}`}
            tabIndex={tab === s.id ? 0 : -1}
            className="pdp-tabs__tab"
            onClick={() => onTab(s.id)}
          >
            {s.id === 'reviews' ? `Reviews (${product.reviewsCount})` : s.title}
          </button>
        ))}
      </div>
      <div
        role="tabpanel"
        id={`pdp-panel-${tab}`}
        aria-labelledby={`pdp-tab-${tab}`}
        tabIndex={0}
        className={`pdp-tabs__panel pdp-tabs__panel--${tab}`}
      >
        <PanelContent id={tab} product={product} selectedSize={selectedSize} />
      </div>
    </section>
  );
}

/** Phone accordions (M-Product): Description / Fabric & care / Size chart. */
export function InfoAccordions({ product, open, onToggle, selectedSize, sectionRef }) {
  return (
    <section ref={sectionRef} className="pdp-accordions" aria-label="Product information">
      {INFO_SECTIONS.filter((s) => s.id !== 'reviews').map((s) => {
        const expanded = Boolean(open[s.id]);
        return (
          <div key={s.id} className="pdp-accordion" data-section={s.id}>
            <h2 className="pdp-accordion__heading">
              <button
                type="button"
                className="pdp-accordion__btn"
                aria-expanded={expanded}
                aria-controls={`pdp-acc-${s.id}`}
                onClick={() => onToggle(s.id)}
              >
                {s.title}
                <Icon name="chevron-down" size={20} strokeWidth={2} className="pdp-accordion__chevron" />
              </button>
            </h2>
            {expanded && (
              <div id={`pdp-acc-${s.id}`} className="pdp-accordion__panel">
                <PanelContent id={s.id} product={product} selectedSize={selectedSize} />
              </div>
            )}
          </div>
        );
      })}
    </section>
  );
}
