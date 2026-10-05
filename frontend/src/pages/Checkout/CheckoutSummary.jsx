import { useState } from 'react';
import Icon from '../../components/ui/Icon.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { formatBDT, formatDiscount, imageUrl, pluralize } from '../../utils/format.js';
import CouponForm from '../Cart/CouponForm.jsx';
import OrderTotals from './OrderTotals.jsx';
import TermsNote from './TermsNote.jsx';

/**
 * Checkout order summary.
 * Desktop: sidebar card with items, coupon, totals and the Place order button.
 * Phone (collapsible): a toggle row "Order summary (3 items) ৳4,705 ▾"; the
 * Place order button lives in the sticky bottom bar instead.
 *
 * Props: formId (the checkout <form> the submit button belongs to), collapsible.
 */
export default function CheckoutSummary({ formId, collapsible }) {
  const { lines, count, subtotal, coupon, discount, zone, deliveryFee, isFreeDelivery, total } = useCart();
  const [open, setOpen] = useState(true);
  const expanded = !collapsible || open;
  const bodyId = 'co-summary-body';

  return (
    <aside className={`co-summary${collapsible ? ' co-summary--collapsible' : ''}`} aria-labelledby="co-summary-title">
      {collapsible ? (
        <h2 className="co-summary__heading">
          <button
            type="button"
            className="co-summary__toggle"
            aria-expanded={open}
            aria-controls={bodyId}
            onClick={() => setOpen((v) => !v)}
          >
            <span id="co-summary-title" className="co-summary__title">
              Order summary <span className="co-summary__count">({pluralize(count, 'item')})</span>
            </span>
            <span className="co-summary__toggle-total">{formatBDT(total)}</span>
            <Icon name="chevron-down" size={20} strokeWidth={2} className={`co-summary__chevron${open ? ' is-open' : ''}`} />
          </button>
        </h2>
      ) : (
        <h2 id="co-summary-title" className="co-summary__title">
          Order summary
        </h2>
      )}

      <div id={bodyId} className="co-summary__body" hidden={!expanded}>
        <ul role="list" className="co-items">
          {lines.map((l) => (
            <li key={l.key} className="co-item">
              <div className="co-item__media">
                <div className="media co-item__photo" style={{ background: l.tone }}>
                  <img src={imageUrl(l.image)} alt={l.name} loading="lazy" decoding="async" />
                </div>
                <span className="co-item__qty">
                  <span className="visually-hidden">Quantity </span>
                  {l.qty}
                </span>
              </div>
              <div className="co-item__info">
                <div className="co-item__name">{l.name}</div>
                <div className="co-item__variant">
                  Size {l.size} · {l.colour}
                </div>
              </div>
              <div className="co-item__price">{formatBDT(l.lineTotal)}</div>
            </li>
          ))}
        </ul>

        <CouponForm id="co-coupon" />

        <OrderTotals
          className="co-summary__totals"
          rows={[
            { label: 'Subtotal', value: formatBDT(subtotal) },
            coupon?.valid && { label: `Discount (${coupon.code})`, value: formatDiscount(discount), tone: 'brand' },
            {
              label: `Delivery (${zone.name})`,
              value: isFreeDelivery ? 'Free' : formatBDT(deliveryFee),
              tone: isFreeDelivery ? 'free' : undefined,
            },
            collapsible && { label: 'Payment', value: 'Cash on Delivery' },
          ]}
          total={formatBDT(total)}
        />
      </div>

      {!collapsible && (
        <>
          <button type="submit" form={formId} className="btn btn--brand btn--block co-summary__place">
            Place order · {formatBDT(total)}
          </button>
          <TermsNote />
        </>
      )}
    </aside>
  );
}
