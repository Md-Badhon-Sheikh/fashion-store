/**
 * Route-based layout rules shared by Layout, FloatingActions and MobileTabBar.
 */

/** Floating cart widget / back-to-top / chat are hidden on these paths. */
const NO_FLOATING = ['/cart', '/checkout'];

/**
 * Phone tab bar is hidden where the page has its own sticky bottom bar
 * (M-Product buy bar, M-Checkout total bar).
 */
const NO_TABBAR_PREFIX = ['/product/', '/checkout'];

export const showFloatingActions = (pathname) => !NO_FLOATING.some((p) => pathname === p || pathname.startsWith(p + '/'));

export const showTabBar = (pathname) => !NO_TABBAR_PREFIX.some((p) => pathname.startsWith(p));

/**
 * Stable page key written to <div class="site" data-page="…"> so page CSS can
 * adjust the shared chrome, e.g. `.site[data-page='product'] .site-header`.
 *   /                → home        /shop/panjabi → shop
 *   /product/x       → product     /pages/faq    → pages
 */
export function pageKey(pathname) {
  const first = pathname.split('/').filter(Boolean)[0];
  return first || 'home';
}
