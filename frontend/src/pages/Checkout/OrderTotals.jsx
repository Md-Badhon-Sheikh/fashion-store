import './OrderTotals.css';

/**
 * Totals list used by the cart summary, checkout summary and order confirmation.
 *
 *   <OrderTotals
 *     rows={[
 *       { label: 'Subtotal', value: '৳5,150' },
 *       { label: 'Discount (EID10)', value: '−৳515', tone: 'brand' },
 *       { label: 'Delivery (Inside Dhaka)', value: 'Free', tone: 'free', note: 'Outside Dhaka ৳130' },
 *     ]}
 *     total="৳4,705"
 *   />
 *
 * Row tones: 'sale' (red), 'brand' (green), 'free' (bold green). Falsy rows are skipped.
 */
export default function OrderTotals({ rows, total, totalLabel = 'Total', className = '' }) {
  return (
    <dl className={`order-totals ${className}`.trim()}>
      {rows.filter(Boolean).map((row) => (
        <div key={row.label} className={`order-totals__row${row.className ? ` ${row.className}` : ''}`}>
          <dt>{row.label}</dt>
          <dd className={row.tone ? `order-totals__value--${row.tone}` : undefined}>{row.value}</dd>
          {row.note && <dd className="order-totals__note">{row.note}</dd>}
        </div>
      ))}
      <div className="order-totals__row order-totals__row--total">
        <dt>{totalLabel}</dt>
        <dd>{total}</dd>
      </div>
    </dl>
  );
}
