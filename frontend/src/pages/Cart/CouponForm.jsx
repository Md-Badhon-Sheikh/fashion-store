import { useState } from 'react';
import Icon from '../../components/ui/Icon.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { coupons } from '../../data/content.js';
import './CouponForm.css';

/**
 * Coupon field bound to CartContext (used in the cart sidebar and the checkout summary).
 * Shows the "CODE applied · label" row with Remove, or an error message
 * (unknown code, app-only code, subtotal below the coupon minimum).
 *
 * Props:
 *   id             input id (must be unique on the page)
 *   title          visible heading text (renders an h2 with a tag icon), optional
 *   showAvailable  list the available codes under the field (cart)
 *   className
 */
export default function CouponForm({ id, title, showAvailable = false, className = '' }) {
  const { coupon, applyCoupon, removeCoupon } = useCart();
  const [code, setCode] = useState('');
  // Feedback for the last attempt: { code, ok, text }
  const [feedback, setFeedback] = useState(null);

  const submit = (e) => {
    e.preventDefault();
    const normalized = code.trim().toUpperCase();
    const result = applyCoupon(normalized);
    setFeedback({ code: normalized, ok: result.ok, text: result.message });
    if (result.ok) setCode('');
  };

  const remove = () => {
    removeCoupon();
    setFeedback(null);
  };

  // An applied-but-invalid coupon (subtotal too low) owns the error; otherwise show the last failed attempt.
  let error = null;
  if (coupon && !coupon.valid) error = coupon.error;
  else if (feedback && !feedback.ok && feedback.code !== coupon?.code) error = feedback.text;

  const errorId = `${id}-error`;
  const titleId = `${id}-title`;

  return (
    <div className={`coupon ${className}`.trim()}>
      {title && (
        <h2 id={titleId} className="coupon__title">
          <Icon name="tag" size={20} className="coupon__title-icon" />
          {title}
        </h2>
      )}
      <form className="coupon__form" onSubmit={submit} noValidate aria-labelledby={title ? titleId : undefined}>
        <label htmlFor={id} className="visually-hidden">
          Coupon code
        </label>
        <input
          id={id}
          type="text"
          className="input coupon__input"
          placeholder={showAvailable ? 'e.g. EID10' : 'Coupon code'}
          value={code}
          onChange={(e) => setCode(e.target.value)}
          autoComplete="off"
          autoCapitalize="characters"
          spellCheck={false}
          aria-invalid={error ? true : undefined}
          aria-describedby={error ? errorId : undefined}
        />
        <button type="submit" className="coupon__apply">
          Apply
        </button>
      </form>

      {coupon?.valid && (
        <div className="coupon__applied" role="status">
          <span>
            <strong>{coupon.code}</strong> applied · {coupon.label}
          </span>
          <button type="button" className="coupon__remove" onClick={remove} aria-label={`Remove coupon ${coupon.code}`}>
            Remove
          </button>
        </div>
      )}

      {error && (
        <div id={errorId} className="coupon__error" role="alert">
          <span>{error}</span>
          {coupon && !coupon.valid && (
            <button type="button" className="coupon__remove coupon__remove--error" onClick={remove} aria-label={`Remove coupon ${coupon.code}`}>
              Remove
            </button>
          )}
        </div>
      )}

      {showAvailable && (
        <p className="coupon__available">
          Available:{' '}
          {coupons.map((c, i) => (
            <span key={c.code}>
              {i > 0 && ' · '}
              <strong>{c.code}</strong> — {c.description}
            </span>
          ))}
        </p>
      )}
    </div>
  );
}
