import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import Breadcrumbs from '../../components/ui/Breadcrumbs.jsx';
import Icon from '../../components/ui/Icon.jsx';
import { useWishlist } from '../../context/WishlistContext.jsx';
import { notificationPrefs, notifications as seedNotifications } from '../../data/account.js';
import { contact } from '../../data/content.js';
import { accountStats, addresses as seedAddresses, customer, orders, pendingReviews } from '../../data/orders.js';
import { getProductsByIds, isOnSale } from '../../data/products.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { PHONE_QUERY, useMediaQuery } from '../../hooks/useMediaQuery.js';
import { pluralize } from '../../utils/format.js';
import { AccountCard, LockIcon, OrdersIcon } from './AccountParts.jsx';
import Addresses from './Addresses.jsx';
import { buildOrder, IN_PROGRESS } from './buildOrder.js';
import Notifications from './Notifications.jsx';
import OrdersList from './Orders.jsx';
import Profile from './Profile.jsx';
import Reviews, { ReviewsSummary } from './Reviews.jsx';
import TrackPanel from './TrackPanel.jsx';
import Wishlist from './Wishlist.jsx';
import './Account.css';

/**
 * My account — Account.dc.html (desktop) / M-Account.dc.html (≤ 768px).
 * Sections switch with ?tab=: overview (default), orders, track, addresses,
 * wishlist, reviews, profile, notifications.
 * Addresses, review and notification edits are local state (no API yet).
 */

const TAB_LABELS = {
  overview: 'Overview',
  orders: 'My orders',
  track: 'Track order',
  addresses: 'Addresses',
  wishlist: 'Wishlist',
  reviews: 'Reviews',
  profile: 'Profile & password',
  notifications: 'Notifications',
};

const allOrders = orders.map(buildOrder);
const activeOrders = allOrders.filter((o) => IN_PROGRESS.includes(o.status));
const reviewProducts = getProductsByIds(pendingReviews);
const totalOrders = accountStats.find((s) => s.label === 'Total orders')?.value ?? String(orders.length);
const totalSpent = accountStats.find((s) => s.label === 'Total spent')?.value ?? '';

export default function Account() {
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const isPhone = useMediaQuery(PHONE_QUERY);
  const wishlist = useWishlist();
  const requested = params.get('tab');
  const tab = requested && TAB_LABELS[requested] ? requested : 'overview';
  useDocumentTitle(tab === 'overview' ? 'My account' : `${TAB_LABELS[tab]} · My account`);

  // Local state shared between the overview and the individual tabs.
  const [addressList, setAddressList] = useState(seedAddresses);
  const [prefs, setPrefs] = useState(notificationPrefs);
  const [noteItems, setNoteItems] = useState(seedNotifications);
  const [reviewed, setReviewed] = useState([]);
  const [notice, setNotice] = useState(null);

  const onNotice = useCallback((text, action) => setNotice({ id: Date.now(), text, action }), []);
  useEffect(() => {
    if (!notice) return undefined;
    const id = setTimeout(() => setNotice(null), 6000);
    return () => clearTimeout(id);
  }, [notice]);

  const pendingProducts = reviewProducts.filter((p) => !reviewed.includes(p.id));
  const unread = noteItems.filter((n) => n.unread).length;
  const onSaleCount = wishlist.products.filter(isOnSale).length;

  const counts = {
    orders: totalOrders,
    wishlist: String(wishlist.count),
    addresses: String(addressList.length),
    reviews: pendingProducts.length,
    notifications: unread,
  };

  const logout = () => navigate('/login');
  const heading = tab === 'overview' ? 'h2' : 'h1';

  const shared = { as: heading, onNotice };
  const sections = {
    orders: <OrdersTab isPhone={isPhone} onNotice={onNotice} as={heading} />,
    track: <TrackPanel as={heading} activeOrders={activeOrders} />,
    addresses: <Addresses list={addressList} setList={setAddressList} {...shared} />,
    wishlist: <Wishlist {...shared} />,
    reviews: (
      <Reviews
        products={reviewProducts}
        submitted={reviewed}
        onSubmit={(p) => {
          setReviewed((r) => [...r, p.id]);
          onNotice(`Thanks for reviewing ${p.name}!`);
        }}
        as={heading}
      />
    ),
    profile: <Profile {...shared} />,
    notifications: (
      <Notifications prefs={prefs} setPrefs={setPrefs} items={noteItems} setItems={setNoteItems} showList {...shared} />
    ),
  };

  let content;
  if (tab !== 'overview') {
    content = sections[tab];
  } else if (isPhone) {
    content = <PhoneOverview counts={counts} pendingCount={pendingProducts.length} unread={unread} onNotice={onNotice} onLogout={logout} />;
  } else {
    content = (
      <>
        <Hello />
        <ul role="list" className="acct-stats">
          {accountStats.map((s) => {
            const live = s.label === 'Wishlist';
            return (
              <li key={s.label} className="acct-stat">
                <span className="acct-stat__label">{s.label}</span>
                <span className="acct-stat__value tabular">{live ? wishlist.count : s.value}</span>
                <span className="acct-stat__note">
                  {live ? `${pluralize(onSaleCount, 'item')} on sale now` : s.note}
                </span>
              </li>
            );
          })}
        </ul>
        <AccountCard id="orders" title="Recent orders" action={{ label: `View all ${totalOrders} orders →`, to: '/account?tab=orders' }}>
          <OrdersList orders={allOrders} isPhone={false} onNotice={onNotice} />
        </AccountCard>
        <Addresses list={addressList} setList={setAddressList} as="h2" onNotice={onNotice} />
        <Wishlist as="h2" limit={4} onNotice={onNotice} />
        <ReviewsSummary pending={pendingProducts} />
        <Profile as="h2" onNotice={onNotice} />
        <Notifications prefs={prefs} setPrefs={setPrefs} items={noteItems} setItems={setNoteItems} as="h2" onNotice={onNotice} />
      </>
    );
  }

  const crumbs = [{ label: 'Home', to: '/' }, { label: 'My account', to: '/account' }];
  if (tab !== 'overview') crumbs.push({ label: TAB_LABELS[tab] });

  return (
    <div className="acct">
      <div className="container">
        <Breadcrumbs items={crumbs} className="acct__crumbs only-desktop" />
        <div className="acct__layout">
          {!isPhone && <Sidebar tab={tab} counts={counts} onLogout={logout} />}
          <div className="acct__main">
            {isPhone && tab === 'overview' && <h1 className="acct__phone-title">My account</h1>}
            {isPhone && tab !== 'overview' && (
              <Link to="/account" className="acct__back">
                <Icon name="chevron-left" size={18} strokeWidth={2} />
                My account
              </Link>
            )}
            {content}
          </div>
        </div>
      </div>
      <Toast notice={notice} onClose={() => setNotice(null)} />
    </div>
  );
}

/* ---------------------------------------------------------------- sidebar */
function Sidebar({ tab, counts, onLogout }) {
  const items = [
    { id: 'overview', icon: <Icon name="grid" size={18} /> },
    { id: 'orders', icon: <OrdersIcon />, count: counts.orders },
    { id: 'track', icon: <Icon name="truck" size={18} /> },
    { id: 'addresses', icon: <Icon name="map-pin" size={18} /> },
    { id: 'wishlist', icon: <Icon name="heart" size={18} />, count: counts.wishlist },
    {
      id: 'reviews',
      icon: <Icon name="star" size={18} />,
      badge: counts.reviews > 0 ? `${counts.reviews} to write` : null,
    },
    { id: 'profile', icon: <LockIcon /> },
    { id: 'notifications', icon: <Icon name="bell" size={18} />, count: counts.notifications > 0 ? `${counts.notifications} new` : null },
  ];
  return (
    <aside className="acct-side" aria-label="Account menu">
      <div className="acct-side__me">
        <Avatar size="md" />
        <div className="acct-side__who">
          <div className="acct-side__name">{customer.name}</div>
          <div className="acct-side__phone tabular">{customer.phone}</div>
        </div>
      </div>
      <nav aria-label="Account" className="acct-side__nav">
        {items.map((it) => (
          <Link
            key={it.id}
            to={it.id === 'overview' ? '/account' : `/account?tab=${it.id}`}
            className={`acct-side__link${tab === it.id ? ' is-active' : ''}`}
            aria-current={tab === it.id ? 'page' : undefined}
          >
            {it.icon}
            <span className="acct-side__label">{TAB_LABELS[it.id]}</span>
            {it.count && <span className="acct-side__count">{it.count}</span>}
            {it.badge && <span className="acct-side__badge">{it.badge}</span>}
          </Link>
        ))}
        <button type="button" className="acct-side__link acct-side__logout" onClick={onLogout}>
          <Icon name="logout" size={18} />
          Logout
        </button>
      </nav>
    </aside>
  );
}

function Avatar({ size = 'md' }) {
  return (
    <span className={`acct-avatar acct-avatar--${size}`}>
      {customer.avatar ? (
        <img src={customer.avatar} alt="Profile photo" />
      ) : (
        <Icon name="user" size={size === 'lg' ? 28 : 22} title="Profile photo" />
      )}
    </span>
  );
}

/* --------------------------------------------------------- desktop hello */
function Hello() {
  const onTheWay = activeOrders[0];
  return (
    <section className="acct-hello" aria-labelledby="acct-hello-title">
      <div className="acct-hello__text">
        <h1 id="acct-hello-title" className="acct-hello__title">
          Hello, {customer.name}
        </h1>
        <p className="acct-hello__sub">
          Member since {customer.memberSince}
          {onTheWay && ` · Your order #${onTheWay.number} is on the way.`}
        </p>
      </div>
      {onTheWay && (
        <Link to={onTheWay.trackUrl} className="btn btn--light acct-hello__btn">
          Track #{onTheWay.number}
          <Icon name="chevron-right" size={16} strokeWidth={2} />
        </Link>
      )}
    </section>
  );
}

/* ---------------------------------------------------------- orders tab */
const ORDER_FILTERS = [
  { id: 'all', label: 'All', test: () => true },
  { id: 'active', label: 'On the way', test: (o) => IN_PROGRESS.includes(o.status) },
  { id: 'delivered', label: 'Delivered', test: (o) => o.status === 'delivered' },
  { id: 'closed', label: 'Returned & cancelled', test: (o) => o.status === 'returned' || o.status === 'cancelled' },
];

function OrdersTab({ isPhone, onNotice, as }) {
  const [filter, setFilter] = useState('all');
  const shown = useMemo(() => allOrders.filter(ORDER_FILTERS.find((f) => f.id === filter).test), [filter]);
  return (
    <AccountCard id="orders" title="My orders" as={as}>
      <div className="acct-filters" role="group" aria-label="Filter orders">
        {ORDER_FILTERS.map((f) => (
          <button
            key={f.id}
            type="button"
            className="chip chip--sm"
            aria-pressed={filter === f.id}
            onClick={() => setFilter(f.id)}
          >
            {f.label}
            <span className="acct-filters__count">{allOrders.filter(f.test).length}</span>
          </button>
        ))}
      </div>
      <OrdersList orders={shown} isPhone={isPhone} onNotice={onNotice} />
      <p className="acct-footnote">
        Showing your {allOrders.length} most recent orders. Need an older invoice? Contact us at {contact.phone}.
      </p>
    </AccountCard>
  );
}

/* --------------------------------------------------------- phone overview */
function PhoneOverview({ counts, pendingCount, unread, onNotice, onLogout }) {
  const quick = [
    { to: '/account?tab=orders', label: 'Orders', icon: <Icon name="package" size={24} strokeWidth={1.7} />, count: counts.orders },
    { to: '/account?tab=wishlist', label: 'Wishlist', icon: <Icon name="heart" size={24} strokeWidth={1.7} />, count: counts.wishlist },
    { to: '/account?tab=addresses', label: 'Addresses', icon: <Icon name="map-pin" size={24} strokeWidth={1.7} />, count: counts.addresses },
    { to: '/account?tab=track', label: 'Track order', icon: <Icon name="truck" size={24} strokeWidth={1.7} /> },
  ];
  const menu = [
    { to: '/account?tab=profile', label: 'Profile & password', icon: <LockIcon size={22} /> },
    { to: '/account?tab=addresses', label: 'Addresses', icon: <Icon name="map-pin" size={22} strokeWidth={1.7} /> },
    { to: '/account?tab=reviews', label: 'My reviews', icon: <Icon name="star" size={22} strokeWidth={1.7} />, badge: pendingCount },
    { to: '/account?tab=notifications', label: 'Notifications', icon: <Icon name="bell" size={22} strokeWidth={1.7} />, badge: unread },
    { to: '/pages/faq', label: 'Help & support', icon: <Icon name="help" size={22} strokeWidth={1.7} /> },
  ];

  return (
    <>
      <section className="acct-me" aria-label="Profile summary">
        <div className="acct-me__row">
          <Avatar size="lg" />
          <div className="acct-me__who">
            <div className="acct-me__name">{customer.name}</div>
            <div className="acct-me__phone tabular">{customer.phone}</div>
            <div className="acct-me__since">Member since {customer.memberSince}</div>
          </div>
          <Link to="/account?tab=profile" className="acct-me__edit" aria-label="Edit profile">
            <Icon name="edit" size={18} strokeWidth={1.9} />
          </Link>
        </div>
        <ul role="list" className="acct-me__stats">
          {[
            [counts.orders, 'Orders'],
            [totalSpent, 'Total spent'],
            [pendingCount, 'To review'],
          ].map(([value, label]) => (
            <li key={label}>
              <span className="acct-me__stat-value tabular">{value}</span>
              <span className="acct-me__stat-label">{label}</span>
            </li>
          ))}
        </ul>
      </section>

      <nav aria-label="Quick links" className="acct-quick">
        {quick.map((q) => (
          <Link key={q.label} to={q.to} className="acct-quick__link">
            {q.icon}
            {q.label}
            {q.count && (
              <span className="acct-quick__count">
                <span className="visually-hidden">: </span>
                {q.count}
              </span>
            )}
          </Link>
        ))}
      </nav>

      <section className="acct-phone-orders" aria-labelledby="acct-recent-title">
        <div className="acct-phone-orders__head">
          <h2 id="acct-recent-title" className="acct-phone-orders__title">
            Recent orders
          </h2>
          <Link to="/account?tab=orders" className="acct-phone-orders__all">
            View all
          </Link>
        </div>
        <OrdersList orders={allOrders.slice(0, 3)} isPhone onNotice={onNotice} />
      </section>

      <nav aria-label="Account settings" className="acct-menu">
        <ul role="list" className="acct-menu__list">
          {menu.map((m) => (
            <li key={m.label}>
              <Link to={m.to} className="acct-menu__link">
                <span className="acct-menu__icon">{m.icon}</span>
                <span className="acct-menu__label">{m.label}</span>
                {m.badge > 0 && (
                  <span className="acct-menu__badge">
                    {m.badge}
                    <span className="visually-hidden"> new</span>
                  </span>
                )}
                <Icon name="chevron-right" size={18} strokeWidth={2} className="acct-menu__chev" />
              </Link>
            </li>
          ))}
          <li>
            <button type="button" className="acct-menu__link acct-menu__link--danger" onClick={onLogout}>
              <span className="acct-menu__icon">
                <Icon name="logout" size={22} strokeWidth={1.7} />
              </span>
              <span className="acct-menu__label">Log out</span>
              <Icon name="chevron-right" size={18} strokeWidth={2} className="acct-menu__chev" />
            </button>
          </li>
        </ul>
        <p className="acct-menu__hotline">
          Hotline <a href={contact.phoneHref}>{contact.phone}</a> · {contact.hours}
        </p>
      </nav>
    </>
  );
}

/* ----------------------------------------------------------------- toast */
function Toast({ notice, onClose }) {
  return (
    <div className="acct-toast-region" role="status" aria-live="polite">
      {notice && (
        <div className="acct-toast" key={notice.id}>
          <span className="acct-toast__text">{notice.text}</span>
          {notice.action && (
            <button
              type="button"
              className="acct-toast__action"
              onClick={() => {
                notice.action.onClick();
                onClose();
              }}
            >
              {notice.action.label}
            </button>
          )}
          <button type="button" className="acct-toast__close" aria-label="Dismiss message" onClick={onClose}>
            <Icon name="close" size={16} />
          </button>
        </div>
      )}
    </div>
  );
}
