import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import Price from '../../components/ui/Price.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { useWishlist } from '../../context/WishlistContext.jsx';
import { getDefaultVariant, getDiscountPercent } from '../../data/products.js';
import { imageUrl } from '../../utils/format.js';
import { AccountCard } from './AccountParts.jsx';

/**
 * Wishlist grid bound to WishlistContext.
 *   limit   show only the first n items (overview) with a "See all" link
 */
export default function Wishlist({ as, limit, onNotice }) {
  const { products, count, remove, add } = useWishlist();
  const { addItem } = useCart();
  const navigate = useNavigate();
  const [notify, setNotify] = useState([]);
  const shown = limit ? products.slice(0, limit) : products;

  const removeItem = (p) => {
    remove(p.id);
    onNotice(`${p.name} removed from your wishlist.`, { label: 'Undo', onClick: () => add(p.id) });
  };

  const addToCart = (p) => {
    const v = getDefaultVariant(p);
    const res = addItem({ productId: p.id, size: v.size, colour: v.colour, qty: 1 });
    onNotice(res.message, res.ok ? { label: 'View cart', onClick: () => navigate('/cart') } : undefined);
  };

  const toggleNotify = (p) => {
    const on = !notify.includes(p.id);
    setNotify((prev) => (on ? [...prev, p.id] : prev.filter((id) => id !== p.id)));
    onNotice(on ? `We’ll send you an SMS when ${p.name} is back in stock.` : `Back-in-stock alert for ${p.name} turned off.`);
  };

  return (
    <AccountCard
      id="wishlist"
      title="Wishlist"
      as={as}
      action={limit && count > 0 ? { label: `See all ${count} →`, to: '/account?tab=wishlist' } : undefined}
    >
      {count === 0 ? (
        <div className="acct-empty">
          <p>Your wishlist is empty. Tap the heart on any product to save it here.</p>
          <Link to="/shop" className="btn btn--primary btn--sm">
            Browse the shop
          </Link>
        </div>
      ) : (
        <ul role="list" className="acct-wish-grid">
          {shown.map((p) => {
            const off = getDiscountPercent(p);
            const notifyOn = notify.includes(p.id);
            return (
              <li key={p.id} className="acct-wish">
                <div className="acct-wish__media media" style={{ background: p.tone }}>
                  <img src={imageUrl(p.image)} alt={p.name} loading="lazy" decoding="async" />
                  {p.soldOut ? (
                    <span className="badge badge--oos acct-wish__badge">Out of stock</span>
                  ) : (
                    off > 0 && (
                      <span className="badge badge--sale acct-wish__badge">
                        <span className="visually-hidden">Discount </span>-{off}%
                      </span>
                    )
                  )}
                  <button
                    type="button"
                    className="acct-wish__remove"
                    aria-label={`Remove ${p.name} from wishlist`}
                    onClick={() => removeItem(p)}
                  >
                    <Icon name="heart" size={20} filled />
                  </button>
                </div>
                <Link to={`/product/${p.slug}`} className="acct-wish__name">
                  {p.name}
                </Link>
                <Price price={p.price} oldPrice={p.oldPrice} className="acct-wish__price" />
                {p.soldOut ? (
                  <button
                    type="button"
                    className="btn btn--outline btn--sm acct-wish__btn"
                    aria-pressed={notifyOn}
                    onClick={() => toggleNotify(p)}
                  >
                    {notifyOn ? (
                      <>
                        <Icon name="check" size={14} strokeWidth={2.4} /> We’ll notify you
                      </>
                    ) : (
                      'Notify me'
                    )}
                  </button>
                ) : (
                  <button
                    type="button"
                    className="btn btn--outline btn--sm acct-wish__btn"
                    onClick={() => addToCart(p)}
                    aria-label={`Add ${p.name} to cart`}
                  >
                    Add to cart
                  </button>
                )}
              </li>
            );
          })}
        </ul>
      )}
    </AccountCard>
  );
}
