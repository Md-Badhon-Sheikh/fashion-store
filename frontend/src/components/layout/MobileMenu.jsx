import { useEffect, useRef, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { menuGroups } from '../../data/categories.js';
import { contact, menuHighlights } from '../../data/content.js';
import Icon from '../ui/Icon.jsx';
import './MobileMenu.css';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

/**
 * Off-canvas menu drawer (M-Menu). Opened from the header hamburger (≤ 900px).
 * Focus moves into the drawer, Tab is trapped, Esc / overlay / close button
 * close it and focus returns to the opener. Body scroll is locked while open.
 */
export default function MobileMenu({ open, onClose }) {
  if (!open) return null;
  return <Drawer onClose={onClose} />;
}

function Drawer({ onClose }) {
  const { pathname } = useLocation();
  const dialogRef = useRef(null);
  const closeRef = useRef(null);
  const initialGroup = menuGroups.find((g) => g.children.some((c) => c.to === pathname))?.slug ?? 'men';
  const [openGroup, setOpenGroup] = useState(initialGroup);

  useEffect(() => {
    const opener = document.activeElement;
    const { overflow } = document.body.style;
    document.body.style.overflow = 'hidden';
    closeRef.current?.focus();

    const onKey = (e) => {
      if (e.key === 'Escape') {
        e.preventDefault();
        onClose();
        return;
      }
      if (e.key !== 'Tab' || !dialogRef.current) return;
      const items = [...dialogRef.current.querySelectorAll(FOCUSABLE)];
      if (!items.length) return;
      const first = items[0];
      const last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    };
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = overflow;
      if (opener && typeof opener.focus === 'function') opener.focus();
    };
  }, [onClose]);

  return (
    <div className="mobile-menu" id="mobile-menu">
      <div className="mobile-menu__overlay" onClick={onClose} aria-hidden="true" />
      <div ref={dialogRef} role="dialog" aria-modal="true" aria-label="Menu" className="mobile-menu__dialog">
        <button ref={closeRef} type="button" className="mobile-menu__close" aria-label="Close menu" onClick={onClose}>
          <Icon name="close" size={20} strokeWidth={2} />
        </button>

        <aside className="mobile-menu__panel">
          <div className="mobile-menu__hello">
            <div className="mobile-menu__hello-row">
              <span className="mobile-menu__avatar">
                <Icon name="user" size={24} />
              </span>
              <div>
                <p className="mobile-menu__hello-title">Hello, guest</p>
                <p className="mobile-menu__hello-text">Log in to track orders and save your wishlist</p>
              </div>
            </div>
            <div className="mobile-menu__auth">
              <Link to="/login" className="btn btn--light" onClick={onClose}>
                Login
              </Link>
              <Link to="/login?mode=register" className="btn btn--outline-light" onClick={onClose}>
                Register
              </Link>
            </div>
          </div>

          <nav aria-label="Shop categories" className="mobile-menu__shop">
            <p className="mobile-menu__label">Shop</p>
            {menuGroups.map((g) => {
              const expanded = openGroup === g.slug;
              const panelId = `menu-group-${g.slug}`;
              return (
                <div key={g.slug} className={`mobile-menu__group${expanded ? ' is-open' : ''}`}>
                  <button
                    type="button"
                    className="mobile-menu__group-btn"
                    aria-expanded={expanded}
                    aria-controls={panelId}
                    onClick={() => setOpenGroup(expanded ? '' : g.slug)}
                  >
                    <span>{g.label}</span>
                    <span className="mobile-menu__group-meta">
                      {g.count}
                      <Icon name="chevron-down" size={18} strokeWidth={2} className="mobile-menu__chevron" />
                    </span>
                  </button>
                  {expanded && (
                    <ul id={panelId} className="mobile-menu__children" role="list">
                      {g.children.map((c) => {
                        const current = c.to === pathname && c.to !== `/shop/${g.slug}`;
                        return (
                          <li key={c.label}>
                            <Link
                              to={c.to}
                              className={`mobile-menu__child${current ? ' is-current' : ''}`}
                              aria-current={current ? 'page' : undefined}
                              onClick={onClose}
                            >
                              <span>{c.label}</span>
                              <span className="mobile-menu__child-count">{c.count}</span>
                            </Link>
                          </li>
                        );
                      })}
                    </ul>
                  )}
                </div>
              );
            })}
            {menuHighlights.map((h) => (
              <Link
                key={h.label}
                to={h.to}
                className={`mobile-menu__row${h.tone === 'sale' ? ' is-sale' : ''}`}
                onClick={onClose}
              >
                {h.label}
                <span className={`mobile-menu__pill mobile-menu__pill--${h.tone}`}>{h.badge}</span>
              </Link>
            ))}
          </nav>

          <hr className="mobile-menu__divider" />

          <nav aria-label="Support" className="mobile-menu__support">
            <Link to="/track-order" className="mobile-menu__support-link" onClick={onClose}>
              <Icon name="truck" size={22} strokeWidth={1.7} />
              Track order
            </Link>
            <Link to="/pages/faq" className="mobile-menu__support-link" onClick={onClose}>
              <Icon name="help" size={22} strokeWidth={1.7} />
              Help &amp; FAQ
            </Link>
          </nav>

          <div className="mobile-menu__footer">
            <p className="mobile-menu__hours">Need help ordering? {contact.hours}</p>
            <a href={contact.phoneHref} className="btn btn--outline btn--block mobile-menu__cta">
              <Icon name="phone" size={19} />
              Call hotline {contact.phone}
            </a>
            <a href={contact.whatsappUrl} className="btn btn--brand btn--block mobile-menu__cta">
              <Icon name="whatsapp" size={19} />
              Chat on WhatsApp
            </a>
          </div>
        </aside>
      </div>
    </div>
  );
}
