import { useId, useRef, useState } from 'react';
import { Link, NavLink, useParams } from 'react-router-dom';
import Breadcrumbs from '../../components/ui/Breadcrumbs.jsx';
import Icon from '../../components/ui/Icon.jsx';
import { contact, infoPages } from '../../data/content.js';
import { mapUrl, pageContent, sizeGuide } from '../../data/pages.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import NotFound from '../NotFound/NotFound.jsx';
import './InfoPage.css';

/**
 * Info pages — InfoPage.dc.html. /pages/:slug for about, contact, faq,
 * size-guide, return-policy, shipping, privacy, terms. Copy lives in
 * data/pages.js; unknown slugs render the 404 page.
 */
export default function InfoPage() {
  const { slug } = useParams();
  const page = pageContent[slug];
  const meta = infoPages.find((p) => p.slug === slug);
  if (!page || !meta) return <NotFound />;
  // key: reset accordion / tab state when moving between pages
  return <InfoArticle key={slug} slug={slug} page={page} />;
}

function InfoArticle({ slug, page }) {
  useDocumentTitle(page.title);
  return (
    <div className="info">
      <div className="container">
        <Breadcrumbs
          className="info__crumbs"
          items={[{ label: 'Home', to: '/' }, { label: 'Help centre', to: '/pages/faq' }, { label: page.title }]}
        />
        <div className="info__layout">
          <aside className="info-side">
            <nav aria-label="Help pages" className="info-nav">
              <div className="info-nav__title">Help &amp; information</div>
              <ul role="list" className="info-nav__list">
                {infoPages.map((p) => (
                  <li key={p.slug}>
                    <NavLink to={`/pages/${p.slug}`} className="info-nav__link">
                      {pageContent[p.slug]?.navLabel || p.title}
                      {p.slug === slug && <Icon name="chevron-right" size={16} strokeWidth={2} className="info-nav__chev" />}
                    </NavLink>
                  </li>
                ))}
              </ul>
            </nav>
            <ReturnCard className="only-desktop" />
          </aside>

          <article className="info-article" aria-labelledby="info-title">
            <header className="info-head">
              <span className="info-head__eyebrow">{page.eyebrow}</span>
              <h1 id="info-title" className="info-head__title">
                {page.title}
              </h1>
              {page.updated && <p className="info-head__updated">Last updated {page.updated}</p>}
              <p className="info-head__intro">{page.intro}</p>
              {page.notice && (
                <p className="info-notice">
                  <Icon name="info" size={18} />
                  {page.notice}
                </p>
              )}
            </header>

            {page.highlights && (
              <ul role="list" className="info-highlights">
                {page.highlights.map((h) => (
                  <li key={h.value + h.text} className={`info-highlight${h.accent ? ' is-accent' : ''}`}>
                    <span className="info-highlight__value">{h.value}</span>
                    <span className="info-highlight__text">{h.text}</span>
                  </li>
                ))}
              </ul>
            )}

            {page.contactFirst && <ContactCard title="Get in touch" />}
            {page.sizeGuide && <SizeGuide />}

            {page.sections?.length > 0 && (
              <div className="info-prose">
                {page.sections.map((s) => (
                  <section key={s.heading} className="info-section">
                    <h2 className="info-section__title">{s.heading}</h2>
                    {s.blocks.map((b, i) => (
                      <Block key={i} block={b} />
                    ))}
                  </section>
                ))}
              </div>
            )}

            {page.faqs && (
              <section id="faq" className="info-faq" aria-labelledby="faq-h">
                <h2 id="faq-h" className="info-faq__title">
                  {page.faqTitle || 'Frequently asked questions'}
                </h2>
                <Accordion items={page.faqs} defaultOpen={page.faqDefaultOpen ?? 0} />
              </section>
            )}

            {page.faqGroups &&
              page.faqGroups.map((g, gi) => (
                <section key={g.title} className="info-faq" aria-labelledby={`faq-g${gi}`}>
                  <h2 id={`faq-g${gi}`} className="info-faq__title info-faq__title--group">
                    {g.title}
                  </h2>
                  <Accordion items={g.items} defaultOpen={gi === 0 ? 0 : -1} />
                </section>
              ))}

            {page.contact && <ContactCard title="Still have questions? Contact us" />}
            <ReturnCard className="only-mobile" />
          </article>
        </div>
      </div>
    </div>
  );
}

/* ------------------------------------------------------------- content */
function RichText({ value }) {
  if (!Array.isArray(value)) return value;
  return value.map((part, i) =>
    typeof part === 'string' ? (
      part
    ) : (
      <Link key={i} to={part.to} className="link-underline">
        {part.label}
      </Link>
    ),
  );
}

function Block({ block }) {
  switch (block.type) {
    case 'list':
      return (
        <ul className="info-list">
          {block.items.map((item, i) => (
            <li key={i}>
              <RichText value={item} />
            </li>
          ))}
        </ul>
      );
    case 'steps':
      return (
        <ol role="list" className="info-steps">
          {block.items.map((item, i) => (
            <li key={i} className="info-steps__item">
              <span className="info-steps__num" aria-hidden="true">
                {i + 1}
              </span>
              <span>
                <RichText value={item} />
              </span>
            </li>
          ))}
        </ol>
      );
    case 'table':
      return (
        <div className="info-table-wrap">
          <table className="info-table">
            <thead>
              <tr>
                {block.columns.map((c) => (
                  <th key={c} scope="col">
                    {c}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {block.rows.map((row) => (
                <tr key={row[0]}>
                  {row.map((cell, i) =>
                    i === 0 ? (
                      <th key={i} scope="row">
                        {cell}
                      </th>
                    ) : (
                      <td key={i}>{cell}</td>
                    ),
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      );
    default:
      return (
        <p>
          <RichText value={block.text} />
        </p>
      );
  }
}

/* ------------------------------------------------------------- FAQ */
function Accordion({ items, defaultOpen = 0 }) {
  const uid = useId();
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div className="info-acc">
      {items.map((f, i) => {
        const isOpen = open === i;
        return (
          <div key={f.q} className={`info-acc__item${isOpen ? ' is-open' : ''}`}>
            <h3 className="info-acc__heading">
              <button
                type="button"
                id={`${uid}-q${i}`}
                className="info-acc__btn"
                aria-expanded={isOpen}
                aria-controls={`${uid}-a${i}`}
                onClick={() => setOpen(isOpen ? -1 : i)}
              >
                {f.q}
                <span className="info-acc__icon">
                  <Icon name="chevron-down" size={16} strokeWidth={2.2} />
                </span>
              </button>
            </h3>
            <div id={`${uid}-a${i}`} role="region" aria-labelledby={`${uid}-q${i}`} className="info-acc__panel" hidden={!isOpen}>
              {f.a}
            </div>
          </div>
        );
      })}
    </div>
  );
}

/* ------------------------------------------------------------- size guide */
function SizeGuide() {
  const uid = useId();
  const [active, setActive] = useState(sizeGuide.tables[0].id);
  const tabRefs = useRef({});
  const tables = sizeGuide.tables;

  const onKeyDown = (e, index) => {
    const keys = { ArrowRight: 1, ArrowLeft: -1 };
    let next = null;
    if (keys[e.key]) next = (index + keys[e.key] + tables.length) % tables.length;
    if (e.key === 'Home') next = 0;
    if (e.key === 'End') next = tables.length - 1;
    if (next === null) return;
    e.preventDefault();
    setActive(tables[next].id);
    tabRefs.current[tables[next].id]?.focus();
  };

  return (
    <section className="info-sizes" aria-labelledby={`${uid}-h`}>
      <h2 id={`${uid}-h`} className="info-section__title">
        Size charts
      </h2>
      <div role="tablist" aria-label="Size chart category" className="info-sizes__tabs h-scroll">
        {tables.map((t, i) => (
          <button
            key={t.id}
            ref={(el) => {
              tabRefs.current[t.id] = el;
            }}
            type="button"
            role="tab"
            id={`${uid}-tab-${t.id}`}
            aria-selected={active === t.id}
            aria-controls={`${uid}-panel-${t.id}`}
            tabIndex={active === t.id ? 0 : -1}
            className="chip chip--sm"
            onClick={() => setActive(t.id)}
            onKeyDown={(e) => onKeyDown(e, i)}
          >
            {t.label}
          </button>
        ))}
      </div>
      {tables.map((t) => (
        <div
          key={t.id}
          role="tabpanel"
          id={`${uid}-panel-${t.id}`}
          aria-labelledby={`${uid}-tab-${t.id}`}
          hidden={active !== t.id}
          className="info-sizes__panel"
        >
          <div className="info-table-wrap">
            <table className="info-table info-table--sizes">
              <caption className="visually-hidden">{t.label} sizes in inches</caption>
              <thead>
                <tr>
                  <th scope="col">Size</th>
                  {t.columns.map((c) => (
                    <th key={c} scope="col">
                      {c}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {t.rows.map((r) => (
                  <tr key={r.size}>
                    <th scope="row">{r.size}</th>
                    {r.values.map((v, i) => (
                      <td key={i}>{v}</td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {t.sample && <p className="info-sizes__note">{sizeGuide.sampleNote}</p>}
        </div>
      ))}
    </section>
  );
}

/* ------------------------------------------------------------- cards */
function ContactCard({ title }) {
  const uid = useId();
  const rows = [
    { icon: 'phone', label: 'Hotline', value: contact.phone, href: contact.phoneHref },
    { icon: 'whatsapp', label: 'WhatsApp', value: contact.phone, href: contact.whatsappUrl },
    { icon: 'mail', label: 'Email', value: contact.email, href: `mailto:${contact.email}` },
    { icon: 'map-pin', label: 'Shop & returns drop-off', value: contact.address },
  ];
  return (
    <section id="contact" className="info-contact" aria-labelledby={`${uid}-h`}>
      <div className="info-contact__body">
        <h2 id={`${uid}-h`} className="info-contact__title">
          {title}
        </h2>
        <p className="info-contact__hours">Open {contact.hours}.</p>
        <ul role="list" className="info-contact__list">
          {rows.map((r) => {
            const inner = (
              <>
                <span className="info-contact__icon">
                  <Icon name={r.icon} size={20} />
                </span>
                <span>
                  <span className="info-contact__label">{r.label}</span>
                  <strong className="info-contact__value">{r.value}</strong>
                </span>
              </>
            );
            return (
              <li key={r.label}>
                {r.href ? (
                  <a href={r.href} className="info-contact__row">
                    {inner}
                  </a>
                ) : (
                  <div className="info-contact__row">{inner}</div>
                )}
              </li>
            );
          })}
        </ul>
      </div>
      <div className="info-map">
        <span className="info-map__road info-map__road--h" aria-hidden="true" />
        <span className="info-map__road info-map__road--v" aria-hidden="true" />
        <span className="info-map__pin" aria-hidden="true">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor" stroke="#fff" strokeWidth="1.2" strokeLinejoin="round">
            <path d="M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12z" />
            <circle cx="12" cy="9" r="2.5" fill="#fff" />
          </svg>
        </span>
        <div className="info-map__bar">
          <span>Map placeholder · shop location</span>
          <a href={mapUrl} target="_blank" rel="noopener noreferrer" className="info-map__btn">
            Get directions
            <span className="visually-hidden"> (opens Google Maps in a new tab)</span>
          </a>
        </div>
      </div>
    </section>
  );
}

function ReturnCard({ className = '' }) {
  return (
    <div className={`info-card ${className}`.trim()}>
      <div className="info-card__title">Have an order to return?</div>
      <p className="info-card__text">Find it with your order number and phone, then tap “Request return / exchange”.</p>
      <Link to="/track-order" className="btn btn--primary btn--sm info-card__btn">
        Find my order
      </Link>
    </div>
  );
}
