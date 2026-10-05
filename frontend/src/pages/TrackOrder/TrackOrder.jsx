import { useEffect, useId, useRef, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { contact } from '../../data/content.js';
import { getOrderByNumber } from '../../data/orders.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { formatBDT, formatDiscount, imageUrl, pluralize } from '../../utils/format.js';
import ReturnRequest from './ReturnRequest.jsx';
import { buildTrackView } from './trackView.js';
import './TrackOrder.css';

/**
 * Track order — TrackOrder.dc.html.
 *   /track-order                 lookup form
 *   /track-order?order=WB-10482  form prefilled + result (or "not found")
 * Submitting writes ?order= so the result URL can be shared.
 * Later: GET /api/orders/{number}?phone=… (the server must check the phone).
 */

const cleanNumber = (v) => String(v || '').replace(/^#/, '').trim().toUpperCase();

function validPhone(v) {
  if (v.includes('•')) return true; // masked number prefilled from the account
  return /^(?:\+?88)?01[3-9]\d{8}$/.test(v.replace(/[\s-]/g, ''));
}

export default function TrackOrder() {
  const [params, setParams] = useSearchParams();
  const query = cleanNumber(params.get('order'));
  const order = query ? getOrderByNumber(query) : null;
  const view = order ? buildTrackView(order) : null;
  useDocumentTitle(view ? `Track order #${view.number}` : 'Track your order');

  const resultRef = useRef(null);
  const [submitted, setSubmitted] = useState(0);
  // After a lookup, move focus to the result (or the error) so it is announced.
  useEffect(() => {
    if (submitted) resultRef.current?.focus();
  }, [submitted]);

  const onLookup = (number) => {
    setParams({ order: number });
    setSubmitted((n) => n + 1);
  };

  return (
    <div className="track">
      <div className="track__inner">
        <LookupForm initialNumber={query} initialPhone={view ? view.address.phone : ''} onLookup={onLookup} />

        <div ref={resultRef} tabIndex={-1} className="track__result">
          {!query && <EmptyState />}
          {query && !order && <NotFound number={query} />}
          {view && <OrderResult view={view} />}
        </div>
      </div>
    </div>
  );
}

/* ------------------------------------------------------------- form */
function LookupForm({ initialNumber, initialPhone, onLookup }) {
  const uid = useId();
  const [number, setNumber] = useState(initialNumber);
  const [phone, setPhone] = useState(initialPhone);
  const [errors, setErrors] = useState({});

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    const clean = cleanNumber(number);
    if (!clean) next.number = 'Enter your order number, e.g. WB-10482.';
    if (!phone.trim()) next.phone = 'Enter the phone number used at checkout.';
    else if (!validPhone(phone)) next.phone = 'Enter a valid mobile number, e.g. 01712-345678.';
    setErrors(next);
    if (next.number) document.getElementById(`${uid}-no`)?.focus();
    else if (next.phone) document.getElementById(`${uid}-phone`)?.focus();
    else onLookup(clean);
  };

  return (
    <section className="track-card track-search" aria-labelledby="track-title">
      <div className="track-search__intro">
        <h1 id="track-title" className="track-search__title">
          Track your order
        </h1>
        <p className="track-search__text">
          Enter the order number from your SMS or email and the phone number used at checkout. Logged in? See every
          order in{' '}
          <Link to="/account?tab=orders" className="link-underline">
            My account
          </Link>
          .
        </p>
      </div>
      <form className="track-search__form" onSubmit={submit} noValidate>
        <div className="field track-search__field">
          <label className="label" htmlFor={`${uid}-no`}>
            Order number
          </label>
          <input
            id={`${uid}-no`}
            className="input track-input track-input--code"
            placeholder="WB-10482"
            autoComplete="off"
            value={number}
            onChange={(e) => setNumber(e.target.value)}
            aria-invalid={Boolean(errors.number) || undefined}
            aria-describedby={errors.number ? `${uid}-no-err` : undefined}
          />
          {errors.number && (
            <span id={`${uid}-no-err`} className="error-text">
              {errors.number}
            </span>
          )}
        </div>
        <div className="field track-search__field">
          <label className="label" htmlFor={`${uid}-phone`}>
            Phone number
          </label>
          <input
            id={`${uid}-phone`}
            className="input track-input"
            type="tel"
            autoComplete="tel"
            placeholder="01XXX-XXXXXX"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
            aria-invalid={Boolean(errors.phone) || undefined}
            aria-describedby={errors.phone ? `${uid}-phone-err` : undefined}
          />
          {errors.phone && (
            <span id={`${uid}-phone-err`} className="error-text">
              {errors.phone}
            </span>
          )}
        </div>
        <button type="submit" className="btn btn--brand track-search__submit">
          Track order
        </button>
      </form>
    </section>
  );
}

/* ------------------------------------------------------- empty & error */
function EmptyState() {
  return (
    <section className="track-card track-state" aria-label="How to find your order">
      <span className="track-state__icon">
        <Icon name="package" size={26} />
      </span>
      <div>
        <h2 className="track-state__title">Your order status will show here</h2>
        <p className="track-state__text">
          The order number starts with “WB-” and is in the confirmation SMS and email, e.g. #WB-10482.
        </p>
      </div>
    </section>
  );
}

function NotFound({ number }) {
  return (
    <section className="track-card track-state track-state--error" role="alert" aria-labelledby="track-nf-title">
      <span className="track-state__icon">
        <Icon name="info" size={26} />
      </span>
      <div className="track-state__body">
        <h2 id="track-nf-title" className="track-state__title">
          We couldn’t find order #{number}
        </h2>
        <p className="track-state__text">
          Check the order number in your SMS or email and the phone number you used at checkout. New orders can take a
          few minutes to appear.
        </p>
        <p className="track-state__text">
          Still stuck? Call <a href={contact.phoneHref}>{contact.phone}</a> or{' '}
          <a href={contact.whatsappUrl}>message us on WhatsApp</a>.
        </p>
      </div>
    </section>
  );
}

/* ------------------------------------------------------------- result */
function OrderResult({ view }) {
  return (
    <>
      <section className="track-card track-status" aria-labelledby="status-h">
        <div className="track-status__head">
          <div className="track-status__titles">
            <div className="track-status__title-row">
              <h2 id="status-h" className="track-status__title">
                Order #{view.number}
              </h2>
              <span className={`status-chip status-chip--${view.status} track-status__chip`}>{view.statusLabel}</span>
            </div>
            <p className="track-status__meta">
              Placed {view.placed} · {pluralize(view.itemCount, 'item')} · {formatBDT(view.total)} {view.paymentLabel}
            </p>
          </div>
          {view.expected && view.status !== 'delivered' && (
            <div className="track-status__eta">
              <span className="track-status__eta-label">Expected delivery</span>
              <span className="track-status__eta-value">{view.expected}</span>
            </div>
          )}
        </div>

        {view.closed ? (
          <p className="track-status__closed">
            <Icon name="info" size={18} />
            {view.note || `This order was ${view.statusLabel.toLowerCase()}.`}
          </p>
        ) : (
          <Stepper steps={view.steps} current={view.stepIndex} />
        )}

        {(view.history.length > 0 || view.courier) && (
          <div className="track-status__details">
            {view.history.length > 0 && <History items={view.history} />}
            {view.courier && <Courier courier={view.courier} view={view} />}
          </div>
        )}
        {!view.closed && view.history.length === 0 && view.note && <p className="track-status__note">{view.note}</p>}
      </section>

      <div className="track-cols">
        <Items view={view} />
        <div className="track-side">
          <section className="track-card track-box" aria-labelledby="addr-h">
            <h2 id="addr-h" className="track-box__title">
              <Icon name="map-pin" size={20} className="track-box__icon" />
              Delivery address
            </h2>
            <p className="track-box__text">
              <strong>{view.address.name}</strong>
              <br />
              <span className="tabular">{view.address.phone}</span>
              {view.address.lines.map((l) => (
                <span key={l}>
                  <br />
                  {l}
                </span>
              ))}
            </p>
          </section>

          {view.canReturn && <ReturnRequest view={view} />}

          <section className="track-help" aria-labelledby="help-h">
            <h2 id="help-h" className="track-box__title">
              Need help with this order?
            </h2>
            <p className="track-help__text">Our team is available {contact.hours}, except Eid holidays.</p>
            <a href={contact.phoneHref} className="track-help__link">
              <Icon name="phone" size={20} />
              <span>
                <strong>Hotline</strong>
                <span>{contact.phone}</span>
              </span>
            </a>
            <a href={contact.whatsappUrl} className="track-help__link">
              <Icon name="whatsapp" size={20} />
              <span>
                <strong>WhatsApp</strong>
                <span>{contact.phone} · quick replies</span>
              </span>
            </a>
            <a href={`mailto:${contact.email}`} className="track-help__link">
              <Icon name="mail" size={20} />
              <span>
                <strong>Email</strong>
                <span>{contact.email}</span>
              </span>
            </a>
          </section>
        </div>
      </div>
    </>
  );
}

function Stepper({ steps, current }) {
  return (
    <ol className="track-steps" aria-label="Order progress">
      {steps.map((s, i) => {
        const done = i <= current;
        const isCurrent = i === current;
        const cls = ['track-step', done && 'is-done', isCurrent && 'is-current', i < current && 'is-passed']
          .filter(Boolean)
          .join(' ');
        return (
          <li key={s.status} className={cls} aria-current={isCurrent ? 'step' : undefined}>
            <div className="track-step__marker">
              <span className="track-step__dot">
                {done ? <Icon name="check" size={16} strokeWidth={3} /> : i + 1}
              </span>
              <span className="track-step__line" aria-hidden="true" />
            </div>
            <div className="track-step__body">
              <div className="track-step__label">
                {s.label}
                <span className="visually-hidden">{done ? ' (completed)' : ' (upcoming)'}</span>
              </div>
              {s.time && <div className="track-step__time">{s.time}</div>}
              {s.note && <div className="track-step__note">{s.note}</div>}
            </div>
          </li>
        );
      })}
    </ol>
  );
}

function History({ items }) {
  return (
    <div className="track-history">
      <h3 className="track-history__title">Tracking history</h3>
      <ul role="list" className="track-history__list">
        {items.map((h, i) => (
          <li key={h.meta} className={`track-history__item${i === 0 ? ' is-latest' : ''}`}>
            <span className="track-history__marker" aria-hidden="true">
              <span className="track-history__dot" />
              <span className="track-history__line" />
            </span>
            <span className="track-history__body">
              <span className="track-history__text">{h.text}</span>
              <span className="track-history__meta">{h.meta}</span>
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}

function Courier({ courier, view }) {
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    if (!copied) return undefined;
    const id = setTimeout(() => setCopied(false), 2000);
    return () => clearTimeout(id);
  }, [copied]);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(courier.consignmentId);
      setCopied(true);
    } catch {
      // Clipboard blocked: select-and-copy still works on the visible ID.
      setCopied(false);
    }
  };

  const rows = [
    ['Tracking code', courier.trackingCode],
    ['Delivery zone', view.deliveryZoneName],
    view.isCod && ['Cash to collect', formatBDT(view.total), true],
    ['Rider', courier.rider],
  ].filter(Boolean);

  return (
    <div className="track-courier">
      <div className="track-courier__head">
        <span className="track-courier__logo" aria-hidden="true">
          {courier.short}
        </span>
        <div>
          <div className="track-courier__label">Courier</div>
          <div className="track-courier__name">{courier.name}</div>
        </div>
      </div>
      <dl className="track-courier__rows">
        <div className="track-courier__row">
          <dt>Consignment ID</dt>
          <dd className="track-courier__id">
            <span className="tabular">{courier.consignmentId}</span>
            <button type="button" className="track-courier__copy" aria-label="Copy consignment ID" onClick={copy}>
              {copied ? <Icon name="check" size={16} strokeWidth={2.4} /> : <CopyIcon />}
            </button>
          </dd>
        </div>
        {rows.map(([label, value, strong]) => (
          <div key={label} className="track-courier__row">
            <dt>{label}</dt>
            <dd className={strong ? 'is-strong' : undefined}>{value}</dd>
          </div>
        ))}
      </dl>
      <span className="visually-hidden" role="status" aria-live="polite">
        {copied ? 'Consignment ID copied' : ''}
      </span>
      <a href={courier.trackingUrl} target="_blank" rel="noopener noreferrer" className="btn btn--outline track-courier__btn">
        Track on courier site
        <ExternalIcon />
        <span className="visually-hidden">(opens in a new tab)</span>
      </a>
      <span className="track-courier__hint">Opens the {courier.name.split(' ')[0]} tracking page in a new tab.</span>
    </div>
  );
}

function Items({ view }) {
  return (
    <section className="track-card track-items" aria-labelledby="items-h">
      <h2 id="items-h" className="track-items__title">
        Items in this order
      </h2>
      <ul role="list" className="track-items__list">
        {view.lines.map((it) => (
          <li key={it.key} className="track-item">
            <div className="track-item__media media" style={{ background: it.tone }}>
              <img src={imageUrl(it.image)} alt={it.name} loading="lazy" decoding="async" />
            </div>
            <div className="track-item__info">
              {it.slug ? (
                <Link to={`/product/${it.slug}`} className="track-item__name">
                  {it.name}
                </Link>
              ) : (
                <span className="track-item__name">{it.name}</span>
              )}
              <span className="track-item__variant">
                Size {it.size} · {it.colour} · Qty {it.qty}
              </span>
            </div>
            {it.total !== null && <span className="track-item__total tabular">{formatBDT(it.total)}</span>}
          </li>
        ))}
      </ul>
      <dl className="track-totals">
        {view.totals && (
          <>
            <div className="track-totals__row">
              <dt>Subtotal</dt>
              <dd>{formatBDT(view.totals.subtotal)}</dd>
            </div>
            {view.totals.discount > 0 && (
              <div className="track-totals__row">
                <dt>Discount{view.totals.coupon ? ` (${view.totals.coupon})` : ''}</dt>
                <dd className="text-brand">{formatDiscount(view.totals.discount)}</dd>
              </div>
            )}
            <div className="track-totals__row">
              <dt>Delivery</dt>
              <dd>{view.totals.delivery > 0 ? formatBDT(view.totals.delivery) : 'Free'}</dd>
            </div>
          </>
        )}
        <div className="track-totals__row track-totals__row--total">
          <dt>Total{view.isCod ? ' (COD)' : ` (${view.paymentLabel})`}</dt>
          <dd>{formatBDT(view.total)}</dd>
        </div>
      </dl>
    </section>
  );
}

/* Page-local icons (not in the shared Icon set) */
function CopyIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" aria-hidden="true" focusable="false">
      <rect x="8" y="8" width="12" height="12" rx="2" />
      <path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3" />
    </svg>
  );
}

function ExternalIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" focusable="false">
      <path d="M14 4h6v6" />
      <path d="M20 4l-9 9" />
      <path d="M18 14v5H5V6h5" />
    </svg>
  );
}
