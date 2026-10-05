import { useCallback, useState } from 'react';
import { Outlet, ScrollRestoration, useLocation } from 'react-router-dom';
import AnnouncementBar from './AnnouncementBar.jsx';
import FloatingActions from './FloatingActions.jsx';
import Footer from './Footer.jsx';
import Header from './Header.jsx';
import MobileMenu from './MobileMenu.jsx';
import MobileTabBar from './MobileTabBar.jsx';
import { pageKey, showTabBar } from './routeRules.js';
import './Layout.css';

/**
 * Store shell: announcement, header, mobile drawer, <Outlet/>, footer,
 * floating actions and the phone tab bar.
 * <ScrollRestoration/> scrolls to top on navigation (or to #hash targets) and
 * restores the position on back/forward.
 */
export default function Layout() {
  const { pathname } = useLocation();
  const [menuOpen, setMenuOpen] = useState(false);
  const openMenu = useCallback(() => setMenuOpen(true), []);
  const closeMenu = useCallback(() => setMenuOpen(false), []);
  const withTabBar = showTabBar(pathname);

  return (
    <div className={`site${withTabBar ? ' site--with-tabbar' : ''}`} data-page={pageKey(pathname)}>
      <a href="#main" className="skip-link">
        Skip to content
      </a>
      <AnnouncementBar />
      <Header onOpenMenu={openMenu} menuOpen={menuOpen} />
      <MobileMenu open={menuOpen} onClose={closeMenu} />
      <main id="main" className="site__main" tabIndex={-1}>
        <Outlet />
      </main>
      <Footer />
      <FloatingActions />
      {withTabBar && <MobileTabBar />}
      <ScrollRestoration />
    </div>
  );
}
