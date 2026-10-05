import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { districts, getDistrict } from '../../data/checkout.js';
import { delivery } from '../../data/content.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { PHONE_QUERY, useMediaQuery } from '../../hooks/useMediaQuery.js';
import { formatBDT } from '../../utils/format.js';
import CheckoutSteps from './CheckoutSteps.jsx';
import CheckoutSummary from './CheckoutSummary.jsx';
import {
  BILLING_KEYS,
  FIELD_ORDER,
  SHIPPING_KEYS,
  buildOrder,
  fieldId,
  initialForm,
  validate,
} from './checkoutForm.js';
import TermsNote from './TermsNote.jsx';
import './Checkout.css';

const FORM_ID = 'checkout-form';

/**
 * Checkout — Checkout.dc.html (desktop) / M-Checkout.dc.html (≤ 768px).
 * Guest checkout with client-side validation. "Place order" builds the order,
 * navigates to /order-success with it in router state and clears the cart.
 */
export default function Checkout() {
  useDocumentTitle('Checkout');
  const navigate = useNavigate();
  const cart = useCart();
  const isPhone = useMediaQuery(PHONE_QUERY);
  const [form, setForm] = useState(initialForm);
  const [errors, setErrors] = useState({});
  const [submitted, setSubmitted] = useState(false);
  const [placing, setPlacing] = useState(false);

  const errorCount = Object.keys(errors).length;

  const update = (patch) => {
    const next = { ...form, ...patch };
    setForm(next);
    // After the first submit, re-validate as the customer fixes fields.
    if (submitted) setErrors(validate(next));
  };

  const changeDistrict = (keys, name) => {
    update({ [keys.district]: name, [keys.area]: '' });
    // Pre-select the delivery zone for the shipping district.
    const zone = getDistrict(name)?.zone;
    if (keys === SHIPPING_KEYS && zone) cart.setDeliveryZone(zone);
  };

  const onSubmit = (e) => {
    e.preventDefault();
    const found = validate(form);
    setErrors(found);
    setSubmitted(true);
    const first = FIELD_ORDER.find((f) => found[f]);
    if (first) {
      document.getElementById(fieldId(first))?.focus();
      return;
    }
    if (cart.isEmpty) return;
    const order = buildOrder(form, cart);
    setPlacing(true);
    navigate('/order-success', { state: { order }, replace: true });
    cart.clearCart();
  };

  if (cart.isEmpty && !placing) {
    return (
      <div className="checkout">
        <CheckoutBar />
        <div className="container checkout__container">
          <div className="co-card checkout__empty">
            <Icon name="bag" size={44} strokeWidth={1.4} className="checkout__empty-icon" />
            <h2 className="checkout__empty-title">Your cart is empty</h2>
            <p className="text-muted">Add a few items to your cart before checking out.</p>
            <Link to="/shop" className="btn btn--primary btn--lg">
              Start shopping
            </Link>
          </div>
        </div>
      </div>
    );
  }

  const fieldProps = { form, errors, update };

  return (
    <div className="checkout">
      <CheckoutBar />
      <CheckoutSteps current={1} className="checkout__steps-mobile container only-mobile" />

      <div className="container checkout__container checkout__layout">
        <form id={FORM_ID} className="checkout__form" onSubmit={onSubmit} noValidate aria-label="Checkout details">
          <div className="co-card co-login only-mobile">
            <span>Have an account? Faster checkout.</span>
            <Link to="/login" className="co-login__link">
              Log in
            </Link>
          </div>

          {submitted && errorCount > 0 && (
            <div className="co-alert" role="alert">
              <Icon name="info" size={20} />
              <span>
                Please check the {errorCount === 1 ? 'highlighted field' : `${errorCount} highlighted fields`} below.
              </span>
            </div>
          )}

          {/* Contact */}
          <section className="co-card" aria-labelledby="co-contact-title">
            <div className="co-card__head">
              <h2 id="co-contact-title" className="co-card__title">
                Contact
              </h2>
              <span className="co-card__aside only-desktop">
                Have an account? <Link to="/login">Log in</Link> · or continue as guest
              </span>
            </div>
            <div className="co-grid">
              <Field
                {...fieldProps}
                name="phone"
                label="Mobile number"
                required
                hint="We’ll send order updates by SMS to this number."
                render={(a11y) => (
                  <div className={`co-phone${errors.phone ? ' is-invalid' : ''}`}>
                    <span className="co-phone__prefix" aria-hidden="true">
                      +88
                    </span>
                    <input
                      {...a11y}
                      type="tel"
                      inputMode="tel"
                      autoComplete="tel-national"
                      placeholder="01XXXXXXXXX"
                      className="co-phone__input"
                      value={form.phone}
                      onChange={(e) => update({ phone: e.target.value })}
                    />
                  </div>
                )}
              />
              <Field
                {...fieldProps}
                name="email"
                label="Email (for invoice, optional)"
                render={(a11y) => (
                  <input
                    {...a11y}
                    type="email"
                    autoComplete="email"
                    placeholder="you@email.com"
                    className="input"
                    value={form.email}
                    onChange={(e) => update({ email: e.target.value })}
                  />
                )}
              />
            </div>
          </section>

          {/* Shipping address */}
          <section className="co-card" aria-labelledby="co-shipping-title">
            <h2 id="co-shipping-title" className="co-card__title">
              Shipping address
            </h2>
            <AddressFields {...fieldProps} keys={SHIPPING_KEYS} onDistrict={changeDistrict} autoCompleteSection="shipping" />
            <div className="co-checks">
              <label className="co-check">
                <input
                  type="checkbox"
                  className="checkbox"
                  checked={form.billingSame}
                  onChange={(e) => update({ billingSame: e.target.checked })}
                />
                Billing address same as shipping
              </label>
              <label className="co-check">
                <input
                  type="checkbox"
                  className="checkbox"
                  checked={form.saveAddress}
                  onChange={(e) => update({ saveAddress: e.target.checked })}
                />
                Save this address for next time
              </label>
            </div>
            {!form.billingSame && (
              <fieldset id="co-billing" className="co-billing">
                <legend className="co-billing__title">Billing address</legend>
                <AddressFields {...fieldProps} keys={BILLING_KEYS} onDistrict={changeDistrict} autoCompleteSection="billing" />
              </fieldset>
            )}
          </section>

          {/* Delivery zone */}
          <fieldset className="co-card co-fieldset">
            <legend className="co-card__title">Delivery area</legend>
            <div className="co-options">
              {delivery.zones.map((z) => {
                const fee = cart.getDeliveryFee(z.id);
                const selected = cart.deliveryZone === z.id;
                return (
                  <label key={z.id} className={`co-option${selected ? ' is-selected' : ''}`}>
                    <input
                      type="radio"
                      name="zone"
                      value={z.id}
                      className="radio"
                      checked={selected}
                      onChange={() => cart.setDeliveryZone(z.id)}
                    />
                    <span className="co-option__body">
                      <span className="co-option__name">{z.name}</span>
                      <span className="co-option__meta">{z.eta}</span>
                    </span>
                    <span className="co-option__price">{fee === 0 ? 'Free' : formatBDT(fee)}</span>
                  </label>
                );
              })}
            </div>
          </fieldset>

          {/* Payment */}
          <fieldset className="co-card co-fieldset">
            <legend className="co-card__title">Payment</legend>
            <div className="co-options">
              <label className="co-option is-selected">
                <input type="radio" name="payment" value="cod" className="radio" defaultChecked />
                <span className="co-option__body">
                  <span className="co-option__name">Cash on Delivery</span>
                  <span className="co-option__meta">Pay the delivery person in cash</span>
                </span>
                <Icon name="cash" size={24} strokeWidth={1.6} className="co-option__icon" />
              </label>
              <label className="co-option is-disabled">
                <input type="radio" name="payment" value="online" className="radio" disabled />
                <span className="co-option__body">
                  <span className="co-option__name">Online payment</span>
                  <span className="co-option__meta">bKash · Nagad · Visa / Mastercard</span>
                </span>
                <span className="co-option__soon">Coming soon</span>
              </label>
            </div>
            <div className="field co-note">
              <label htmlFor={fieldId('note')} className="label">
                Order note (optional)
              </label>
              <textarea
                id={fieldId('note')}
                className="textarea"
                rows={3}
                maxLength={300}
                placeholder="e.g. Call before delivery"
                value={form.note}
                onChange={(e) => update({ note: e.target.value })}
              />
            </div>
          </fieldset>
        </form>

        <CheckoutSummary formId={FORM_ID} collapsible={isPhone} />

        <TermsNote className="only-mobile co-terms--mobile" />
      </div>

      {/* Phone: sticky total + Place order (M-Checkout) */}
      <div className="co-sticky only-mobile">
        <div className="co-sticky__total">
          <span className="co-sticky__label">Total (incl. delivery)</span>
          <span className="co-sticky__amount">{formatBDT(cart.total)}</span>
        </div>
        <button type="submit" form={FORM_ID} className="btn btn--brand co-sticky__btn">
          Place order
        </button>
      </div>
    </div>
  );
}

/* ------------------------------------------------ page bar (title, steps) */
function CheckoutBar() {
  return (
    <div className="checkout-bar">
      <div className="container checkout__container checkout-bar__inner">
        <Link to="/cart" className="icon-btn checkout-bar__back only-mobile" aria-label="Back to cart">
          <Icon name="chevron-left" size={22} strokeWidth={2} />
        </Link>
        <h1 className="checkout-bar__title">Checkout</h1>
        <CheckoutSteps current={1} className="only-desktop" />
        <span className="checkout-bar__secure">
          <Icon name="shield" size={16} strokeWidth={2} />
          <span className="only-desktop">Secure checkout</span>
          <span className="only-mobile">Secure</span>
        </span>
      </div>
    </div>
  );
}

/* ------------------------------------------------------------- fields */

/**
 * Label + control + hint + error. `render(a11yProps)` returns the control;
 * spread a11yProps on the input so id / aria-invalid / aria-describedby are set.
 */
function Field({ name, label, required = false, hint, errors, render, className = '' }) {
  const id = fieldId(name);
  const error = errors[name];
  const describedBy = [hint && `${id}-hint`, error && `${id}-error`].filter(Boolean).join(' ') || undefined;
  return (
    <div className={`field co-field ${className}`.trim()}>
      <label htmlFor={id} className="label">
        {label}
        {required && <span aria-hidden="true"> *</span>}
      </label>
      {render({
        id,
        name,
        'aria-invalid': error ? true : undefined,
        'aria-describedby': describedBy,
        'aria-required': required || undefined,
      })}
      {hint && (
        <p id={`${id}-hint`} className="hint">
          {hint}
        </p>
      )}
      {error && (
        <p id={`${id}-error`} className="error-text co-field__error">
          <Icon name="info" size={16} strokeWidth={2} />
          {error}
        </p>
      )}
    </div>
  );
}

function AddressFields({ form, errors, update, keys, onDistrict, autoCompleteSection }) {
  const district = getDistrict(form[keys.district]);
  const ac = (token) => `section-${autoCompleteSection} ${autoCompleteSection} ${token}`;
  return (
    <div className="co-grid co-grid--address">
      <Field
        form={form}
        errors={errors}
        name={keys.name}
        label="Full name"
        required
        className="co-field--name"
        render={(a11y) => (
          <input
            {...a11y}
            type="text"
            autoComplete={ac('name')}
            placeholder="Recipient’s name"
            className="input"
            value={form[keys.name]}
            onChange={(e) => update({ [keys.name]: e.target.value })}
          />
        )}
      />
      <Field
        form={form}
        errors={errors}
        name={keys.district}
        label="District"
        required
        render={(a11y) => (
          <select {...a11y} className="select" value={form[keys.district]} onChange={(e) => onDistrict(keys, e.target.value)}>
            <option value="">Select district</option>
            {districts.map((d) => (
              <option key={d.name} value={d.name}>
                {d.name}
              </option>
            ))}
          </select>
        )}
      />
      <Field
        form={form}
        errors={errors}
        name={keys.area}
        label="Area / Thana"
        required
        render={(a11y) => (
          <select
            {...a11y}
            className="select"
            value={form[keys.area]}
            onChange={(e) => update({ [keys.area]: e.target.value })}
            disabled={!district}
          >
            <option value="">{district ? 'Select area' : 'Choose a district first'}</option>
            {district?.areas.map((a) => (
              <option key={a} value={a}>
                {a}
              </option>
            ))}
          </select>
        )}
      />
      <Field
        form={form}
        errors={errors}
        name={keys.address}
        label="Full address"
        required
        className="co-field--address"
        render={(a11y) => (
          <input
            {...a11y}
            type="text"
            autoComplete={ac('street-address')}
            placeholder="House, road, block, landmark"
            className="input"
            value={form[keys.address]}
            onChange={(e) => update({ [keys.address]: e.target.value })}
          />
        )}
      />
    </div>
  );
}
