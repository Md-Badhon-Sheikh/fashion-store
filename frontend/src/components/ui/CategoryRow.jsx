import { useCallback, useEffect, useId, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import Icon from './Icon.jsx';
import ProductCard from './ProductCard.jsx';
import './CategoryRow.css';

/**
 * Horizontal product row with title, subtitle, VIEW ALL, prev/next arrows and
 * position dots. 5 cards visible on desktop (4 ≤1200px, 3 ≤900px); on phones
 * it is a swipeable scroll-snap row (arrows hidden, dots kept).
 *
 * Props:
 *   title       string (h2)
 *   subtitle    string, optional
 *   products    product objects (data/products.js)
 *   viewAllTo   route for VIEW ALL, optional
 *   cardVariant ProductCard variant, default "carousel"
 *   className   extra class on the <section>
 */
export default function CategoryRow({ title, subtitle, products, viewAllTo, cardVariant = 'carousel', className = '' }) {
  const trackRef = useRef(null);
  const headingId = useId();
  const [state, setState] = useState({ index: 0, maxIndex: 0 });

  const measure = useCallback(() => {
    const track = trackRef.current;
    if (!track || !track.firstElementChild) return;
    const card = track.firstElementChild;
    const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
    const step = card.getBoundingClientRect().width + gap;
    if (!step) return;
    const maxScroll = track.scrollWidth - track.clientWidth;
    const maxIndex = Math.max(0, Math.round(maxScroll / step));
    const index = maxScroll <= 1 ? 0 : Math.min(maxIndex, Math.round(track.scrollLeft / step));
    setState((s) => (s.index === index && s.maxIndex === maxIndex ? s : { index, maxIndex }));
  }, []);

  useEffect(() => {
    const track = trackRef.current;
    if (!track) return undefined;
    measure();
    const ro = typeof ResizeObserver !== 'undefined' ? new ResizeObserver(measure) : null;
    ro?.observe(track);
    track.addEventListener('scroll', measure, { passive: true });
    return () => {
      ro?.disconnect();
      track.removeEventListener('scroll', measure);
    };
  }, [measure, products.length]);

  const scrollToIndex = (i) => {
    const track = trackRef.current;
    if (!track || !track.firstElementChild) return;
    const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
    const step = track.firstElementChild.getBoundingClientRect().width + gap;
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    track.scrollTo({ left: i * step, behavior: reduce ? 'auto' : 'smooth' });
  };

  const atStart = state.index <= 0;
  const atEnd = state.index >= state.maxIndex;

  return (
    <section className={`category-row ${className}`.trim()} aria-labelledby={headingId}>
      <div className="category-row__head">
        <div>
          <h2 id={headingId} className="category-row__title">
            {title}
          </h2>
          {subtitle && <p className="category-row__sub">{subtitle}</p>}
        </div>
        {viewAllTo && (
          <Link to={viewAllTo} className="category-row__all">
            View all<span className="visually-hidden"> {title}</span>
          </Link>
        )}
      </div>

      <div className="category-row__viewport">
        <button
          type="button"
          className="category-row__arrow category-row__arrow--prev"
          aria-label={`Previous ${title}`}
          onClick={() => scrollToIndex(state.index - 1)}
          disabled={atStart}
        >
          <Icon name="chevron-left" size={18} strokeWidth={2.4} />
        </button>

        <ul className="category-row__track" ref={trackRef} role="list">
          {products.map((p) => (
            <li key={p.id} className="category-row__item">
              <ProductCard product={p} variant={cardVariant} />
            </li>
          ))}
        </ul>

        <button
          type="button"
          className="category-row__arrow category-row__arrow--next"
          aria-label={`Next ${title}`}
          onClick={() => scrollToIndex(state.index + 1)}
          disabled={atEnd}
        >
          <Icon name="chevron-right" size={18} strokeWidth={2.4} />
        </button>
      </div>

      {state.maxIndex > 0 && (
        <div className="category-row__dots" aria-hidden="true">
          {Array.from({ length: state.maxIndex + 1 }, (_, i) => (
            <span key={i} className={`category-row__dot${i === state.index ? ' is-active' : ''}`} />
          ))}
        </div>
      )}
    </section>
  );
}
