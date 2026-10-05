import { createContext, useCallback, useContext, useEffect, useMemo, useReducer } from 'react';
import { coupons, delivery } from '../data/content.js';
import { getProductById, getStock, getVariantSku } from '../data/products.js';
import { formatBDT } from '../utils/format.js';
import { readJSON, writeJSON } from '../utils/storage.js';

const STORAGE_KEY = 'yb_cart_v1';

/** Seed used only when the cart has never been stored (matches the Cart/Checkout artboards). */
const SEED_ITEMS = [
  { productId: 'p01', size: 'M', colour: 'Off-white', qty: 1 },
  { productId: 'k01', size: 'L', colour: 'Maroon', qty: 2 },
];

/** Cart lines are identified by product + size + colour (lines[i].key). */
const getLineKey =(productId, size, colour) => `${productId}__${size ?? ''}__${colour ?? ''}`;

function maxQtyFor(item) {
  const product = getProductById(item.productId);
  if (!product) return 0;
  return getStock(product, item.colour, item.size);
}

function initState() {
  const saved = readJSON(STORAGE_KEY);
  if (saved && Array.isArray(saved.items)) {
    return {
      items: saved.items.filter((i) => getProductById(i.productId)),
      couponCode: saved.couponCode ?? null,
      zone: saved.zone ?? delivery.defaultZone,
    };
  }
  return { items: SEED_ITEMS, couponCode: null, zone: delivery.defaultZone };
}

function reducer(state, action) {
  switch (action.type) {
    case 'add': {
      const { productId, size, colour, qty } = action.item;
      const key = getLineKey(productId, size, colour);
      const max = maxQtyFor(action.item);
      const existing = state.items.find((i) => getLineKey(i.productId, i.size, i.colour) === key);
      if (existing) {
        return {
          ...state,
          items: state.items.map((i) =>
            i === existing ? { ...i, qty: Math.min(max, i.qty + qty) } : i,
          ),
        };
      }
      return { ...state, items: [...state.items, { productId, size, colour, qty: Math.min(max, qty) }] };
    }
    case 'setQty': {
      return {
        ...state,
        items: state.items
          .map((i) => {
            if (getLineKey(i.productId, i.size, i.colour) !== action.key) return i;
            const max = maxQtyFor(i);
            return { ...i, qty: Math.max(0, Math.min(max, action.qty)) };
          })
          .filter((i) => i.qty > 0),
      };
    }
    case 'variant': {
      const index = state.items.findIndex((i) => getLineKey(i.productId, i.size, i.colour) === action.key);
      if (index === -1) return state;
      const current = state.items[index];
      const next = { ...current, ...action.changes };
      const nextKey = getLineKey(next.productId, next.size, next.colour);
      const max = maxQtyFor(next);
      const others = state.items.filter((_, i) => i !== index);
      const duplicate = others.find((i) => getLineKey(i.productId, i.size, i.colour) === nextKey);
      if (duplicate) {
        return {
          ...state,
          items: others.map((i) => (i === duplicate ? { ...i, qty: Math.min(max, i.qty + current.qty) } : i)),
        };
      }
      const items = [...state.items];
      items[index] = { ...next, qty: Math.max(1, Math.min(max, next.qty)) };
      return { ...state, items };
    }
    case 'remove':
      return { ...state, items: state.items.filter((i) => getLineKey(i.productId, i.size, i.colour) !== action.key) };
    case 'restore': {
      const items = [...state.items];
      items.splice(Math.min(action.index ?? items.length, items.length), 0, action.item);
      return { ...state, items };
    }
    case 'clear':
      return { ...state, items: [], couponCode: null };
    case 'coupon':
      return { ...state, couponCode: action.code };
    case 'zone':
      return { ...state, zone: action.zone };
    default:
      return state;
  }
}

const CartContext = createContext(null);

export function CartProvider({ children }) {
  const [state, dispatch] = useReducer(reducer, undefined, initState);

  useEffect(() => {
    writeJSON(STORAGE_KEY, state);
  }, [state]);

  const addItem = useCallback(({ productId, size, colour, qty = 1 }) => {
    const product = getProductById(productId);
    if (!product) return { ok: false, message: 'Product not found.' };
    const stock = getStock(product, colour, size);
    if (stock <= 0) return { ok: false, message: `${product.name} is out of stock in this size.` };
    dispatch({ type: 'add', item: { productId, size, colour, qty } });
    return { ok: true, message: `${product.name} added to your cart.`, key: getLineKey(productId, size, colour) };
  }, []);

  const updateQty = useCallback((key, qty) => dispatch({ type: 'setQty', key, qty }), []);
  const updateVariant = useCallback((key, changes) => dispatch({ type: 'variant', key, changes }), []);
  const clearCart = useCallback(() => dispatch({ type: 'clear' }), []);
  const setDeliveryZone = useCallback((zone) => dispatch({ type: 'zone', zone }), []);
  const removeCoupon = useCallback(() => dispatch({ type: 'coupon', code: null }), []);

  const removeItem = useCallback(
    (key) => {
      const index = state.items.findIndex((i) => getLineKey(i.productId, i.size, i.colour) === key);
      if (index === -1) return null;
      const removed = { item: state.items[index], index };
      dispatch({ type: 'remove', key });
      return removed;
    },
    [state.items],
  );

  /** Undo a removeItem(): restoreItem(removedValueReturnedByRemoveItem) */
  const restoreItem = useCallback((removed) => {
    if (removed?.item) dispatch({ type: 'restore', item: removed.item, index: removed.index });
  }, []);

  const lines = useMemo(
    () =>
      state.items
        .map((item) => {
          const product = getProductById(item.productId);
          if (!product) return null;
          const colourObj = product.colours.find((c) => c.name === item.colour) || product.colours[0];
          return {
            ...item,
            key: getLineKey(item.productId, item.size, item.colour),
            product,
            name: product.name,
            image: colourObj?.image || product.image,
            tone: colourObj?.tone || product.tone,
            colourHex: colourObj?.hex,
            sku: getVariantSku(product, item.colour, item.size),
            unitPrice: product.price,
            oldPrice: product.oldPrice,
            lineTotal: product.price * item.qty,
            maxQty: getStock(product, item.colour, item.size),
          };
        })
        .filter(Boolean),
    [state.items],
  );

  const totals = useMemo(() => {
    const count = lines.reduce((n, l) => n + l.qty, 0);
    const subtotal = lines.reduce((n, l) => n + l.lineTotal, 0);
    const saleSavings = lines.reduce((n, l) => n + (l.oldPrice ? (l.oldPrice - l.unitPrice) * l.qty : 0), 0);

    // Coupon
    let coupon = null;
    let discount = 0;
    if (state.couponCode) {
      const def = coupons.find((c) => c.code === state.couponCode);
      if (def) {
        const valid = subtotal >= def.minSubtotal && count > 0;
        discount = valid ? (def.type === 'percent' ? Math.round((subtotal * def.value) / 100) : def.value) : 0;
        discount = Math.min(discount, subtotal);
        coupon = {
          code: def.code,
          label: def.label,
          description: def.description,
          valid,
          discount,
          error: valid ? null : `${def.code} needs a subtotal of ${formatBDT(def.minSubtotal)} or more.`,
        };
      }
    }

    // Delivery
    const zone = delivery.zones.find((z) => z.id === state.zone) || delivery.zones[0];
    const qualifiesForFree =
      subtotal >= delivery.freeDeliveryThreshold && (delivery.freeDeliveryWithCoupon || !coupon?.valid);
    const getDeliveryFee = (zoneId = zone.id) => {
      if (count === 0 || qualifiesForFree) return 0;
      return (delivery.zones.find((z) => z.id === zoneId) || zone).fee;
    };
    const deliveryFee = getDeliveryFee(zone.id);

    return {
      count,
      lineCount: lines.length,
      isEmpty: lines.length === 0,
      subtotal,
      saleSavings,
      coupon,
      discount,
      zone,
      deliveryZone: zone.id,
      deliveryFee,
      isFreeDelivery: count > 0 && qualifiesForFree,
      freeDeliveryThreshold: delivery.freeDeliveryThreshold,
      freeDeliveryRemaining: Math.max(0, delivery.freeDeliveryThreshold - subtotal),
      freeDeliveryProgress: Math.min(100, Math.round((subtotal / delivery.freeDeliveryThreshold) * 100)),
      getDeliveryFee,
      total: Math.max(0, subtotal - discount + deliveryFee),
    };
  }, [lines, state.couponCode, state.zone]);

  const applyCoupon = useCallback(
    (rawCode) => {
      const code = String(rawCode || '').trim().toUpperCase();
      if (!code) return { ok: false, message: 'Enter a coupon code.' };
      const def = coupons.find((c) => c.code === code);
      if (!def) return { ok: false, message: `“${code}” is not a valid coupon code.` };
      if (def.appOnly) return { ok: false, message: `${def.code} can only be used in our mobile app.` };
      dispatch({ type: 'coupon', code: def.code });
      if (totals.subtotal < def.minSubtotal) {
        return { ok: false, message: `${def.code} needs a subtotal of ${formatBDT(def.minSubtotal)} or more.` };
      }
      return { ok: true, message: `${def.code} applied · ${def.label}` };
    },
    [totals.subtotal],
  );

  const value = useMemo(
    () => ({
      items: state.items,
      lines,
      ...totals,
      addItem,
      updateQty,
      updateVariant,
      removeItem,
      restoreItem,
      clearCart,
      applyCoupon,
      removeCoupon,
      setDeliveryZone,
    }),
    [state.items, lines, totals, addItem, updateQty, updateVariant, removeItem, restoreItem, clearCart, applyCoupon, removeCoupon, setDeliveryZone],
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

// eslint-disable-next-line react/only-export-components
export function useCart() {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used inside <CartProvider>');
  return ctx;
}
