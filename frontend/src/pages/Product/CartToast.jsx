import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';

/**
 * Confirmation toast after "Add to cart" (and other short notices).
 * Polite live region, auto-hides after 5s, has a close button.
 *
 * Props: toast { id, tone: 'success' | 'info' | 'error', title, detail, cartLink }, onClose
 */
export default function CartToast({ toast, onClose }) {
  useEffect(() => {
    if (!toast) return undefined;
    const id = setTimeout(onClose, 5000);
    return () => clearTimeout(id);
  }, [toast, onClose]);

  return (
    <div className="pdp-toast-region" role="status" aria-live="polite">
      {toast && (
        <div key={toast.id} className={`pdp-toast pdp-toast--${toast.tone}`}>
          <span className="pdp-toast__icon" aria-hidden="true">
            <Icon name={toast.tone === 'success' ? 'check' : 'info'} size={18} strokeWidth={2.4} />
          </span>
          <div className="pdp-toast__text">
            <p className="pdp-toast__title">{toast.title}</p>
            {toast.detail && <p className="pdp-toast__detail">{toast.detail}</p>}
          </div>
          {toast.cartLink && (
            <Link to="/cart" className="pdp-toast__link">
              View cart
            </Link>
          )}
          <button type="button" className="pdp-toast__close" aria-label="Dismiss" onClick={onClose}>
            <Icon name="close" size={16} strokeWidth={2.2} />
          </button>
        </div>
      )}
    </div>
  );
}
