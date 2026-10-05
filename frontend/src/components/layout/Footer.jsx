import { useState } from 'react';
import { Link } from 'react-router-dom';
import { brand, contact, footerColumns, paymentMethods } from '../../data/content.js';
import { PHONE_QUERY, useMediaQuery } from '../../hooks/useMediaQuery.js';
import Icon from '../ui/Icon.jsx';
import './Footer.css';

/**
 * Desktop: brand column + three link columns (payment chips under Company).
 * Phone (≤ 768px): link columns become an accordion (Help open by default),
 * payment chips move below it (M-Home).
 */
export default function Footer() {
  const isPhone = useMediaQuery(PHONE_QUERY);
  const [openCol, setOpenCol] = useState('help');

  const payments = (
    <ul className="site-footer__payments" role="list" aria-label="Payment methods">
      {paymentMethods.map((m) => (
        <li key={m} className="site-footer__payment">
          {m}
        </li>
      ))}
    </ul>
  );

  return (
    <footer className="site-footer">
      <div className="site-footer__grid container">
        <div className="site-footer__brand">
          <Link to="/" className="site-footer__logo">
            {brand.name}
          </Link>
          <address className="site-footer__address">
            {contact.address}
            <br />
            Hotline: {contact.phone}
            {isPhone ? ' · ' : <br />}
            {contact.email}
          </address>
        </div>

        {footerColumns.map((col) => {
          const expanded = !isPhone || openCol === col.id;
          const listId = `footer-col-${col.id}`;
          return (
            <div key={col.id} className={`site-footer__col${expanded ? ' is-open' : ''}`}>
              {isPhone ? (
                <h2 className="site-footer__heading">
                  <button
                    type="button"
                    className="site-footer__toggle"
                    aria-expanded={expanded}
                    aria-controls={listId}
                    onClick={() => setOpenCol(expanded ? '' : col.id)}
                  >
                    {col.title}
                    <Icon name="chevron-down" size={18} strokeWidth={2} className="site-footer__chevron" />
                  </button>
                </h2>
              ) : (
                <h2 className="site-footer__heading">{col.title}</h2>
              )}
              {expanded && (
                <ul id={listId} className="site-footer__links" role="list">
                  {col.links.map((l) => (
                    <li key={l.label}>
                      <Link to={l.to}>{l.label}</Link>
                    </li>
                  ))}
                </ul>
              )}
              {!isPhone && col.id === 'company' && payments}
            </div>
          );
        })}

        {isPhone && payments}
      </div>
      <div className="site-footer__bottom container">
        © {brand.year} {brand.name}. All rights reserved.
      </div>
    </footer>
  );
}
