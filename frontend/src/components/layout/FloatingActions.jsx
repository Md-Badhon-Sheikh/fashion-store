import { useEffect, useRef, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useCart } from '../../context/CartContext.jsx';
import { contact } from '../../data/content.js';
import { formatBDT } from '../../utils/format.js';
import Icon from '../ui/Icon.jsx';
import { showFloatingActions } from './routeRules.js';
import './FloatingActions.css';

/**
 * Desktop/tablet floating UI (hidden ≤ 768px and on /cart, /checkout):
 *  - right-middle cart widget with live item count + subtotal → /cart
 *  - back-to-top button
 *  - chat button with a small WhatsApp / Messenger popover
 */
export default function FloatingActions() {
  const { pathname } = useLocation();
  const { count, subtotal } = useCart();
  const [chatOpen, setChatOpen] = useState(false);
  const chatRef = useRef(null);

  useEffect(() => {
    if (!chatOpen) return undefined;
    const onKey = (e) => e.key === 'Escape' && setChatOpen(false);
    const onClick = (e) => {
      if (chatRef.current && !chatRef.current.contains(e.target)) setChatOpen(false);
    };
    document.addEventListener('keydown', onKey);
    document.addEventListener('mousedown', onClick);
    return () => {
      document.removeEventListener('keydown', onKey);
      document.removeEventListener('mousedown', onClick);
    };
  }, [chatOpen]);

  if (!showFloatingActions(pathname)) return null;

  const itemsLabel = `${count} ${count === 1 ? 'Item' : 'Items'}`;

  const toTop = () => {
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    document.getElementById('main')?.focus({ preventScroll: true });
  };

  return (
    <div className="floating-actions">
      <Link
        to="/cart"
        className="floating-cart"
        aria-label={`Cart, ${count} ${count === 1 ? 'item' : 'items'}, total ${formatBDT(subtotal)}`}
      >
        <span className="floating-cart__top">
          <Icon name="bag" size={24} />
          {itemsLabel}
        </span>
        <span className="floating-cart__total">{formatBDT(subtotal)}</span>
      </Link>

      <div className="floating-actions__stack" ref={chatRef}>
        <button type="button" className="floating-actions__top" aria-label="Back to top" onClick={toTop}>
          <Icon name="chevron-up" size={20} strokeWidth={2.2} />
        </button>
        <button
          type="button"
          className="floating-actions__chat"
          aria-label="Chat with us on WhatsApp or Messenger"
          aria-expanded={chatOpen}
          aria-controls="floating-chat-panel"
          onClick={() => setChatOpen((v) => !v)}
        >
          <Icon name={chatOpen ? 'close' : 'chat'} size={26} />
        </button>
        {chatOpen && (
          <div id="floating-chat-panel" className="floating-chat" role="group" aria-label="Chat options">
            <p className="floating-chat__title">Need help? Chat with us</p>
            <p className="floating-chat__text">{contact.hours}</p>
            <a href={contact.whatsappUrl} className="btn btn--brand btn--block btn--sm">
              <Icon name="whatsapp" size={18} />
              WhatsApp
            </a>
            <a href={contact.messengerUrl} className="btn btn--outline btn--block btn--sm">
              <Icon name="chat" size={18} />
              Messenger
            </a>
          </div>
        )}
      </div>
    </div>
  );
}
