import { useEffect, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { delivery } from '../../data/content.js';
import { sampleOrder } from '../../data/orders.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { formatBDT, formatDiscount, imageUrl, pluralize } from '../../utils/format.js';
import CheckoutSteps from '../Checkout/CheckoutSteps.jsx';
import OrderTotals from '../Checkout/OrderTotals.jsx';
import './OrderSuccess.css';

/**
 * Order confirmation — OrderSuccess.dc.html.
 * Reads the order Checkout put in router state (`navigate('/order-success', { state: { order } })`);
 * visited directly it shows the sample order #WB-10482 from data/orders.js.
 */
export default function OrderSuccess() {
  useDocumentTitle('Order placed');
  const location = useLocation();
  const order = location.state?.order ?? sampleOrder;
  const zone = delivery.zones.find((z) => z.id === order.deliveryZone);
  const phone = order.address.phone;

  return (
    <div className="order-success">
      <div className="container order-success__container">
        <CheckoutSteps current={2} align="center" className="order-success__steps" />

        <section className="os-hero" aria-labelledby="os-title">
          <div className="os-hero__badge" aria-hidden="true">
            <span>
              <Icon name="check" size={36} strokeWidth={2.6} />
            </span>
          </div>
          <p className="os-hero__eyebrow">Order placed successfully</p>
          <h1 id="os-title" className="os-hero__title">
            Thank you! Your order is confirmed.
          </h1>
          <OrderNumber number={order.number} />
          <p className="os-hero__sent">
            <Icon name="mail" size={18} className="os-icon" />
            <span>
              SMS{order.contact?.email === null ? '' : ' and email'} sent to <strong>{phone}</strong>
            </span>
          </p>
          <p className="os-hero__text">
            Our team will call you shortly to confirm the order. Please keep{' '}
            <strong>{formatBDT(order.total)} in cash</strong> ready for the delivery person.
          </p>
          <div className="os-hero__actions">
            <Link to={`/track-order?order=${encodeURIComponent(order.number)}`} className="btn btn--brand os-hero__btn">
              <Icon name="truck" size={20} />
              Track order
            </Link>
            <Link to="/shop" className="btn btn--primary os-hero__btn">
              Continue shopping
            </Link>
            <button type="button" className="btn btn--outline os-hero__btn" onClick={() => window.print()}>
              <Icon name="download" size={20} />
              Download invoice (PDF)
            </button>
          </div>
        </section>

        <dl className="os-facts">
          <div className="os-fact">
            <dt>Order date</dt>
            <dd>{order.placedAt}</dd>
          </div>
          <div className="os-fact">
            <dt>Expected delivery</dt>
            <dd>{order.expectedWindow}</dd>
          </div>
          <div className="os-fact">
            <dt>Payment</dt>
            <dd>{order.payment.label}</dd>
          </div>
          <div className="os-fact">
            <dt>Order status</dt>
            <dd>
              <span className="status-chip status-chip--pending">{order.statusOnPlacement}</span>
            </dd>
          </div>
        </dl>

        <div className="os-details">
          <section className="os-card os-items" aria-labelledby="os-items-title">
            <h2 id="os-items-title" className="os-card__title">
              Items in this order
            </h2>
            <ul role="list" className="os-items__list">
              {order.items.map((item) => (
                <li key={`${item.productId}-${item.size}-${item.colour}`} className="os-item">
                  <div className="media os-item__media" style={{ background: item.tone }}>
                    <img src={imageUrl(item.image)} alt={item.name} loading="lazy" decoding="async" />
                  </div>
                  <div className="os-item__info">
                    <div className="os-item__name">{item.name}</div>
                    <div className="os-item__meta">
                      Size {item.size} · {item.colour}
                    </div>
                    <div className="os-item__meta os-item__unit">
                      {formatBDT(item.unitPrice)} × {item.qty}
                    </div>
                  </div>
                  <div className="os-item__total">{formatBDT(item.unitPrice * item.qty)}</div>
                </li>
              ))}
            </ul>
            <OrderTotals
              rows={[
                { label: `Subtotal (${pluralize(order.itemCount, 'item')})`, value: formatBDT(order.subtotal) },
                order.discount > 0 && {
                  label: `Discount${order.coupon ? ` (${order.coupon})` : ''}`,
                  value: formatDiscount(order.discount),
                  tone: 'brand',
                },
                {
                  label: `Delivery (${order.deliveryZoneName})`,
                  value: order.deliveryFee === 0 ? 'Free' : formatBDT(order.deliveryFee),
                  tone: order.deliveryFee === 0 ? 'free' : undefined,
                },
              ]}
              totalLabel="Total payable on delivery"
              total={formatBDT(order.total)}
            />
          </section>

          <div className="os-side">
            <section className="os-card" aria-labelledby="os-address-title">
              <h2 id="os-address-title" className="os-card__title os-card__title--sm">
                <Icon name="map-pin" size={20} className="os-icon" />
                Delivery address
              </h2>
              <address className="os-card__text">
                <strong>{order.address.name}</strong>
                <br />
                {phone}
                {order.address.lines.map((line) => (
                  <span key={line}>
                    <br />
                    {line}
                  </span>
                ))}
              </address>
              <p className="os-card__meta">
                {[order.deliveryZoneName, order.courier?.name, zone?.eta].filter(Boolean).join(' · ')}
              </p>
            </section>

            <section className="os-card" aria-labelledby="os-payment-title">
              <h2 id="os-payment-title" className="os-card__title os-card__title--sm">
                <Icon name="cash" size={20} className="os-icon" />
                Payment
              </h2>
              <p className="os-card__text">
                <strong>
                  {order.payment.label} ({order.payment.short})
                </strong>
                <br />
                Pay {formatBDT(order.total)} in cash when you receive the parcel. You can check the items before paying.
              </p>
            </section>

            <section className="os-card" aria-labelledby="os-next-title">
              <h2 id="os-next-title" className="os-card__title os-card__title--sm">
                What happens next
              </h2>
              <ol className="os-steps">
                <li>
                  <span className="os-steps__num is-active" aria-hidden="true">
                    1
                  </span>
                  <span>We call {phone} to confirm your order.</span>
                </li>
                <li>
                  <span className="os-steps__num" aria-hidden="true">
                    2
                  </span>
                  <span>Your items are packed and handed to the courier. You get an SMS with the consignment ID.</span>
                </li>
                <li>
                  <span className="os-steps__num" aria-hidden="true">
                    3
                  </span>
                  <span>The rider calls before delivery. Pay cash and enjoy.</span>
                </li>
              </ol>
            </section>
          </div>
        </div>

        <GuestAccount phone={phone} />
      </div>
    </div>
  );
}

/* ------------------------------------------------- order number + copy */
function OrderNumber({ number }) {
  const [copied, setCopied] = useState(null); // null | 'ok' | 'error'

  useEffect(() => {
    if (!copied) return undefined;
    const id = setTimeout(() => setCopied(null), 2500);
    return () => clearTimeout(id);
  }, [copied]);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(number);
      setCopied('ok');
    } catch {
      setCopied('error');
    }
  };

  return (
    <div className="os-number">
      <span className="os-number__label">Order number</span>
      <span className="os-number__value">#{number}</span>
      <button
        type="button"
        className={`os-number__copy${copied === 'ok' ? ' is-copied' : ''}`}
        aria-label="Copy order number"
        onClick={copy}
      >
        {copied === 'ok' ? (
          <Icon name="check" size={18} strokeWidth={2.2} />
        ) : (
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" aria-hidden="true">
            <rect x="8" y="8" width="12" height="12" rx="2" />
            <path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3" />
          </svg>
        )}
      </button>
      <span className="visually-hidden" role="status">
        {copied === 'ok' && `Order number ${number} copied`}
        {copied === 'error' && 'Could not copy. Please note the order number.'}
      </span>
    </div>
  );
}

/* ------------------------------------------------ guest → create account */
function GuestAccount({ phone }) {
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [created, setCreated] = useState(false);

  const submit = (e) => {
    e.preventDefault();
    if (password.length < 6) {
      setError('Choose a password with at least 6 characters.');
      return;
    }
    // Demo only: wire to the registration API (phone from the order + password).
    setError('');
    setCreated(true);
    setPassword('');
  };

  return (
    <section className="os-guest" aria-labelledby="os-guest-title">
      <div className="os-guest__copy">
        <p className="os-guest__eyebrow">You checked out as a guest</p>
        <h2 id="os-guest-title" className="os-guest__title">
          Create an account to track orders easily
        </h2>
        <ul role="list" className="os-guest__perks">
          {['See all your orders in one place', 'Save your address for next time', 'Easy returns & exchanges'].map((perk) => (
            <li key={perk}>
              <Icon name="check" size={16} strokeWidth={2.4} />
              {perk}
            </li>
          ))}
        </ul>
      </div>

      {created ? (
        <div className="os-guest__form" role="status">
          <p className="os-guest__done">
            <Icon name="check" size={20} strokeWidth={2.4} />
            Account created. Log in with <strong>{phone}</strong> next time.
          </p>
          <Link to="/account" className="btn btn--light os-guest__btn">
            Go to my account
          </Link>
        </div>
      ) : (
        <form className="os-guest__form" onSubmit={submit} noValidate>
          <p className="os-guest__hint">
            Your phone <strong>{phone}</strong> will be your login. Just set a password.
          </p>
          <div className="os-guest__row">
            <label htmlFor="os-password" className="visually-hidden">
              Choose a password
            </label>
            <input
              id="os-password"
              type="password"
              className="os-guest__input"
              autoComplete="new-password"
              placeholder="Choose a password (min. 6 characters)"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              aria-invalid={error ? true : undefined}
              aria-describedby={error ? 'os-password-error' : undefined}
            />
            <button type="submit" className="btn btn--light os-guest__btn">
              Create account
            </button>
          </div>
          {error && (
            <p id="os-password-error" className="os-guest__error" role="alert">
              {error}
            </p>
          )}
          <Link to="/login" className="os-guest__login">
            Already have an account? Log in
          </Link>
        </form>
      )}
    </section>
  );
}
