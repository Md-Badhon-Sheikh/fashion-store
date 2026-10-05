import { useId, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';

const REASONS = [
  'Wrong size',
  'Wrong colour',
  'Damaged or defective',
  'Different from what I ordered',
  'Changed my mind (unworn)',
];

/**
 * "Return or exchange" box with an inline request form.
 * No API yet: submitting shows a confirmation with a sample request number.
 * Later: POST /api/orders/{number}/returns { items, reason, wanted, note }.
 */
export default function ReturnRequest({ view }) {
  const uid = useId();
  const [open, setOpen] = useState(false);
  const [done, setDone] = useState(null);
  const [selected, setSelected] = useState(view.lines.length === 1 ? [view.lines[0].key] : []);
  const [reason, setReason] = useState('');
  const [wanted, setWanted] = useState('');
  const [note, setNote] = useState('');
  const [errors, setErrors] = useState({});
  const statusRef = useRef(null);

  const toggleItem = (key) =>
    setSelected((prev) => (prev.includes(key) ? prev.filter((k) => k !== key) : [...prev, key]));

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    if (selected.length === 0) next.items = 'Choose at least one item.';
    if (!reason) next.reason = 'Choose a reason.';
    setErrors(next);
    if (Object.keys(next).length) return;
    setDone({ id: `RX-${view.number.replace(/^WB-/, '')}`, count: selected.length });
    setOpen(false);
    requestAnimationFrame(() => statusRef.current?.focus());
  };

  const isExchange = reason === 'Wrong size' || reason === 'Wrong colour';

  return (
    <section className="track-card track-box" aria-labelledby={`${uid}-h`}>
      <h2 id={`${uid}-h`} className="track-box__title">
        Return or exchange
      </h2>

      {done ? (
        <div className="track-return__done" ref={statusRef} tabIndex={-1} role="status">
          <Icon name="check" size={20} strokeWidth={2.4} />
          <p>
            <strong>Request {done.id} received.</strong> We’ll confirm pickup by SMS. You get an SMS at every step.
          </p>
        </div>
      ) : (
        <>
          <p className="track-box__text track-box__text--sm">
            Wrong size or colour? Request an exchange within 7 days of delivery. Items must be unworn with tags attached.
          </p>
          <button
            type="button"
            className="btn btn--outline track-return__toggle"
            aria-expanded={open}
            aria-controls={`${uid}-form`}
            onClick={() => setOpen((o) => !o)}
          >
            <Icon name="exchange" size={18} />
            Request return / exchange
          </button>
        </>
      )}

      {open && !done && (
        <form id={`${uid}-form`} className="track-return__form" onSubmit={submit} noValidate>
          {view.status !== 'delivered' && (
            <p className="track-return__hint">
              <Icon name="info" size={16} />
              Your order is still on the way. We’ll arrange the pickup after delivery.
            </p>
          )}
          <fieldset className="track-return__items" aria-describedby={errors.items ? `${uid}-items-err` : undefined}>
            <legend className="label">Which items?</legend>
            {view.lines.map((it) => (
              <label key={it.key} className="track-return__item">
                <input
                  type="checkbox"
                  className="checkbox"
                  checked={selected.includes(it.key)}
                  onChange={() => toggleItem(it.key)}
                />
                <span>
                  {it.name}
                  <span className="track-return__variant">
                    Size {it.size} · {it.colour} · Qty {it.qty}
                  </span>
                </span>
              </label>
            ))}
            {errors.items && (
              <span id={`${uid}-items-err`} className="error-text">
                {errors.items}
              </span>
            )}
          </fieldset>

          <div className="field">
            <label className="label" htmlFor={`${uid}-reason`}>
              Reason
            </label>
            <select
              id={`${uid}-reason`}
              className="select"
              value={reason}
              onChange={(e) => setReason(e.target.value)}
              aria-invalid={Boolean(errors.reason) || undefined}
              aria-describedby={errors.reason ? `${uid}-reason-err` : undefined}
            >
              <option value="">Choose a reason</option>
              {REASONS.map((r) => (
                <option key={r}>{r}</option>
              ))}
            </select>
            {errors.reason && (
              <span id={`${uid}-reason-err`} className="error-text">
                {errors.reason}
              </span>
            )}
          </div>

          {isExchange && (
            <div className="field">
              <label className="label" htmlFor={`${uid}-wanted`}>
                New size or colour
              </label>
              <input
                id={`${uid}-wanted`}
                className="input"
                placeholder="e.g. Size L, Navy"
                value={wanted}
                onChange={(e) => setWanted(e.target.value)}
              />
            </div>
          )}

          <div className="field">
            <label className="label" htmlFor={`${uid}-note`}>
              Note <span className="track-return__optional">(optional)</span>
            </label>
            <textarea
              id={`${uid}-note`}
              className="textarea"
              rows={2}
              placeholder="Anything we should know? Add photos of damage when we call."
              value={note}
              onChange={(e) => setNote(e.target.value)}
            />
          </div>

          <div className="track-return__actions">
            <button type="submit" className="btn btn--brand btn--sm">
              Send request
            </button>
            <button type="button" className="btn btn--outline btn--sm" onClick={() => setOpen(false)}>
              Cancel
            </button>
          </div>
        </form>
      )}

      <span className="track-box__fine">
        {view.returnWindowEnds
          ? `Opens after delivery · available until ${view.returnWindowEnds}. `
          : 'Available for 7 days after delivery. '}
        <Link to="/pages/return-policy" className="link-underline">
          Read the policy
        </Link>
      </span>
    </section>
  );
}
