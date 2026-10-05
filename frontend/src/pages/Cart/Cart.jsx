import { useEffect, useMemo, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import WishlistButton from '../../components/ui/WishlistButton.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { useWishlist } from '../../context/WishlistContext.jsx';
import { cartRelatedFallbackIds, cartRelatedIds } from '../../data/checkout.js';
import { delivery } from '../../data/content.js';
import { getCategoryLabel, getDefaultVariant, getProductsByIds } from '../../data/products.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { formatBDT, formatDiscount, imageUrl, pluralize } from '../../utils/format.js';
import CheckoutSteps from '../Checkout/CheckoutSteps.jsx';
import OrderTotals from '../Checkout/OrderTotals.jsx';
import CartLine from './CartLine.jsx';
import CouponForm from './CouponForm.jsx';
import './Cart.css';

/**
 * Cart — Cart.dc.html (desktop) / M-Cart.dc.html (≤ 768px).
 * Lines, quantities, variants, coupon and totals all come from CartContext.
 * Remove / move to wishlist show an Undo notice.
 */
export default function Cart() {
  useDocumentTitle('Your cart');
  const cart = useCart();
  const wishlist = useWishlist();
  const { lines, count, isEmpty } = cart;
  // Last removal, for Undo: { text, removed, wishAdded }
  const [notice, setNotice] = useState(null);
  const undoRef = useRef(null);
  const itemsRef = useRef(null);

  // Removing a line removes the focused button too: move focus to Undo.
  useEffect(() => {
    if (notice) undoRef.current?.focus();
  }, [notice]);

  const handleRemove = (line) => {
    const removed = cart.removeItem(line.key);
    if (removed) setNotice({ text: `${line.name} removed from your cart.`, removed, wishAdded: null });
  };

  const handleMoveToWishlist = (line) => {
    const alreadyWished = wishlist.has(line.productId);
    const removed = cart.removeItem(line.key);
    if (!removed) return;
    if (!alreadyWished) wishlist.add(line.productId);
    setNotice({
      text: `${line.name} moved to your wishlist.`,
      removed,
      wishAdded: alreadyWished ? null : line.productId,
    });
  };

  const undo = () => {
    if (!notice) return;
    cart.restoreItem(notice.removed);
    if (notice.wishAdded) wishlist.remove(notice.wishAdded);
    setNotice(null);
    itemsRef.current?.focus();
  };

  return (
    <div className="cart-page">
      <header className="cart-head">
        <div className="cart-head__inner container">
          <Link to="/" className="icon-btn cart-head__icon only-mobile" aria-label="Back to home">
            <Icon name="chevron-left" size={22} strokeWidth={2} />
          </Link>
          <div className="cart-head__titles">
            <h1 className="cart-head__title">
              <span className="only-desktop">Your cart</span>
              <span className="only-mobile">My cart</span>
            </h1>
            <span className="cart-head__count" aria-live="polite">
              {pluralize(count, 'item')}
            </span>
          </div>
          <CheckoutSteps current={0} className="cart-head__steps only-desktop" />
          <Link to="/account?tab=wishlist" className="icon-btn cart-head__icon only-mobile" aria-label={`Wishlist, ${pluralize(wishlist.count, 'item')}`}>
            <Icon name="heart" size={22} />
          </Link>
        </div>
      </header>

      <div className={`cart-layout container${isEmpty ? ' cart-layout--empty' : ''}`}>
        <div className="cart-main">
          {!isEmpty && <FreeDelivery />}

          {notice && (
            <div className="cart-notice" role="status">
              <span>{notice.text}</span>
              <button type="button" ref={undoRef} className="cart-notice__undo" onClick={undo}>
                Undo
              </button>
            </div>
          )}

          <section className="cart-items" aria-label="Cart items" ref={itemsRef} tabIndex={-1}>
            {isEmpty ? (
              <EmptyCart />
            ) : (
              <>
                <div className="cart-items__head only-desktop" aria-hidden="true">
                  <span>Product</span>
                  <span>Quantity</span>
                  <span>Total</span>
                </div>
                <ul role="list" className="cart-items__list">
                  {lines.map((line) => (
                    <CartLine key={line.key} line={line} onRemove={handleRemove} onMoveToWishlist={handleMoveToWishlist} />
                  ))}
                </ul>
              </>
            )}
            <div className="cart-items__foot only-desktop">
              <Link to="/shop" className="cart-items__continue">
                <Icon name="chevron-left" size={18} strokeWidth={2} />
                Continue shopping
              </Link>
              <span className="cart-items__note">Prices include VAT. Items in your cart are not reserved.</span>
            </div>
          </section>
        </div>

        {!isEmpty && (
          <aside className="cart-side" aria-label="Coupon and order summary">
            <section className="cart-card">
              <CouponForm id="cart-coupon" title="Coupon code" showAvailable />
            </section>
            <CartSummary />
          </aside>
        )}
      </div>

      <RelatedProducts />
    </div>
  );
}

/* ------------------------------------------------------- free delivery */
function FreeDelivery() {
  const { subtotal, isFreeDelivery, freeDeliveryRemaining, freeDeliveryProgress, freeDeliveryThreshold, coupon } = useCart();
  const blockedByCoupon = !isFreeDelivery && freeDeliveryRemaining === 0;

  let message;
  if (isFreeDelivery) {
    message = (
      <>
        You have unlocked <strong>free delivery</strong> on this order.
      </>
    );
  } else if (blockedByCoupon) {
    message = (
      <>
        Free delivery over {formatBDT(freeDeliveryThreshold)} can’t be combined with coupon <strong>{coupon?.code}</strong>.
      </>
    );
  } else {
    message = (
      <>
        Add <strong>{formatBDT(freeDeliveryRemaining)} more</strong> to get free delivery.
      </>
    );
  }

  return (
    <div className="cart-card cart-free">
      <p className="cart-free__text">
        <Icon name="truck" size={24} strokeWidth={1.7} className="cart-free__icon" />
        <span aria-live="polite">{message}</span>
      </p>
      <div
        className="cart-free__bar"
        role="progressbar"
        aria-label="Progress to free delivery"
        aria-valuemin={0}
        aria-valuemax={freeDeliveryThreshold}
        aria-valuenow={Math.min(subtotal, freeDeliveryThreshold)}
        aria-valuetext={`${formatBDT(subtotal)} of ${formatBDT(freeDeliveryThreshold)}`}
      >
        <span style={{ width: `${freeDeliveryProgress}%` }} />
      </div>
      <div className="cart-free__scale">
        <span className="only-desktop">৳0</span>
        <span className="only-mobile">{formatBDT(subtotal)}</span>
        <span>Free delivery at {formatBDT(freeDeliveryThreshold)}</span>
      </div>
    </div>
  );
}

/* ---------------------------------------------------------- empty cart */
function EmptyCart() {
  return (
    <div className="cart-empty">
      <Icon name="bag" size={48} strokeWidth={1.4} className="cart-empty__icon" />
      <h2 className="cart-empty__title">Your cart is empty</h2>
      <p className="cart-empty__text">Items you add will appear here.</p>
      <Link to="/shop" className="btn btn--primary btn--lg">
        Start shopping
      </Link>
    </div>
  );
}

/* ------------------------------------------------------------- summary */
function CartSummary() {
  const { count, subtotal, saleSavings, coupon, discount, zone, deliveryFee, isFreeDelivery, total } = useCart();
  const otherZones = delivery.zones.filter((z) => z.id !== zone.id);
  const zoneNote = isFreeDelivery
    ? `Free delivery on orders over ${formatBDT(delivery.freeDeliveryThreshold)}.`
    : `${otherZones.map((z) => `${z.name} ${formatBDT(z.fee)}`).join(' · ')} · choose your area at checkout`;

  return (
    <section className="cart-card cart-summary" aria-labelledby="cart-summary-title">
      {/* On phones only this box is a card; the actions sit below it (M-Cart). */}
      <div className="cart-summary__box">
        <h2 id="cart-summary-title" className="cart-summary__title">
          <span className="only-desktop">Order summary</span>
          <span className="only-mobile">Summary</span>
        </h2>
        <OrderTotals
          rows={[
            { label: `Subtotal (${pluralize(count, 'item')})`, value: formatBDT(subtotal) },
            saleSavings > 0 && { label: 'You save on sale items', value: formatDiscount(saleSavings), tone: 'sale' },
            coupon?.valid && { label: `Coupon (${coupon.code})`, value: formatDiscount(discount), tone: 'brand' },
            {
              label: `Delivery (${zone.name})`,
              value: isFreeDelivery ? 'Free' : formatBDT(deliveryFee),
              tone: isFreeDelivery ? 'free' : undefined,
              note: zoneNote,
            },
          ]}
          total={formatBDT(total)}
        />
      </div>

      <div className="cart-summary__actions">
        <Link to="/checkout" className="btn btn--brand btn--block cart-summary__checkout">
          <span className="only-desktop">Proceed to checkout</span>
          <span className="only-mobile">Checkout · {formatBDT(total)}</span>
          <Icon name="arrow-right" size={18} strokeWidth={2.2} />
        </Link>
        <p className="cart-summary__trust-line only-mobile">
          <Icon name="shield" size={15} strokeWidth={2} />
          Cash on Delivery · 7-day easy exchange
        </p>
        <Link to="/shop" className="btn btn--outline btn--block cart-summary__continue">
          Continue shopping
        </Link>
      </div>

      <ul role="list" className="cart-summary__trust only-desktop">
        <li>
          <Icon name="cash" size={18} />
          Cash on Delivery available
        </li>
        <li>
          <Icon name="exchange" size={18} />
          7-day easy size &amp; colour exchange
        </li>
        <li>
          <Icon name="shield" size={18} />
          Secure checkout
        </li>
      </ul>
    </section>
  );
}

/* ------------------------------------------------------ you may also like */
function RelatedProducts() {
  const { items } = useCart();
  const products = useMemo(() => {
    const inCart = new Set(items.map((i) => i.productId));
    return getProductsByIds([...cartRelatedIds, ...cartRelatedFallbackIds])
      .filter((p) => !p.soldOut && !inCart.has(p.id))
      .slice(0, 4);
  }, [items]);

  if (products.length === 0) return null;

  return (
    <section className="cart-related" aria-labelledby="cart-related-title">
      <div className="container">
        <div className="cart-related__head">
          <h2 id="cart-related-title" className="cart-related__title">
            You may also like
          </h2>
          <Link to="/shop" className="link-arrow">
            View all →
          </Link>
        </div>
        <ul role="list" className="cart-related__grid">
          {products.map((p) => (
            <li key={p.id}>
              <RelatedCard product={p} />
            </li>
          ))}
        </ul>
      </div>
    </section>
  );
}

function RelatedCard({ product }) {
  const { addItem } = useCart();
  const [added, setAdded] = useState(false);
  const [error, setError] = useState('');
  const url = `/product/${product.slug}`;

  useEffect(() => {
    if (!added) return undefined;
    const id = setTimeout(() => setAdded(false), 2500);
    return () => clearTimeout(id);
  }, [added]);

  const add = () => {
    const variant = getDefaultVariant(product);
    const result = addItem({ productId: product.id, size: variant.size, colour: variant.colour, qty: 1 });
    setAdded(result.ok);
    setError(result.ok ? '' : result.message);
  };

  return (
    <article className="related-card">
      <div className="related-card__media media" style={{ background: product.tone }}>
        <Link to={url} tabIndex={-1} aria-hidden="true" className="related-card__media-link">
          <img src={imageUrl(product.image)} alt={product.name} loading="lazy" decoding="async" />
        </Link>
        <WishlistButton product={product} className="related-card__wish" />
      </div>
      <div className="related-card__cat">{getCategoryLabel(product)}</div>
      <Link to={url} className="related-card__name">
        {product.name}
      </Link>
      <div className="related-card__row">
        <span className="related-card__price">{formatBDT(product.price)}</span>
        <button
          type="button"
          className={`related-card__add${added ? ' is-added' : ''}`}
          onClick={add}
          aria-label={added ? `${product.name} added to cart` : `Add ${product.name} to cart`}
        >
          {added ? (
            <>
              Added <Icon name="check" size={14} strokeWidth={2.4} />
            </>
          ) : (
            'Add'
          )}
        </button>
      </div>
      {error && (
        <p className="related-card__error" role="alert">
          {error}
        </p>
      )}
    </article>
  );
}
