import { useCallback, useSyncExternalStore } from 'react';

/**
 * Subscribe to a CSS media query.
 *   const isPhone = useMediaQuery('(max-width: 768px)');
 * Prefer plain CSS media queries for layout; use this only when markup or
 * behaviour must differ (e.g. accordion vs. always-open footer columns).
 */
export function useMediaQuery(query) {
  const subscribe = useCallback(
    (onChange) => {
      if (typeof window === 'undefined' || !window.matchMedia) return () => {};
      const mql = window.matchMedia(query);
      mql.addEventListener('change', onChange);
      return () => mql.removeEventListener('change', onChange);
    },
    [query],
  );
  const getSnapshot = () => (typeof window !== 'undefined' && window.matchMedia ? window.matchMedia(query).matches : false);
  return useSyncExternalStore(subscribe, getSnapshot, () => false);
}

export const PHONE_QUERY = '(max-width: 768px)';
export const TABLET_QUERY = '(max-width: 900px)';
