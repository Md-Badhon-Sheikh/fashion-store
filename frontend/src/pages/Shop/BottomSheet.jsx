import { useEffect, useId, useRef } from 'react';
import Icon from '../../components/ui/Icon.jsx';

const FOCUSABLE =
  'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Phone bottom sheet dialog (M-Shop filter / sort sheets).
 * Focus moves to the close button, Tab is trapped inside, Esc / overlay /
 * close button call onClose, focus returns to the opener and body scroll is
 * locked while open. Render it only while open.
 *
 * Props: title, onClose, footer (sticky action row), className, children.
 */
export default function BottomSheet({ title, onClose, footer, className = '', children }) {
  const dialogRef = useRef(null);
  const closeRef = useRef(null);
  const titleId = useId();

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
      const items = [...dialogRef.current.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
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
    <div className={`sheet ${className}`.trim()}>
      <div className="sheet__overlay" onClick={onClose} aria-hidden="true" />
      <section ref={dialogRef} role="dialog" aria-modal="true" aria-labelledby={titleId} className="sheet__dialog">
        <div className="sheet__handle" aria-hidden="true">
          <span />
        </div>
        <div className="sheet__head">
          <h2 id={titleId} className="sheet__title">
            {title}
          </h2>
          <button ref={closeRef} type="button" className="icon-btn" aria-label={`Close ${title.toLowerCase()}`} onClick={onClose}>
            <Icon name="close" size={20} strokeWidth={2} />
          </button>
        </div>
        <div className="sheet__body">{children}</div>
        {footer && <div className="sheet__footer">{footer}</div>}
      </section>
    </div>
  );
}
