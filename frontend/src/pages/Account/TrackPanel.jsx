import { useId, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { formatBDT } from '../../utils/format.js';
import { AccountCard, StatusChip } from './AccountParts.jsx';

/** Account → Track order: active orders + a quick lookup that opens /track-order. */
export default function TrackPanel({ as, activeOrders }) {
  const uid = useId();
  const navigate = useNavigate();
  const [number, setNumber] = useState('');
  const [error, setError] = useState('');

  const submit = (e) => {
    e.preventDefault();
    const clean = number.replace(/^#/, '').trim().toUpperCase();
    if (!clean) {
      setError('Enter an order number, e.g. WB-10482.');
      return;
    }
    navigate(`/track-order?order=${encodeURIComponent(clean)}`);
  };

  return (
    <AccountCard id="track" title="Track order" as={as}>
      {activeOrders.length > 0 ? (
        <ul role="list" className="acct-track-list">
          {activeOrders.map((o) => (
            <li key={o.number} className="acct-track-item">
              <span className="acct-track-item__icon">
                <Icon name="truck" size={22} />
              </span>
              <div className="acct-track-item__body">
                <span className="acct-track-item__no">
                  #{o.number} <StatusChip status={o.status} />
                </span>
                <span className="acct-track-item__meta">
                  {o.date} · {o.itemsLabel} · {formatBDT(o.total)} {o.payment}
                </span>
                {o.note && <span className="acct-track-item__note">{o.note}</span>}
              </div>
              <Link to={o.trackUrl} className="btn btn--brand btn--sm">
                Track #{o.number}
              </Link>
            </li>
          ))}
        </ul>
      ) : (
        <p className="text-muted">No orders on the way right now.</p>
      )}

      <form className="acct-track-form" onSubmit={submit} noValidate>
        <div className="field">
          <label className="label" htmlFor={`${uid}-no`}>
            Track another order
          </label>
          <input
            id={`${uid}-no`}
            className="input acct-input acct-track-form__input"
            placeholder="Order number, e.g. WB-10482"
            value={number}
            onChange={(e) => {
              setNumber(e.target.value);
              setError('');
            }}
            aria-invalid={Boolean(error) || undefined}
            aria-describedby={error ? `${uid}-err` : undefined}
          />
          {error && (
            <span id={`${uid}-err`} className="error-text">
              {error}
            </span>
          )}
        </div>
        <button type="submit" className="btn btn--outline">
          Track
        </button>
      </form>
    </AccountCard>
  );
}
