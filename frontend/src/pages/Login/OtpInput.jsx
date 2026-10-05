import { useRef } from 'react';

/**
 * Six single-digit boxes that behave like one code field:
 * typing auto-advances, Backspace goes back, arrow keys move, and pasting a
 * full code (from any box) fills every box.
 *
 *   <OtpInput value={digits} onChange={setDigits} length={6} invalid={false} />
 * `value` is an array of `length` strings ('' or one digit).
 */
export default function OtpInput({ id, value, onChange, length = 6, invalid = false, describedBy, autoFocus = false }) {
  const refs = useRef([]);

  const focusBox = (i) => {
    const box = refs.current[Math.max(0, Math.min(length - 1, i))];
    if (box) {
      box.focus();
      box.select();
    }
  };

  const fillFrom = (start, text) => {
    const digits = text.replace(/\D/g, '').slice(0, length - start).split('');
    if (digits.length === 0) return;
    const next = [...value];
    digits.forEach((d, k) => {
      next[start + k] = d;
    });
    onChange(next);
    focusBox(start + digits.length >= length ? length - 1 : start + digits.length);
  };

  const handleChange = (i, e) => {
    const raw = e.target.value.replace(/\D/g, '');
    if (!raw) {
      const next = [...value];
      next[i] = '';
      onChange(next);
      return;
    }
    // Autofill (SMS one-time-code) or fast typing can put several digits in one box.
    if (raw.length > 1) {
      fillFrom(i, raw);
      return;
    }
    const next = [...value];
    next[i] = raw;
    onChange(next);
    if (i < length - 1) focusBox(i + 1);
  };

  const handleKeyDown = (i, e) => {
    if (e.key === 'Backspace' && !value[i] && i > 0) {
      e.preventDefault();
      const next = [...value];
      next[i - 1] = '';
      onChange(next);
      focusBox(i - 1);
    } else if (e.key === 'ArrowLeft') {
      e.preventDefault();
      focusBox(i - 1);
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      focusBox(i + 1);
    }
  };

  const handlePaste = (i, e) => {
    const text = e.clipboardData?.getData('text') ?? '';
    if (!/\d/.test(text)) return;
    e.preventDefault();
    fillFrom(i, text);
  };

  return (
    <div className="auth-otp" id={id}>
      {Array.from({ length }, (_, i) => (
        <input
          key={i}
          ref={(el) => {
            refs.current[i] = el;
          }}
          className={`auth-otp__box${value[i] ? ' is-filled' : ''}`}
          type="text"
          inputMode="numeric"
          pattern="[0-9]*"
          autoComplete={i === 0 ? 'one-time-code' : 'off'}
          maxLength={length}
          value={value[i] ?? ''}
          aria-label={`Digit ${i + 1} of ${length}`}
          aria-invalid={invalid || undefined}
          aria-describedby={describedBy}
          autoFocus={autoFocus && i === 0}
          onChange={(e) => handleChange(i, e)}
          onKeyDown={(e) => handleKeyDown(i, e)}
          onPaste={(e) => handlePaste(i, e)}
          onFocus={(e) => e.target.select()}
        />
      ))}
    </div>
  );
}
