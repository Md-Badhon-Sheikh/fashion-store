import { orderItems, orderNotes } from '../../data/account.js';
import { addresses, orderSteps, statusLabels } from '../../data/orders.js';
import { getProductById } from '../../data/products.js';

const PAYMENT_LABELS = { COD: 'Cash on Delivery', bKash: 'bKash', Nagad: 'Nagad', Card: 'Card' };

/**
 * Normalises an order from getOrderByNumber() into what the TrackOrder page
 * renders. The sample order (#WB-10482) has full detail (timeline, courier,
 * items, totals); the other orders are history summaries, so the missing
 * parts are derived or left out. The API should return the full shape.
 */
export function buildTrackView(order) {
  const full = Array.isArray(order.items);
  const stepIndex = orderSteps.indexOf(order.status);
  const closed = stepIndex === -1; // cancelled / returned

  const lines = full
    ? order.items.map((it) => {
        const product = getProductById(it.productId);
        return {
          key: `${it.productId}-${it.size}-${it.colour}`,
          name: it.name,
          slug: product?.slug,
          image: it.image,
          tone: it.tone,
          size: it.size,
          colour: it.colour,
          qty: it.qty,
          total: it.unitPrice * it.qty,
        };
      })
    : (orderItems[order.number] || [])
        .map((it) => ({ ...it, product: getProductById(it.productId) }))
        .filter((it) => it.product)
        .map((it) => ({
          key: `${it.productId}-${it.size}-${it.colour}`,
          name: it.product.name,
          slug: it.product.slug,
          image: it.product.image,
          tone: it.product.tone,
          size: it.size,
          colour: it.colour,
          qty: it.qty,
          total: null, // price paid per line isn't in the order summary
        }));

  const steps = full
    ? order.timeline
    : orderSteps.map((status) => ({ status, time: '', note: '' }));

  return {
    number: order.number,
    status: order.status,
    statusLabel: statusLabels[order.status] || order.status,
    closed,
    stepIndex,
    placed: full ? order.placedAt.split(' · ')[0] : order.date,
    itemCount: full ? order.itemCount : order.items,
    total: order.total,
    paymentLabel: full ? order.payment.label : PAYMENT_LABELS[order.payment] || order.payment,
    isCod: full ? order.payment.method === 'cod' : order.payment === 'COD',
    expected: full ? order.expectedDelivery : null,
    note: orderNotes[order.number] || '',
    steps: steps.map((s) => ({ ...s, label: statusLabels[s.status] || s.status })),
    history: full ? order.history : [],
    courier: full ? order.courier : null,
    deliveryZoneName: full ? order.deliveryZoneName : null,
    lines,
    totals: full
      ? { subtotal: order.subtotal, coupon: order.coupon, discount: order.discount, delivery: order.deliveryFee, total: order.total }
      : null,
    address: full ? order.address : addresses.find((a) => a.isDefault) || addresses[0],
    returnWindowEnds: full ? order.returnWindowEnds : null,
    canReturn: order.status === 'shipped' || order.status === 'delivered',
  };
}
