import { useId, useMemo, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import CategoryRow from '../../components/ui/CategoryRow.jsx';
import Countdown from '../../components/ui/Countdown.jsx';
import Icon from '../../components/ui/Icon.jsx';
import ProductCard from '../../components/ui/ProductCard.jsx';
import SearchForm from '../../components/ui/SearchForm.jsx';
import SectionHeader from '../../components/ui/SectionHeader.jsx';
import { categories, homeCategoryOrder } from '../../data/categories.js';
import {
  flashSale,
  heroSlides,
  homeRows,
  newArrivalIds,
  newArrivalTabs,
  newsletter,
  promoCards,
  trustBadges,
} from '../../data/content.js';
import { getProductParent, getProductsByIds } from '../../data/products.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { imageUrl } from '../../utils/format.js';
import './Home.css';

/**
 * Home — Main.dc.html (desktop) / M-Home.dc.html (≤ 768px).
 * Section order: search (phone) · hero · shop by category · flash sale ·
 * new arrivals · promos · category rows · trust badges · newsletter.
 */
export default function Home() {
  useDocumentTitle();
  return (
    <div className="home">
      <div className="home__search container only-mobile">
        <SearchForm id="home-search" placeholder="Search panjabi, kurti, shirt…" />
      </div>
      <HeroDesktop />
      <HeroSlider />
      <ShopByCategory />
      <FlashSale />
      <NewArrivals />
      <Promos />
      <CategoryRows />
      <TrustBadges />
      <Newsletter />
    </div>
  );
}

/* ------------------------------------------------------------------ hero */
function HeroDesktop() {
  const slide = heroSlides[0];
  const [a, b, c] = heroSlides;
  return (
    <section className="container section only-desktop" aria-label="Featured collection">
      <div className="hero">
        <div className="hero__copy">
          <span className="hero__eyebrow">{slide.eyebrow}</span>
          <h1 className="hero__title">{slide.title}</h1>
          <p className="hero__text">{slide.textDesktop || slide.text}</p>
          <div className="hero__actions">
            <Link to={slide.cta.to} className="btn btn--light btn--lg">
              {slide.cta.label}
            </Link>
            {slide.secondaryCta && (
              <Link to={slide.secondaryCta.to} className="btn btn--outline-light btn--lg">
                {slide.secondaryCta.label}
              </Link>
            )}
          </div>
          <div className="hero__dots" aria-hidden="true">
            {heroSlides.map((s, i) => (
              <span key={s.id} className={`hero__dot${i === 0 ? ' is-active' : ''}`} />
            ))}
          </div>
        </div>
        <div className="hero__tiles">
          <HeroTile slide={a} className="hero__tile--tall" eager />
          <div className="hero__tile-stack">
            <HeroTile slide={b} eager />
            <HeroTile slide={c} eager />
          </div>
        </div>
      </div>
    </section>
  );
}

function HeroTile({ slide, className = '', eager = false }) {
  return (
    <div className={`hero__tile media ${className}`.trim()} style={{ background: slide.tone }}>
      <img src={imageUrl(slide.image)} alt={slide.alt} loading={eager ? 'eager' : 'lazy'} />
    </div>
  );
}

function HeroSlider() {
  const [index, setIndex] = useState(0);
  const touchX = useRef(null);
  const slide = heroSlides[index];
  const count = heroSlides.length;
  const go = (i) => setIndex((i + count) % count);

  return (
    <section className="hero-m only-mobile" aria-roledescription="carousel" aria-label="Featured">
      <div
        className="hero-m__card"
        aria-roledescription="slide"
        aria-label={`${index + 1} of ${count}`}
        onTouchStart={(e) => {
          touchX.current = e.touches[0].clientX;
        }}
        onTouchEnd={(e) => {
          if (touchX.current === null) return;
          const dx = e.changedTouches[0].clientX - touchX.current;
          touchX.current = null;
          if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
        }}
      >
        <div className="hero-m__copy" aria-live="polite">
          <span className="hero-m__eyebrow">{slide.eyebrow}</span>
          <h1 className="hero-m__title">{slide.title}</h1>
          <p className="hero-m__text">{slide.text}</p>
          <Link to={slide.cta.to} className="btn btn--light hero-m__cta">
            {slide.cta.label}
          </Link>
        </div>
        <div className="hero-m__photo media" style={{ background: slide.tone }}>
          <img key={slide.id} src={imageUrl(slide.image)} alt={slide.alt} />
        </div>
      </div>
      <div className="hero-m__dots">
        {heroSlides.map((s, i) => (
          <button
            key={s.id}
            type="button"
            className={`hero-m__dot${i === index ? ' is-active' : ''}`}
            aria-label={`Show banner ${i + 1} of ${count}`}
            aria-pressed={i === index}
            onClick={() => go(i)}
          >
            <span />
          </button>
        ))}
      </div>
    </section>
  );
}

/* ------------------------------------------------------- shop by category */
function ShopByCategory() {
  const headingId = useId();
  const list = homeCategoryOrder.map((slug) => categories.find((c) => c.slug === slug)).filter(Boolean);
  return (
    <section className="container section home-cats" aria-labelledby={headingId}>
      <SectionHeader
        id={headingId}
        title="Shop by category"
        action={{ label: 'All categories', to: '/shop', mobileLabel: 'See all', arrow: true }}
      />
      <ul className="home-cats__list" role="list">
        {list.map((c, i) => (
          <li key={c.slug} className={`home-cats__item${i >= 6 ? ' home-cats__item--extra' : ''}`}>
            <Link to={`/shop/${c.slug}`} className="home-cats__link">
              <span className="home-cats__circle media" style={{ background: c.tone }}>
                <img src={imageUrl(c.image)} alt="" loading="lazy" />
              </span>
              <span className="home-cats__name">{c.name}</span>
              <span className="home-cats__count">{c.count} items</span>
            </Link>
          </li>
        ))}
      </ul>
    </section>
  );
}

/* ------------------------------------------------------------ flash sale */
function FlashSale() {
  const products = useMemo(() => getProductsByIds(flashSale.productIds), []);
  return (
    <section id="flash" className="container section home-flash" aria-labelledby="flash-title">
      <div className="home-flash__box">
        <div className="home-flash__head">
          <div className="home-flash__title-wrap">
            <h2 id="flash-title" className="home-flash__title">
              {flashSale.title}
            </h2>
            <Countdown endsAt={flashSale.endsAt} />
          </div>
          <Link to="/shop?filter=flash-sale" className="home-flash__all">
            <span className="only-desktop">See all deals →</span>
            <span className="only-mobile">See all →</span>
          </Link>
        </div>
        <ul className="home-flash__list" role="list">
          {products.map((p) => (
            <li key={p.id}>
              <ProductCard product={p} variant="flash" />
            </li>
          ))}
        </ul>
      </div>
    </section>
  );
}

/* ---------------------------------------------------------- new arrivals */
function NewArrivals() {
  const [tab, setTab] = useState('all');
  const all = useMemo(() => getProductsByIds(newArrivalIds), []);
  const active = newArrivalTabs.find((t) => t.id === tab);
  const visible = all.filter((p) => {
    if (tab === 'all') return true;
    if (active?.maxPrice) return p.price <= active.maxPrice;
    return getProductParent(p) === tab;
  });

  return (
    <section id="new" className="container section home-new" aria-labelledby="new-title">
      <div className="home-new__head">
        <h2 id="new-title" className="home-new__title">
          New arrivals
        </h2>
        <Link to="/shop?filter=new" className="home-new__all only-mobile">
          View all
        </Link>
        <div className="home-new__tabs" role="group" aria-label="Filter new arrivals">
          {newArrivalTabs.map((t) => (
            <button
              key={t.id}
              type="button"
              className={`chip${t.mobileOnly ? ' only-mobile' : ''}`}
              aria-pressed={tab === t.id}
              onClick={() => setTab(t.id)}
            >
              {t.label}
            </button>
          ))}
        </div>
      </div>

      {visible.length > 0 ? (
        <ul className="home-new__grid" role="list" aria-live="polite">
          {visible.map((p) => (
            <li key={p.id}>
              <ProductCard product={p} variant="grid" />
            </li>
          ))}
        </ul>
      ) : (
        <p className="home-new__empty" role="status">
          No new arrivals in this section yet. <Link to="/shop?filter=new">See all new arrivals</Link>
        </p>
      )}

      <Link to="/shop?filter=new" className="btn btn--outline btn--block home-new__more only-mobile">
        View all new arrivals
      </Link>
    </section>
  );
}

/* ---------------------------------------------------------------- promos */
function Promos() {
  return (
    <section className="container section home-promos" aria-label="Offers">
      {promoCards.map((p) => (
        <article key={p.id} className={`promo promo--${p.id}`}>
          <div className="promo__media media" style={{ background: p.tone }}>
            <img src={imageUrl(p.image)} alt={p.alt} loading="lazy" />
          </div>
          <div className="promo__body">
            <span className="promo__eyebrow">{p.eyebrow}</span>
            <h3 className="promo__title">{p.title}</h3>
            <Link to={p.cta.to} className="promo__cta">
              {p.cta.label} →
            </Link>
          </div>
        </article>
      ))}
    </section>
  );
}

/* --------------------------------------------------------- category rows */
function CategoryRows() {
  const rows = useMemo(() => homeRows.map((r) => ({ ...r, products: getProductsByIds(r.productIds) })), []);
  return (
    <div className="home-rows">
      {rows.map((r) => (
        <div key={r.id} className="container home-rows__row">
          <CategoryRow title={r.title} subtitle={r.subtitle} products={r.products} viewAllTo={r.to} />
        </div>
      ))}
    </div>
  );
}

/* ---------------------------------------------------------- trust badges */
function TrustBadges() {
  return (
    <section className="container section" aria-label="Why shop with us">
      <ul className="trust" role="list">
        {trustBadges.map((b) => (
          <li key={b.title} className="trust__item">
            <Icon name={b.icon} size={28} strokeWidth={1.6} className="trust__icon" />
            <div>
              <p className="trust__title">
                <span className="only-desktop">{b.title}</span>
                <span className="only-mobile">{b.titleShort || b.title}</span>
              </p>
              <p className="trust__text">
                <span className="only-desktop">{b.text}</span>
                <span className="only-mobile">{b.textShort || b.text}</span>
              </p>
            </div>
          </li>
        ))}
      </ul>
    </section>
  );
}

/* ------------------------------------------------------------ newsletter */
function Newsletter() {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState({ type: 'idle', message: '' });
  const inputId = useId();
  const msgId = useId();

  const submit = (e) => {
    e.preventDefault();
    const value = email.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      setStatus({ type: 'error', message: 'Please enter a valid email address.' });
      return;
    }
    // TODO: POST /api/newsletter { email }
    setStatus({ type: 'success', message: newsletter.success });
    setEmail('');
  };

  return (
    <section className="container section home-newsletter" aria-labelledby="nl-title">
      <div className="newsletter">
        <div className="newsletter__copy">
          <h2 id="nl-title" className="newsletter__title">
            {newsletter.title}
          </h2>
          <p className="newsletter__text">{newsletter.text}</p>
        </div>
        <form className="newsletter__form" onSubmit={submit} noValidate>
          <label htmlFor={inputId} className="visually-hidden">
            Email address
          </label>
          <input
            id={inputId}
            type="email"
            autoComplete="email"
            placeholder={newsletter.placeholder}
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            aria-invalid={status.type === 'error'}
            aria-describedby={msgId}
            className="newsletter__input"
          />
          <button type="submit" className="newsletter__btn">
            {newsletter.button}
          </button>
          <p id={msgId} className={`newsletter__msg newsletter__msg--${status.type}`} role="status">
            {status.message}
          </p>
        </form>
      </div>
    </section>
  );
}
