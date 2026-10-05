import { orderItems, orderNotes } from '../../data/account.js';
import { statusLabels } from '../../data/orders.js';
import { getProductById } from '../../data/products.js';
import { pluralize } from '../../utils/format.js';

/** Statuses that count as "on the way" (trackable). */
export const IN_PROGRESS = ['pending', 'confirmed', 'processing', 'shipped'];

/** Order summary (orders.js) + line items, note and labels for display. */
export function buildOrder(order) {
  const lines = (orderItems[order.number] || [])
    .map((line) => ({ ...line, product: getProductById(line.productId) }))
    .filter((line) => line.product);
  return {
    ...order,
    lines,
    note: orderNotes[order.number] || '',
    itemsLabel: pluralize(order.items, 'item'),
    statusLabel: statusLabels[order.status] || order.status,
    trackUrl: `/track-order?order=${order.number}`,
  };
}
