import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { getProductById } from '../data/products.js';
import { readJSON, writeJSON } from '../utils/storage.js';

const STORAGE_KEY = 'yb_wishlist_v1';

/** Seed used only when the wishlist has never been stored (Account artboard). */
const SEED_IDS = ['p02', 's01', 'w02', 'n01'];

function initIds() {
  const saved = readJSON(STORAGE_KEY);
  if (Array.isArray(saved)) return saved.filter((id) => getProductById(id));
  return SEED_IDS;
}

const WishlistContext = createContext(null);

export function WishlistProvider({ children }) {
  const [ids, setIds] = useState(initIds);

  useEffect(() => {
    writeJSON(STORAGE_KEY, ids);
  }, [ids]);

  const has = useCallback((id) => ids.includes(id), [ids]);
  const add = useCallback((id) => setIds((prev) => (prev.includes(id) ? prev : [...prev, id])), []);
  const remove = useCallback((id) => setIds((prev) => prev.filter((x) => x !== id)), []);
  const toggle = useCallback(
    (id) => setIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id])),
    [],
  );
  const clear = useCallback(() => setIds([]), []);

  const value = useMemo(
    () => ({
      ids,
      products: ids.map(getProductById).filter(Boolean),
      count: ids.length,
      has,
      add,
      remove,
      toggle,
      clear,
    }),
    [ids, has, add, remove, toggle, clear],
  );

  return <WishlistContext.Provider value={value}>{children}</WishlistContext.Provider>;
}

// eslint-disable-next-line react/only-export-components
export function useWishlist() {
  const ctx = useContext(WishlistContext);
  if (!ctx) throw new Error('useWishlist must be used inside <WishlistProvider>');
  return ctx;
}
