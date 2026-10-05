import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCart } from '../../context/CartContext.jsx';
import { getCategoryLabel, getDefaultVariant, getDiscountPercent } from '../../data/products.js';
import { formatBDT, imageUrl } from '../../utils/format.js';
import Icon from './Icon.jsx';
import Price from './Price.jsx';
import WishlistButton from './WishlistButton.jsx';
import './ProductCard.css';

/**
 * Product card in three variants:
 *
 *  variant="grid"      New-arrivals style: 3:4 photo, tag pill, wishlist heart,
 *                      category label, name, price, colour dots + sizes.
 *  variant="carousel"  Category-row style: square photo, wishlist (left),
 *                      "Save x%" / "Sold out" badge, BUY NOW + ADD TO CART
 *                      (adds the first in-stock size/colour to the cart).
 *  variant="flash"     Flash-sale style: whole card is a link, -x% badge,
 *                      flash price, stock progress bar.
 *
 * Props: product (from data/products.js), variant, loading ('lazy'|'eager'),
 *        showSizes (grid only, default true), className.
 */
export default function ProductCard({ product, variant = 'grid', loading = 'lazy', showSizes = true, className = '' }) {
  if (variant === 'carousel') return <CarouselCard product={product} loading={loading} className={className} />;
  if (variant === 'flash') return <FlashCard product={product} loading={loading} className={className} />;
  return <GridCard product={product} loading={loading} showSizes={showSizes} className={className} />;
}

const productUrl = (p) => `/product/${p.slug}`;

function Photo({ product, loading, dim = false }) {
  return (
    <img
      src={imageUrl(product.image)}
      alt={product.name}
      loading={loading}
      decoding="async"
      className={dim ? 'is-dimmed' : undefined}
    />
  );
}

function tagFor(product) {
  if (product.soldOut) return { label: 'Sold out', className: 'badge--dark' };
  if (product.tags.includes('new')) return { label: 'New', className: '' };
  if (product.tags.includes('bestseller')) return { label: 'Best seller', className: '' };
  return null;
}

/* ------------------------------------------------------------------ grid */
function GridCard({ product, loading, showSizes, className }) {
  const tag = tagFor(product);
  return (
    <article className={`product-card product-card--grid ${className}`.trim()}>
      <div className="product-card__media media" style={{ background: product.tone }}>
        <Link to={productUrl(product)} tabIndex={-1} aria-hidden="true" className="product-card__media-link">
          <Photo product={product} loading={loading} />
        </Link>
        {tag && <span className={`badge product-card__tag ${tag.className}`}>{tag.label}</span>}
        <WishlistButton product={product} className="product-card__wish" />
      </div>
      <div className="product-card__body">
        <div className="product-card__cat">{getCategoryLabel(product)}</div>
        <Link to={productUrl(product)} className="product-card__name">
          {product.name}
        </Link>
        <Price price={product.price} oldPrice={product.oldPrice} saleColour={false} className="product-card__price" />
        <div className="product-card__meta">
          <span className="product-card__swatches" aria-label={`${product.colours.length} colours: ${product.colours.map((c) => c.name).join(', ')}`} role="img">
            {product.colours.map((c) => (
              <span key={c.name} className="swatch" style={{ background: c.hex }} />
            ))}
          </span>
          {showSizes && (
            <span className="product-card__sizes">
              <span className="visually-hidden">Sizes </span>
              {product.sizes.map((s) => s.label).join(' ')}
            </span>
          )}
        </div>
      </div>
    </article>
  );
}

/* -------------------------------------------------------------- carousel */
function CarouselCard({ product, loading, className }) {
  const { addItem } = useCart();
  const navigate = useNavigate();
  const [added, setAdded] = useState(false);
  const [error, setError] = useState('');
  const off = getDiscountPercent(product);

  const add = () => {
    const variant = getDefaultVariant(product);
    const result = addItem({ productId: product.id, size: variant.size, colour: variant.colour, qty: 1 });
    if (result.ok) {
      setAdded(true);
      setError('');
    } else {
      setError(result.message);
    }
    return result.ok;
  };

  const buyNow = () => {
    if (add()) navigate('/checkout');
  };

  // Brief "ADDED" confirmation, then back to normal so the button can be used again.
  useEffect(() => {
    if (!added) return undefined;
    const id = setTimeout(() => setAdded(false), 2500);
    return () => clearTimeout(id);
  }, [added]);

  return (
    <article className={`product-card product-card--carousel${product.soldOut ? ' is-sold-out' : ''} ${className}`.trim()}>
      <div className="product-card__media media" style={{ background: product.tone }}>
        <Link to={productUrl(product)} tabIndex={-1} aria-hidden="true" className="product-card__media-link">
          <Photo product={product} loading={loading} dim={product.soldOut} />
        </Link>
        <WishlistButton product={product} size="sm" className="product-card__wish product-card__wish--left" />
        {product.soldOut ? (
          <span className="badge badge--dark product-card__corner">Sold out</span>
        ) : (
          off > 0 && <span className="badge badge--sale product-card__corner">Save {off}%</span>
        )}
      </div>
      <div className="product-card__body">
        <Link to={productUrl(product)} className="product-card__name">
          {product.name}
        </Link>
        <Price price={product.price} oldPrice={product.oldPrice} className="product-card__price product-card__price--strong" />
        {product.soldOut ? (
          <button type="button" className="btn btn--caps btn--sale product-card__actions-single" disabled>
            Sold out
          </button>
        ) : (
          <div className="product-card__actions">
            <button type="button" className="btn btn--caps btn--primary" onClick={buyNow}>
              Buy now
            </button>
            <button
              type="button"
              className={`btn btn--caps ${added ? 'btn--brand' : 'btn--outline'}`}
              onClick={add}
              aria-label={added ? `${product.name} added to cart` : `Add ${product.name} to cart`}
            >
              {added ? (
                <>
                  Added <Icon name="check" size={14} strokeWidth={2.4} />
                </>
              ) : (
                'Add to cart'
              )}
            </button>
          </div>
        )}
        {error && (
          <p className="product-card__error" role="alert">
            {error}
          </p>
        )}
      </div>
    </article>
  );
}

/* ----------------------------------------------------------------- flash */
function FlashCard({ product, loading, className }) {
  const flash = product.flashSale || { price: product.price, compareAt: product.oldPrice, soldPct: 0, left: 0 };
  const off = flash.compareAt ? Math.round((1 - flash.price / flash.compareAt) * 100) : 0;
  const leftText = flash.left <= 10 ? `Only ${flash.left} left` : `${flash.left} left`;
  return (
    <Link to={productUrl(product)} className={`product-card product-card--flash ${className}`.trim()}>
      <div className="product-card__media media" style={{ background: product.tone }}>
        <Photo product={product} loading={loading} />
        {off > 0 && (
          <span className="badge badge--sale product-card__tag">
            <span className="visually-hidden">Discount </span>-{off}%
          </span>
        )}
      </div>
      <div className="product-card__body">
        <span className="product-card__name">{product.name}</span>
        <span className="price price--sale product-card__price">
          <span className="price__now">{formatBDT(flash.price)}</span>
          {flash.compareAt && (
            <span className="price__old">
              <span className="visually-hidden">Regular price </span>
              {formatBDT(flash.compareAt)}
            </span>
          )}
        </span>
        <span
          className="product-card__stock-bar"
          role="progressbar"
          aria-label="Claimed"
          aria-valuemin={0}
          aria-valuemax={100}
          aria-valuenow={flash.soldPct}
        >
          <span style={{ width: `${flash.soldPct}%` }} />
        </span>
        <span className="product-card__left">{leftText}</span>
      </div>
    </Link>
  );
}
