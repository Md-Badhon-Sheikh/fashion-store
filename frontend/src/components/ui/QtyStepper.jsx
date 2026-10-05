import Icon from './Icon.jsx';
import './QtyStepper.css';

/**
 * Quantity stepper:  [ − ]  2  [ + ]
 *
 * Props:
 *   value      current quantity (number)
 *   onChange   (nextQty) => void, already clamped to [min, max]
 *   min        default 1
 *   max        default Infinity (pass the variant stock)
 *   label      item name for aria labels ("Decrease quantity of …")
 *   size       "md" (48px, product page) | "sm" (40px, cart rows)
 *   className
 */
export default function QtyStepper({ value, onChange, min = 1, max = Infinity, label, size = 'md', className = '' }) {
  const atMin = value <= min;
  const atMax = value >= max;
  const suffix = label ? ` of ${label}` : '';
  return (
    <div className={`qty-stepper qty-stepper--${size} ${className}`.trim()} role="group" aria-label={`Quantity${suffix}`}>
      <button
        type="button"
        className="qty-stepper__btn"
        aria-label={`Decrease quantity${suffix}`}
        onClick={() => onChange(Math.max(min, value - 1))}
        disabled={atMin}
      >
        <Icon name="minus" size={16} strokeWidth={2.2} />
      </button>
      <span className="qty-stepper__value" aria-live="polite">
        {value}
      </span>
      <button
        type="button"
        className="qty-stepper__btn"
        aria-label={`Increase quantity${suffix}`}
        onClick={() => onChange(Math.min(max, value + 1))}
        disabled={atMax}
      >
        <Icon name="plus" size={16} strokeWidth={2.2} />
      </button>
    </div>
  );
}
