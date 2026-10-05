import { Link, useLocation } from 'react-router-dom';
import { useCart } from '../../context/CartContext.jsx';
import Icon from '../ui/Icon.jsx';
import './MobileTabBar.css';

/**
 * Phone bottom navigation (≤ 768px). Hidden on routes with their own sticky
 * bottom bar (see routeRules.js). Layout adds bottom padding for it.
 */
export default function MobileTabBar() {
  const { pathname, search } = useLocation();
  const { count } = useCart();
  const wishlistTab = pathname === '/account' && new URLSearchParams(search).get('tab') === 'wishlist';

  const tabs = [
    { id: 'home', label: 'Home', icon: 'home', to: '/', active: pathname === '/' },
    { id: 'categories', label: 'Categories', icon: 'grid', to: '/shop', active: pathname.startsWith('/shop') },
    { id: 'wishlist', label: 'Wishlist', icon: 'heart', to: '/account?tab=wishlist', active: wishlistTab },
    { id: 'cart', label: 'Cart', icon: 'bag', to: '/cart', active: pathname === '/cart', badge: count },
    {
      id: 'account',
      label: 'Account',
      icon: 'user',
      to: '/account',
      active: (pathname === '/account' && !wishlistTab) || pathname === '/login',
    },
  ];

  return (
    <nav aria-label="Primary" className="tabbar">
      {tabs.map((t) => (
        <Link
          key={t.id}
          to={t.to}
          className={`tabbar__item${t.active ? ' is-active' : ''}`}
          aria-current={t.active ? 'page' : undefined}
          aria-label={t.id === 'cart' ? `Cart, ${count} ${count === 1 ? 'item' : 'items'}` : undefined}
        >
          <span className="tabbar__icon">
            <Icon name={t.icon} size={22} />
            {t.badge > 0 && (
              <span className="tabbar__badge" aria-hidden="true">
                {t.badge}
              </span>
            )}
          </span>
          {t.label}
        </Link>
      ))}
    </nav>
  );
}
