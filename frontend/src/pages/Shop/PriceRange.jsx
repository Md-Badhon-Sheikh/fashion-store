import { useId } from 'react';

const digits = (v) => String(v ?? '').replace(/[^\d]/g, '');
const grouped = (v) => (v === '' ? '' : Number(v).toLocaleString('en-IN'));

/**
 * Min / max price fields + a two-thumb range slider.
 * Values are strings of digits ('' = no limit) so the fields can be empty.
 *
 * Props:
 *   bounds    { min, max } price range of the current listing
 *   value     { min: string, max: string }
 *   onChange  (nextValue) => void
 *   variant   'desktop' (number inputs, slider above) | 'sheet' (grouped text inputs, slider below)
 */
export default function PriceRange({ bounds, value, onChange, variant = 'desktop' }) {
  const id = useId();
  const lo = bounds.min;
  const hi = Math.max(bounds.max, lo + 1);
  const span = hi - lo;
  const minNum = value.min === '' ? lo : Math.min(Math.max(Number(value.min), lo), hi);
  const maxNum = value.max === '' ? hi : Math.max(Math.min(Number(value.max), hi), lo);
  const left = ((Math.min(minNum, maxNum) - lo) / span) * 100;
  const right = 100 - ((Math.max(minNum, maxNum) - lo) / span) * 100;
  const step = span > 2000 ? 50 : 10;
  const isSheet = variant === 'sheet';

  const slider = (
    <div className="price-range__slider">
      <span className="price-range__track" aria-hidden="true" />
      <span className="price-range__fill" style={{ left: `${left}%`, right: `${right}%` }} aria-hidden="true" />
      <input
        type="range"
        min={lo}
        max={hi}
        step={step}
        value={minNum}
        aria-label="Minimum price"
        aria-valuetext={grouped(String(minNum))}
        onChange={(e) => onChange({ ...value, min: String(Math.min(Number(e.target.value), maxNum)) })}
      />
      <input
        type="range"
        min={lo}
        max={hi}
        step={step}
        value={maxNum}
        aria-label="Maximum price"
        aria-valuetext={grouped(String(maxNum))}
        onChange={(e) => onChange({ ...value, max: String(Math.max(Number(e.target.value), minNum)) })}
      />
    </div>
  );

  const field = (key, label) => (
    <label className="price-range__field" htmlFor={`${id}-${key}`}>
      {label}
      {isSheet ? (
        <input
          id={`${id}-${key}`}
          className="price-range__input"
          type="text"
          inputMode="numeric"
          autoComplete="off"
          placeholder={grouped(String(key === 'min' ? lo : hi))}
          value={grouped(value[key])}
          onChange={(e) => onChange({ ...value, [key]: digits(e.target.value) })}
        />
      ) : (
        <input
          id={`${id}-${key}`}
          className="price-range__input"
          type="number"
          min={0}
          inputMode="numeric"
          placeholder={String(key === 'min' ? lo : hi)}
          value={value[key]}
          onChange={(e) => onChange({ ...value, [key]: digits(e.target.value) })}
        />
      )}
    </label>
  );

  return (
    <div className={`price-range price-range--${variant}`}>
      {!isSheet && slider}
      <div className="price-range__fields">
        {field('min', isSheet ? 'Min (৳)' : 'Min')}
        <span className="price-range__dash" aria-hidden="true">
          –
        </span>
        {field('max', isSheet ? 'Max (৳)' : 'Max')}
      </div>
      {isSheet && slider}
    </div>
  );
}
