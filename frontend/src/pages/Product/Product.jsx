import { useCallback, useMemo, useRef, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import Breadcrumbs from '../../components/ui/Breadcrumbs.jsx';
import Icon from '../../components/ui/Icon.jsx';
import QtyStepper from '../../components/ui/QtyStepper.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { useWishlist } from '../../context/WishlistContext.jsx';
import { getCategory, getParent } from '../../data/categories.js';
import { delivery } from '../../data/content.js';
import {
  getCategoryLabel,
  getDiscountPercent,
  getProductBySlug,
  getProductsByCategory,
  getProductsBySlugs,
  getStock,
  getVariantSku,
  isOnSale,
} from '../../data/products.js';
import { getBrand } from '../../data/shop.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { PHONE_QUERY, useMediaQuery } from '../../hooks/useMediaQuery.js';
import { formatBDT, imageUrl } from '../../utils/format.js';
import NotFound from '../NotFound/NotFound.jsx';
import CartToast from './CartToast.jsx';
import { InfoAccordions, InfoTabs, Reviews } from './ProductInfo.jsx';
import { DesktopGallery, MobileGallery } from './ProductGallery.jsx';
import './Product.css';

/**
 * Product detail — Product.dc.html (desktop) / M-Product.dc.html (≤ 768px).
 * Route /product/:slug; unknown slugs render the 404 view.
 */
export default function Product() {
  const { slug } = useParams();
  const product = getProductBySlug(slug);
  if (!product) return <NotFound />;
  return <ProductView key={product.id} product={product} />;
}

/* ------------------------------------------------------------- helpers */

const LOW_STOCK = 5;

const colourStock = (product, colourName) => product.sizes.reduce((n, s) => n + getStock(product, colourName, s.label), 0);

function initialVariant(product) {
  const colour = product.colours.find((c) => colourStock(product, c.name) > 0) || product.colours[0];
  const inStock = product.sizes.filter((s) => getStock(product, colour.name, s.label) > 0).map((s) => s.label);
  // The artboards open with "M" selected; otherwise the first size in stock.
  const size = inStock.includes('M') ? 'M' : inStock[0] ?? null;
  return { colour: colour.name, size };
}

/** Selected colour's photo first, then the shared product shots. */
function galleryFor(product, colour) {
  const first = colour?.image || product.image;
  return [first, ...product.gallery.filter((k) => k !== product.image && k !== first)];
}

function relatedFor(product) {
  const picked = product.related ? getProductsBySlugs(product.related) : [];
  const fill = getProductsByCategory(product.category).filter((p) => p.id !== product.id && !picked.includes(p));
  return [...picked, ...fill].slice(0, 4);
}

function scrollToEl(el) {
  if (!el) return;
  const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
}

/* ---------------------------------------------------------------- view */

function ProductView({ product }) {
  useDocumentTitle(product.name);
  const navigate = useNavigate();
  const isPhone = useMediaQuery(PHONE_QUERY);
  const { addItem, items } = useCart();
  const wishlist = useWishlist();

  const [variant] = useState(() => initialVariant(product));
  const [colourName, setColourName] = useState(variant.colour);
  const [sizeChoice, setSizeChoice] = useState(variant.size);
  const [qtyChoice, setQtyChoice] = useState(1);
  const [slide, setSlide] = useState(0);
  const [tab, setTab] = useState(product.sizeChart ? 'size-chart' : 'description');
  const [panels, setPanels] = useState(() => (product.sizeChart ? { 'size-chart': true } : { description: true }));
  const [compare, setCompare] = useState(false);
  const [sizeError, setSizeError] = useState('');
  const [toast, setToast] = useState(null);

  const sizesRef = useRef(null);
  const infoRef = useRef(null);
  const reviewsRef = useRef(null);

  // ---- derived variant state
  const colour = product.colours.find((c) => c.name === colourName) || product.colours[0];
  const stockOf = (label) => getStock(product, colour.name, label);
  const size = sizeChoice && stockOf(sizeChoice) > 0 ? sizeChoice : null;
  const stock = size ? stockOf(size) : 0;
  const inCart = items.find((i) => i.productId === product.id && i.size === size && i.colour === colour.name)?.qty ?? 0;
  const available = Math.max(0, stock - inCart);
  const maxQty = Math.max(1, stock);
  const qty = Math.min(qtyChoice, maxQty);
  const colourSoldOut = colourStock(product, colour.name) === 0;

  const images = useMemo(() => galleryFor(product, colour), [product, colour]);
  const index = Math.min(slide, images.length - 1);
  const tone = index === 0 ? colour.tone || product.tone : product.tone;
  const photoAlt = (i) => `${product.name} in ${colour.name}${i > 0 ? `, photo ${i + 1}` : ''}`;
  const related = useMemo(() => relatedFor(product), [product]);

  const category = getCategory(product.category);
  const parent = category ? getParent(category.parent) : null;
  const crumbs = [{ label: 'Home', to: '/' }];
  if (parent && parent.slug !== category.slug) crumbs.push({ label: parent.name, to: `/shop/${parent.slug}` });
  if (category) crumbs.push({ label: category.name, to: `/shop/${category.slug}` });
  crumbs.push({ label: product.name });

  const onSale = isOnSale(product);
  const off = getDiscountPercent(product);
  const wished = wishlist.has(product.id);

  // ---- stock line
  let stockText = 'Please select a size';
  let stockTone = 'muted';
  if (product.soldOut) {
    stockText = 'Out of stock in all sizes';
    stockTone = 'out';
  } else if (colourSoldOut) {
    stockText = `Out of stock in ${colour.name} — try another colour`;
    stockTone = 'out';
  } else if (size && stock <= LOW_STOCK) {
    stockText = `Low stock: only ${stock} left in ${size}`;
    stockTone = 'low';
  } else if (size) {
    stockText = `In stock (${stock} available in ${size})`;
    stockTone = 'ok';
  }

  // ---- actions
  const closeToast = useCallback(() => setToast(null), []);

  const pickColour = (name) => {
    setColourName(name);
    setSlide(0);
  };

  const pickSize = (label) => {
    setSizeChoice(label);
    setQtyChoice(1);
    setSizeError('');
  };

  /** Adds the current selection. Returns true when the shopper can continue to checkout. */
  const addToCart = () => {
    if (product.soldOut || colourSoldOut) return false;
    if (!size) {
      setSizeError('Please select a size first.');
      sizesRef.current?.querySelector('button:not(:disabled)')?.focus();
      return false;
    }
    if (available <= 0) {
      setToast({
        id: Date.now(),
        tone: 'info',
        title: `You already have all ${stock} in your cart`,
        detail: `${colour.name} · Size ${size}`,
        cartLink: true,
      });
      return true;
    }
    const addQty = Math.min(qty, available);
    const result = addItem({ productId: product.id, colour: colour.name, size, qty: addQty });
    if (!result.ok) {
      setToast({ id: Date.now(), tone: 'error', title: result.message });
      return false;
    }
    setToast({
      id: Date.now(),
      tone: 'success',
      title: 'Added to your cart',
      detail: `${product.name} · ${colour.name} · Size ${size} × ${addQty}${addQty < qty ? ` (only ${available} more available)` : ''}`,
      cartLink: true,
    });
    return true;
  };

  const buyNow = () => {
    if (addToCart()) navigate('/checkout');
  };

  const showSection = (id) => {
    if (isPhone) {
      if (id === 'reviews') {
        scrollToEl(reviewsRef.current);
        return;
      }
      setPanels((p) => ({ ...p, [id]: true }));
      requestAnimationFrame(() => scrollToEl(infoRef.current?.querySelector(`[data-section="${id}"]`)));
      return;
    }
    setTab(id);
    requestAnimationFrame(() => scrollToEl(infoRef.current));
  };

  const share = async () => {
    const url = window.location.href;
    if (navigator.share) {
      try {
        await navigator.share({ title: product.name, url });
      } catch {
        // Share sheet dismissed
      }
      return;
    }
    try {
      await navigator.clipboard.writeText(url);
      setToast({ id: Date.now(), tone: 'info', title: 'Link copied to clipboard' });
    } catch {
      setToast({ id: Date.now(), tone: 'info', title: 'Copy this link to share', detail: url });
    }
  };

  const goBack = () => {
    if (window.history.state?.idx > 0) navigate(-1);
    else navigate(category ? `/shop/${category.slug}` : '/shop');
  };

  const badge = product.soldOut ? (
    <span className="pdp-badge pdp-badge--dark">Sold out</span>
  ) : (
    off > 0 && (
      <span className="pdp-badge">
        <span aria-hidden="true">-{off}%</span>
        <span className="visually-hidden">{off}% off</span>
      </span>
    )
  );

  const sizeGuide = product.sizeChart ? (
    <a
      href="#size-chart"
      className="pdp-size-guide"
      onClick={(e) => {
        e.preventDefault();
        showSection('size-chart');
      }}
    >
      <Icon name="ruler" size={18} />
      Size guide
    </a>
  ) : (
    <Link to="/pages/size-guide" className="pdp-size-guide">
      <Icon name="ruler" size={18} />
      Size guide
    </Link>
  );

  const addDisabled = product.soldOut || colourSoldOut;

  return (
    <div className={`pdp${isPhone ? ' pdp--phone' : ''}`}>
      {!isPhone && (
        <div className="container pdp__crumbs">
          <Breadcrumbs items={crumbs} />
        </div>
      )}

      <section className="container pdp__main" aria-label={product.name}>
        {isPhone ? (
          <MobileGallery
            product={product}
            images={images}
            index={index}
            onSelect={setSlide}
            tone={tone}
            alt={photoAlt}
            badge={badge}
            onBack={goBack}
            onShare={share}
          />
        ) : (
          <DesktopGallery images={images} index={index} onSelect={setSlide} tone={tone} alt={photoAlt} badge={badge} />
        )}

        <div className="pdp-buy">
          <div className="pdp-buy__head">
            <p className="pdp-buy__meta">
              {getCategoryLabel(product)} · {getBrand(product).name}
            </p>
            <h1 className="pdp-buy__title">{product.name}</h1>
            <div className="pdp-buy__rating">
              <span className="pdp-buy__stars">
                <span aria-hidden="true">★ </span>
                <span className="visually-hidden">Rated </span>
                {product.rating.toFixed(1)}
                <span className="visually-hidden"> out of 5</span>
              </span>
              <a
                href="#reviews"
                onClick={(e) => {
                  e.preventDefault();
                  showSection('reviews');
                }}
              >
                {product.reviewsCount} reviews
              </a>
              <span>SKU: {getVariantSku(product, colour.name, size)}</span>
            </div>
          </div>

          <div className="pdp-buy__price">
            <span className="pdp-buy__now">
              {onSale && <span className="visually-hidden">Sale price </span>}
              {formatBDT(product.price)}
            </span>
            {onSale && (
              <>
                <span className="pdp-buy__old">
                  <span className="visually-hidden">Regular price </span>
                  {formatBDT(product.oldPrice)}
                </span>
                <span className="pdp-buy__save">Save {formatBDT(product.oldPrice - product.price)}</span>
              </>
            )}
          </div>

          <fieldset className="pdp-option">
            <legend className="pdp-option__legend">
              Colour: <span className="pdp-option__value">{colour.name}</span>
            </legend>
            <div className="pdp-colours">
              {product.colours.map((c) => (
                <button
                  key={c.name}
                  type="button"
                  className={`pdp-colour${c.name === colour.name ? ' is-active' : ''}`}
                  aria-label={colourStock(product, c.name) === 0 ? `${c.name} (sold out)` : c.name}
                  aria-pressed={c.name === colour.name}
                  onClick={() => pickColour(c.name)}
                >
                  <span style={{ background: c.hex }} />
                </button>
              ))}
            </div>
          </fieldset>

          <fieldset className="pdp-option">
            <legend className="pdp-option__legend pdp-option__legend--row">
              <span>
                Size: <span className="pdp-option__value">{size || 'Select'}</span>
              </span>
              {sizeGuide}
            </legend>
            <div className="pdp-sizes" ref={sizesRef}>
              {product.sizes.map((s) => {
                const n = stockOf(s.label);
                const out = n === 0;
                const selected = s.label === size;
                let sub = 'In stock';
                if (out) sub = 'Sold out';
                else if (n <= LOW_STOCK) sub = `${n} left`;
                return (
                  <button
                    key={s.label}
                    type="button"
                    className={`pdp-size${selected ? ' is-active' : ''}${!out && n <= LOW_STOCK ? ' is-low' : ''}`}
                    aria-pressed={selected}
                    aria-label={`${s.label}, ${out ? 'sold out' : `${n} in stock`}`}
                    disabled={out}
                    onClick={() => pickSize(s.label)}
                  >
                    <span className="pdp-size__label">{s.label}</span>
                    <span className="pdp-size__sub" aria-hidden="true">
                      {sub}
                    </span>
                  </button>
                );
              })}
            </div>
            <p className={`pdp-stock pdp-stock--${stockTone}`} aria-live="polite">
              {stockText}
            </p>
            {sizeError && (
              <p className="error-text" role="alert">
                {sizeError}
              </p>
            )}
          </fieldset>

          {!isPhone && (
            <>
              <div className="pdp-actions">
                <QtyStepper value={qty} onChange={setQtyChoice} max={maxQty} label={product.name} className="pdp-actions__qty" />
                <button type="button" className="btn btn--primary btn--lg pdp-actions__btn" onClick={addToCart} disabled={addDisabled}>
                  {addDisabled ? 'Sold out' : 'Add to cart'}
                </button>
                <button type="button" className="btn btn--brand btn--lg pdp-actions__btn" onClick={buyNow} disabled={addDisabled}>
                  Buy now
                </button>
              </div>
              <div className="pdp-links">
                <button type="button" className="pdp-links__btn" aria-pressed={wished} onClick={() => wishlist.toggle(product.id)}>
                  <Icon name="heart" size={18} filled={wished} className={wished ? 'text-sale' : undefined} />
                  {wished ? 'Saved to wishlist' : 'Add to wishlist'}
                </button>
                <button type="button" className="pdp-links__btn" aria-pressed={compare} onClick={() => setCompare((v) => !v)}>
                  <Icon name={compare ? 'check' : 'exchange'} size={18} />
                  {compare ? 'Added to compare' : 'Compare'}
                </button>
              </div>
            </>
          )}

          <DeliveryInfo phone={isPhone} />
        </div>
      </section>

      {isPhone ? (
        <>
          <InfoAccordions
            product={product}
            open={panels}
            onToggle={(id) => setPanels((p) => ({ ...p, [id]: !p[id] }))}
            selectedSize={size}
            sectionRef={infoRef}
          />
          <section id="reviews" ref={reviewsRef} className="pdp-mreviews" aria-labelledby="pdp-reviews-title">
            <Reviews product={product} compact headingId="pdp-reviews-title" />
          </section>
        </>
      ) : (
        <InfoTabs product={product} tab={tab} onTab={setTab} selectedSize={size} sectionRef={infoRef} />
      )}

      {related.length > 0 && <Related products={related} />}

      {isPhone && (
        <div className="pdp-buybar" role="region" aria-label="Buy">
          <QtyStepper value={qty} onChange={setQtyChoice} max={maxQty} label={product.name} size="sm" className="pdp-buybar__qty" />
          <button type="button" className="btn btn--primary pdp-buybar__btn" onClick={addToCart} disabled={addDisabled}>
            {addDisabled ? 'Sold out' : 'Add to cart'}
          </button>
          <button type="button" className="btn btn--brand pdp-buybar__btn" onClick={buyNow} disabled={addDisabled}>
            Buy now
          </button>
        </div>
      )}

      <CartToast toast={toast} onClose={closeToast} />
    </div>
  );
}

/* ------------------------------------------------------------- pieces */

function DeliveryInfo({ phone }) {
  const [inside, outside] = delivery.zones;
  const rows = phone
    ? [
        { icon: 'truck', label: inside.name, value: `${formatBDT(inside.fee)} · ${inside.etaShort}` },
        { icon: 'map-pin', label: outside.name, value: `${formatBDT(outside.fee)} · ${outside.etaShort}` },
        { icon: 'cash', label: 'Cash on Delivery', value: 'Available' },
        { icon: 'exchange', label: '7-day easy exchange', value: 'Size or colour', soft: true },
      ]
    : [
        { label: inside.name, value: `${formatBDT(inside.fee)} · ${inside.etaShort}` },
        { label: outside.name, value: `${formatBDT(outside.fee)} · ${outside.etaShort}` },
        { label: 'Cash on Delivery available', value: '7-day exchange', soft: true },
      ];
  return (
    <ul className="pdp-delivery" role="list" aria-label="Delivery and returns">
      {rows.map((r) => (
        <li key={r.label} className={`pdp-delivery__row${r.soft ? ' is-soft' : ''}`}>
          {r.icon && <Icon name={r.icon} size={22} strokeWidth={1.6} className="pdp-delivery__icon" />}
          <span className="pdp-delivery__label">{r.label}</span>
          <span className="pdp-delivery__value">{r.value}</span>
        </li>
      ))}
    </ul>
  );
}

function Related({ products }) {
  return (
    <section className="container pdp-related" aria-labelledby="pdp-related-title">
      <h2 id="pdp-related-title" className="pdp-section-title">
        You may also like
      </h2>
      <ul className="pdp-related__list" role="list">
        {products.map((p) => (
          <li key={p.id}>
            <Link to={`/product/${p.slug}`} className="pdp-related__card">
              <span className="pdp-related__media media" style={{ background: p.tone }}>
                <img src={imageUrl(p.image)} alt={p.name} loading="lazy" decoding="async" />
              </span>
              <span className="pdp-related__name">{p.name}</span>
              <span className="pdp-related__price">{formatBDT(p.price)}</span>
            </Link>
          </li>
        ))}
      </ul>
    </section>
  );
}
