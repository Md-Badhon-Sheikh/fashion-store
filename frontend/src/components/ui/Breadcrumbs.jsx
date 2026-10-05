import { Fragment } from 'react';
import { Link } from 'react-router-dom';
import './Breadcrumbs.css';

/**
 * <Breadcrumbs items={[{ label: 'Home', to: '/' }, { label: 'Men', to: '/shop/men' }, { label: 'Panjabi' }]} />
 * The last item (or any item without `to`) is rendered as the current page.
 */
export default function Breadcrumbs({ items, className = '' }) {
  return (
    <nav aria-label="Breadcrumb" className={`breadcrumbs ${className}`.trim()}>
      <ol className="breadcrumbs__list">
        {items.map((item, i) => {
          const last = i === items.length - 1;
          return (
            <Fragment key={`${item.label}-${i}`}>
              <li className="breadcrumbs__item">
                {item.to && !last ? (
                  <Link to={item.to}>{item.label}</Link>
                ) : (
                  <span aria-current={last ? 'page' : undefined} className="breadcrumbs__current">
                    {item.label}
                  </span>
                )}
              </li>
              {!last && (
                <li className="breadcrumbs__sep" aria-hidden="true">
                  /
                </li>
              )}
            </Fragment>
          );
        })}
      </ol>
    </nav>
  );
}
