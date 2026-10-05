import { Link } from 'react-router-dom';
import { AccountCard } from './AccountParts.jsx';

/**
 * Notifications: preference checkboxes (overview + tab) and, on the tab,
 * the recent notification list with "Mark all as read".
 * prefs / items state lives in Account.
 */
export default function Notifications({ as, prefs, setPrefs, items, setItems, showList = false, onNotice }) {
  const unread = items.filter((n) => n.unread).length;

  const toggle = (id) => {
    setPrefs((prev) => prev.map((p) => (p.id === id && !p.locked ? { ...p, checked: !p.checked } : p)));
  };

  return (
    <AccountCard
      id="notifications"
      title="Notifications"
      as={as}
      headExtra={
        showList && unread > 0 ? (
          <button
            type="button"
            className="acct-textbtn"
            onClick={() => {
              setItems((prev) => prev.map((n) => ({ ...n, unread: false })));
              onNotice('All notifications marked as read.');
            }}
          >
            Mark all as read
          </button>
        ) : null
      }
    >
      {showList && (
        <ul role="list" className="acct-notes">
          {items.map((n) => (
            <li key={n.id} className={`acct-note${n.unread ? ' is-unread' : ''}`}>
              <span className="acct-note__dot" aria-hidden="true" />
              <div className="acct-note__body">
                <Link
                  to={n.to}
                  className="acct-note__title"
                  onClick={() => setItems((prev) => prev.map((x) => (x.id === n.id ? { ...x, unread: false } : x)))}
                >
                  {n.unread && <span className="visually-hidden">Unread: </span>}
                  {n.title}
                </Link>
                <p className="acct-note__text">{n.text}</p>
                <p className="acct-note__time">{n.time}</p>
              </div>
            </li>
          ))}
        </ul>
      )}

      <fieldset className="acct-prefs">
        <legend className={showList ? 'acct-form__title' : 'visually-hidden'}>Notification preferences</legend>
        {prefs.map((p) => (
          <label key={p.id} className="acct-check acct-check--pref">
            <input
              type="checkbox"
              className="checkbox"
              checked={p.checked}
              disabled={p.locked}
              onChange={() => toggle(p.id)}
            />
            <span>
              {p.label} {p.note && <span className="acct-optional">{p.note}</span>}
            </span>
          </label>
        ))}
      </fieldset>
    </AccountCard>
  );
}
