import './CheckoutSteps.css';

const STEPS = [
  { label: 'Cart' },
  { label: 'Checkout', mobileLabel: 'Details' },
  { label: 'Confirmation' },
];

/**
 * "1. Cart — 2. Checkout — 3. Confirmation" (Cart, Checkout, OrderSuccess).
 * On phones it renders as three progress bars with labels (M-Checkout).
 *
 * Props: current (0 | 1 | 2), align ('start' | 'center'), className.
 */
export default function CheckoutSteps({ current, align = 'start', className = '' }) {
  return (
    <ol className={`checkout-steps checkout-steps--${align} ${className}`.trim()} aria-label="Checkout steps">
      {STEPS.map((step, i) => {
        const state = i < current ? ' is-done' : i === current ? ' is-current' : '';
        return (
          <li key={step.label} className={`checkout-steps__step${state}`} aria-current={i === current ? 'step' : undefined}>
            <span className="checkout-steps__bar" aria-hidden="true" />
            <span className="checkout-steps__label">
              {i + 1}.{' '}
              {step.mobileLabel ? (
                <>
                  <span className="checkout-steps__desktop">{step.label}</span>
                  <span className="checkout-steps__mobile">{step.mobileLabel}</span>
                </>
              ) : (
                step.label
              )}
            </span>
            {i < STEPS.length - 1 && (
              <span className="checkout-steps__sep" aria-hidden="true">
                —
              </span>
            )}
          </li>
        );
      })}
    </ol>
  );
}
