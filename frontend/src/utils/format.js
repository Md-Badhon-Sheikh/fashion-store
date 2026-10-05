/**
 * Format a number as Bangladeshi Taka with Indian digit grouping.
 *   formatBDT(214600)  -> "৳2,14,600"
 *   formatBDT(-515)    -> "−৳515"   (true minus sign, for discounts)
 *   formatBDT(0)       -> "৳0"
 */
export function formatBDT(amount) {
  const n = Math.round(Number(amount) || 0);
  const abs = Math.abs(n).toLocaleString('en-IN');
  return (n < 0 ? '−' : '') + '৳' + abs;
}

/** Discount label like "−৳515" for a positive discount amount. */
export function formatDiscount(amount) {
  return '−' + formatBDT(Math.abs(amount));
}

/** Percentage saved between an old and a new price, rounded: 23 */
export function percentOff(price, oldPrice) {
  if (!oldPrice || oldPrice <= price) return 0;
  return Math.round((1 - price / oldPrice) * 100);
}

/** "1 item" / "3 items" */
export function pluralize(count, singular, plural = singular + 's') {
  return `${count} ${count === 1 ? singular : plural}`;
}

/** Public URL for a design image key, e.g. imageUrl('hero1') -> "/images/hero1.jpg" */
export function imageUrl(key) {
  return key ? `/images/${key}.jpg` : '';
}
