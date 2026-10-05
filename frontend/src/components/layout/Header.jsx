import { useId, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useCart } from '../../context/CartContext.jsx';
import { useWishlist } from '../../context/WishlistContext.jsx';
import { getCategory } from '../../data/categories.js';
import { brand, mainNav } from '../../data/content.js';
import Icon from '../ui/Icon.jsx';
import SearchForm from '../ui/SearchForm.jsx';
import './Header.css';

/** Nav item is active for its own URL, and department links (/shop/men) also for their categories (/shop/panjabi). */
function isActive(location, to) {
  const [path, query] = to.split('?');
  if (query) return location.pathname === path && location.search === `?${query}`;
  if (location.pathname === path) return true;
  const [, section, slug] = location.pathname.split('/');
  const [, navSection, navSlug] = path.split('/');
  if (section !== 'shop' || navSection !== 'shop' || !slug) return false;
  return getCategory(slug)?.parent === navSlug;
}

export default function Header({ onOpenMenu, menuOpen }) {
  const location = useLocation();
  const { count: cartCount } = useCart();
  const { count: wishCount } = useWishlist();
  const [searchOpen, setSearchOpen] = useState(false);
  const searchId = useId();
  const mobileSearchId = useId();

  return (
    <header className="site-header">
      <div className="site-header__inner container">
        <button
          type="button"
          className="icon-btn site-header__burger"
          aria-label="Open menu"
          aria-expanded={menuOpen}
          aria-controls="mobile-menu"
          onClick={onOpenMenu}
        >
          <Icon name="menu" size={22} strokeWidth={1.9} />
        </button>

        <Link to="/" className="site-header__logo">
          {brand.name}
        </Link>

        <nav aria-label="Main" className="site-header__nav">
          <ul role="list">
            {mainNav.map((item) => {
              const active = isActive(location, item.to);
              return (
                <li key={item.label}>
                  <Link
                    to={item.to}
                    className={`site-header__nav-link${item.sale ? ' is-sale' : ''}${active ? ' is-active' : ''}`}
                    aria-current={active ? 'page' : undefined}
                  >
                    {item.label}
                  </Link>
                </li>
              );
            })}
          </ul>
        </nav>

        <SearchForm id={searchId} className="site-header__search" />

        <div className="site-header__actions">
          <button
            type="button"
            className="icon-btn site-header__search-toggle"
            aria-label={searchOpen ? 'Close search' : 'Search'}
            aria-expanded={searchOpen}
            aria-controls="header-mobile-search"
            onClick={() => setSearchOpen((v) => !v)}
          >
            <Icon name={searchOpen ? 'close' : 'search'} size={22} strokeWidth={1.9} />
          </button>
          <Link to="/account" className="icon-btn site-header__account" aria-label="My account">
            <Icon name="user" size={22} />
          </Link>
          <Link
            to="/account?tab=wishlist"
            className="icon-btn site-header__wishlist"
            aria-label={`Wishlist, ${wishCount} ${wishCount === 1 ? 'item' : 'items'}`}
          >
            <Icon name="heart" size={22} />
            {wishCount > 0 && (
              <span className="count-badge" aria-hidden="true">
                {wishCount}
              </span>
            )}
          </Link>
          <Link to="/cart" className="icon-btn" aria-label={`Cart, ${cartCount} ${cartCount === 1 ? 'item' : 'items'}`}>
            <Icon name="bag" size={22} />
            {cartCount > 0 && (
              <span className="count-badge count-badge--brand" aria-hidden="true">
                {cartCount}
              </span>
            )}
          </Link>
        </div>
      </div>

      {searchOpen && (
        <div id="header-mobile-search" className="site-header__mobile-search">
          <SearchForm
            id={mobileSearchId}
            placeholder="Search panjabi, kurti, shirt…"
            autoFocus
            onSubmitted={() => setSearchOpen(false)}
          />
        </div>
      )}
    </header>
  );
}
