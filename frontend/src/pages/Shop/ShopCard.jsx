import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import Price from '../../components/ui/Price.jsx';
import WishlistButton from '../../components/ui/WishlistButton.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { getDefaultVariant, getDiscountPercent, getTotalStock } from '../../data/products.js';
import { imageUrl } from '../../utils/format.js';

/** Image badge: out of stock › sale % › new › best seller. */
function badgeFor(product) {
  if (product.soldOut) return { label: 'Out of stock', tone: 'oos' };
  const off = getDiscountPercent(product);
  if (off > 0) return { label: `-${off}%`, tone: 'sale', sr: `${off}% off` };
  if (product.tags.includes('new')) return { label: 'New', tone: 'plain' };
  if (product.tags.includes('bestseller')) return { label: 'Best seller', tone: 'plain' };
  return null;
}

function stockFor(product) {
  const n = getTotalStock(product);
  if (n === 0) return { label: 'Out of stock', tone: 'out' };
  if (n <= 3) return { label: `Only ${n} left`, tone: 'low' };
  return { label: 'In stock', tone: 'ok' };
}

/**
 * Shop listing card (Shop.dc.html / M-Shop.dc.html): 3:4 photo with badge,
 * wishlist and a quick "Add to cart" / "Notify me" button (desktop), fabric,
 * name, price, colour dots (desktop) or rating (phone) and stock status.
 */
export default function ShopCard({ product, loading = 'lazy' }) {
  const { addItem } = useCart();
  const [status, setStatus] = useState(null); // 'added' | 'notify' | { error }
  const badge = badgeFor(product);
  const stock = stockFor(product);
  const url = `/product/${product.slug}`;

  useEffect(() => {
    if (status !== 'added') return undefined;
    const id = setTimeout(() => setStatus(null), 2500);
    return () => clearTimeout(id);
  }, [status]);

  const quickAdd = () => {
    if (product.soldOut) {
      setStatus((s) => (s === 'notify' ? null : 'notify'));
      return;
    }
    const v = getDefaultVariant(product);
    const result = addItem({ productId: product.id, colour: v.colour, size: v.size, qty: 1 });
    setStatus(result.ok ? 'added' : { error: result.message });
  };

  let ctaText = 'Add to cart';
  let ctaLabel = `Quick add ${product.name} to cart`;
  if (product.soldOut) {
    ctaText = status === 'notify' ? 'We’ll notify you' : 'Notify me';
    ctaLabel = `Notify me when ${product.name} is back`;
  } else if (status === 'added') {
    ctaText = 'Added';
    ctaLabel = `${product.name} added to cart`;
  }

  return (
    <article className="shop-card">
      <div className="shop-card__media media" style={{ background: product.tone }}>
        <Link to={url} tabIndex={-1} aria-hidden="true" className="shop-card__media-link">
          <img src={imageUrl(product.image)} alt={product.name} loading={loading} decoding="async" />
        </Link>
        {badge && (
          <span className={`shop-card__badge shop-card__badge--${badge.tone}`}>
            {badge.sr ? (
              <>
                <span aria-hidden="true">{badge.label}</span>
                <span className="visually-hidden">{badge.sr}</span>
              </>
            ) : (
              badge.label
            )}
          </span>
        )}
        <WishlistButton product={product} className="shop-card__wish" />
        <button
          type="button"
          className={`shop-card__quick${product.soldOut ? ' shop-card__quick--notify' : ''}${status === 'added' ? ' is-added' : ''}`}
          aria-label={ctaLabel}
          aria-pressed={product.soldOut ? status === 'notify' : undefined}
          onClick={quickAdd}
        >
          <Icon name={status === 'added' || status === 'notify' ? 'check' : product.soldOut ? 'bell' : 'bag'} size={16} strokeWidth={2} />
          {ctaText}
        </button>
      </div>

      <div className="shop-card__body">
        <div className="shop-card__fabric">{product.fabric}</div>
        <Link to={url} className="shop-card__name">
          {product.name}
        </Link>
        <Price price={product.price} oldPrice={product.oldPrice} className="shop-card__price" />
        <div className="shop-card__meta">
          <span
            className="shop-card__swatches"
            role="img"
            aria-label={`${product.colours.length} ${product.colours.length === 1 ? 'colour' : 'colours'} available`}
          >
            {product.colours.map((c) => (
              <span key={c.name} className="swatch" style={{ background: c.hex }} />
            ))}
          </span>
          <span className="shop-card__rating">
            <span className="shop-card__stars">
              <span aria-hidden="true">★ </span>
              <span className="visually-hidden">Rated </span>
              {product.rating.toFixed(1)}
            </span>{' '}
            ({product.reviewsCount}
            <span className="visually-hidden"> reviews</span>)
          </span>
          <span className={`shop-card__stock shop-card__stock--${stock.tone}`}>{stock.label}</span>
        </div>
        <p className="visually-hidden" role="status">
          {status === 'added' ? `${product.name} added to your cart.` : ''}
          {status === 'notify' ? `We’ll let you know when ${product.name} is back in stock.` : ''}
        </p>
        {status?.error && (
          <p className="shop-card__error" role="alert">
            {status.error}
          </p>
        )}
      </div>
    </article>
  );
}
