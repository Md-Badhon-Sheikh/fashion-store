import { Link } from 'react-router-dom';
import { statusLabels } from '../../data/orders.js';

export function StatusChip({ status, className = '' }) {
  return (
    <span className={`status-chip status-chip--${status} ${className}`.trim()}>{statusLabels[status] || status}</span>
  );
}

/**
 * White rounded card with a heading row.
 *   <AccountCard id="orders" title="Recent orders" as="h2" action={{ label: 'View all', to: '?tab=orders' }}>…</AccountCard>
 */
export function AccountCard({ id, title, as: Heading = 'h2', action, headExtra, className = '', children }) {
  const headingId = `${id}-title`;
  return (
    <section id={id} className={`acct-card ${className}`.trim()} aria-labelledby={headingId}>
      <div className="acct-card__head">
        <Heading id={headingId} className="acct-card__title">
          {title}
        </Heading>
        {headExtra}
        {action && (
          <Link to={action.to} className="acct-card__action">
            {action.label}
          </Link>
        )}
      </div>
      {children}
    </section>
  );
}

/* ---------------------------------------------- page-local icons (not in Icon set) */
const svgProps = {
  width: 18,
  height: 18,
  viewBox: '0 0 24 24',
  fill: 'none',
  stroke: 'currentColor',
  strokeWidth: 1.8,
  strokeLinecap: 'round',
  strokeLinejoin: 'round',
  'aria-hidden': true,
  focusable: 'false',
};

export function LockIcon({ size = 18 }) {
  return (
    <svg {...svgProps} width={size} height={size}>
      <rect x="5" y="10" width="14" height="10" rx="2" />
      <path d="M8 10V7a4 4 0 0 1 8 0v3" />
    </svg>
  );
}

export function BriefcaseIcon({ size = 18 }) {
  return (
    <svg {...svgProps} width={size} height={size}>
      <rect x="4" y="7" width="16" height="13" rx="2" />
      <path d="M9 7V4h6v3" />
    </svg>
  );
}

export function OrdersIcon({ size = 18 }) {
  return (
    <svg {...svgProps} width={size} height={size}>
      <path d="M3 7l9-4 9 4v10l-9 4-9-4V7z" />
      <path d="M3 7l9 4 9-4" />
      <path d="M12 11v10" />
    </svg>
  );
}
