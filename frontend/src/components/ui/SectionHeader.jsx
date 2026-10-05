import { Link } from 'react-router-dom';
import './SectionHeader.css';

/**
 * Section title row: heading (+ optional subtitle) on the left, a link or any
 * custom content (children) on the right.
 *
 *   <SectionHeader title="Shop by category" action={{ label: 'All categories', to: '/shop', mobileLabel: 'See all' }} />
 *   <SectionHeader title="New arrivals"><FilterTabs /></SectionHeader>
 *
 * Props: title, subtitle, id (heading id for aria-labelledby), as ('h2'),
 *        tone ('default' | 'sale'), action { label, to, mobileLabel?, arrow? },
 *        children (right-side content), className.
 */
export default function SectionHeader({
  title,
  subtitle,
  id,
  as: Heading = 'h2',
  tone = 'default',
  action,
  children,
  className = '',
}) {
  const arrow = action?.arrow !== false;
  return (
    <div className={`section-header section-header--${tone} ${className}`.trim()}>
      <div className="section-header__titles">
        <Heading id={id} className="section-header__title">
          {title}
        </Heading>
        {subtitle && <p className="section-header__sub">{subtitle}</p>}
      </div>
      {children}
      {action && (
        <Link to={action.to} className="section-header__action">
          {action.mobileLabel ? (
            <>
              <span className="only-desktop">
                {action.label}
                {arrow && ' →'}
              </span>
              <span className="only-mobile">{action.mobileLabel}</span>
            </>
          ) : (
            <>
              {action.label}
              {arrow && ' →'}
            </>
          )}
        </Link>
      )}
    </div>
  );
}
