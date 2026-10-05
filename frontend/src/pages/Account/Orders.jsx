import { Link, useNavigate } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { cardProgress, cardSteps } from '../../data/account.js';
import { getStock } from '../../data/products.js';
import { formatBDT, imageUrl } from '../../utils/format.js';
import { StatusChip } from './AccountParts.jsx';
import { IN_PROGRESS } from './buildOrder.js';

/**
 * Orders as a table (desktop, Account.dc.html) or as cards (phone, M-Account).
 * `orders` are built with buildOrder(). `onNotice(text, action?)` shows a toast.
 */
export default function OrdersList({ orders, isPhone, onNotice }) {
  const reorder = useReorder(onNotice);
  if (orders.length === 0) {
    return <p className="acct-empty">No orders here yet.</p>;
  }
  if (isPhone) {
    return (
      <div className="acct-order-cards">
        {orders.map((o) => (
          <OrderCard key={o.number} order={o} onReorder={() => reorder(o)} />
        ))}
      </div>
    );
  }
  return <OrdersTable orders={orders} onReorder={reorder} onNotice={onNotice} />;
}

/** Adds an order's items back to the cart (first in-stock variant if the old one sold out). */
function useReorder(onNotice) {
  const { addItem } = useCart();
  const navigate = useNavigate();
  return (order) => {
    let added = 0;
    order.lines.forEach((line) => {
      const inStock = getStock(line.product, line.colour, line.size) > 0;
      if (!inStock) return;
      const res = addItem({ productId: line.productId, size: line.size, colour: line.colour, qty: line.qty });
      if (res.ok) added += 1;
    });
    if (added > 0) navigate('/cart');
    else onNotice(`Items from #${order.number} are out of stock right now.`);
  };
}

/* ------------------------------------------------------------------ table */
function OrdersTable({ orders, onReorder, onNotice }) {
  return (
    <div className="acct-table-wrap">
      <table className="acct-table">
        <caption className="visually-hidden">Your orders</caption>
        <thead>
          <tr>
            <th scope="col">Order</th>
            <th scope="col">Date</th>
            <th scope="col">Items</th>
            <th scope="col" className="is-num">
              Total
            </th>
            <th scope="col">Payment</th>
            <th scope="col">Status</th>
            <th scope="col" className="is-num">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          {orders.map((o) => (
            <tr key={o.number}>
              <th scope="row" className="acct-table__order">
                #{o.number}
              </th>
              <td>{o.date}</td>
              <td>{o.itemsLabel}</td>
              <td className="is-num acct-table__total">{formatBDT(o.total)}</td>
              <td>{o.payment}</td>
              <td>
                <StatusChip status={o.status} />
              </td>
              <td className="is-num acct-table__actions">
                <PrimaryAction order={o} onReorder={onReorder} className="acct-pill" />
                <button
                  type="button"
                  className="acct-invoice"
                  aria-label={`Download invoice for #${o.number}`}
                  onClick={() => onNotice(`Invoice for #${o.number} will download here once the store API is connected.`)}
                >
                  <Icon name="download" size={14} strokeWidth={2} />
                  Invoice
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

/** Track (in progress) · Reorder (delivered) · Details (returned / cancelled) */
function PrimaryAction({ order, onReorder, className }) {
  if (IN_PROGRESS.includes(order.status)) {
    return (
      <Link to={order.trackUrl} className={className} aria-label={`Track order #${order.number}`}>
        Track
      </Link>
    );
  }
  if (order.status === 'delivered') {
    return (
      <button type="button" className={className} onClick={() => onReorder(order)} aria-label={`Reorder #${order.number}`}>
        Reorder
      </button>
    );
  }
  return (
    <Link to={order.trackUrl} className={className} aria-label={`Details of order #${order.number}`}>
      Details
    </Link>
  );
}

/* ------------------------------------------------------------- phone card */
function OrderCard({ order, onReorder }) {
  const progress = cardProgress[order.status];
  let primary;
  if (IN_PROGRESS.includes(order.status)) {
    primary = (
      <Link to={order.trackUrl} className="btn btn--primary btn--sm acct-order-card__btn">
        Track order
      </Link>
    );
  } else if (order.status === 'delivered') {
    primary = (
      <Link to="/account?tab=reviews" className="btn btn--primary btn--sm acct-order-card__btn">
        Write a review
      </Link>
    );
  } else {
    primary = (
      <button type="button" className="btn btn--primary btn--sm acct-order-card__btn" onClick={onReorder}>
        Reorder
      </button>
    );
  }

  return (
    <article className="acct-order-card" aria-labelledby={`oc-${order.number}`}>
      <div className="acct-order-card__top">
        <div>
          <h3 id={`oc-${order.number}`} className="acct-order-card__no">
            Order #{order.number}
          </h3>
          <p className="acct-order-card__meta">
            {order.date} · {order.itemsLabel} · {order.payment}
          </p>
        </div>
        <StatusChip status={order.status} />
      </div>

      <div className="acct-order-card__row">
        <ul role="list" className="acct-order-card__thumbs">
          {order.lines.map((line) => (
            <li key={`${line.productId}-${line.size}-${line.colour}`} className="acct-thumb media" style={{ background: line.product.tone }}>
              <img src={imageUrl(line.product.image)} alt={line.product.name} loading="lazy" decoding="async" />
            </li>
          ))}
        </ul>
        <span className="acct-order-card__total tabular">{formatBDT(order.total)}</span>
      </div>

      <div>
        {progress && (
          <>
            <div
              className={`acct-progress${order.status === 'delivered' ? ' is-done' : ''}`}
              role="progressbar"
              aria-label={`Order #${order.number} progress: ${order.statusLabel}`}
              aria-valuemin={0}
              aria-valuemax={100}
              aria-valuenow={progress.pct}
            >
              <span style={{ width: `${progress.pct}%` }} />
            </div>
            <ol className="acct-progress__steps" aria-hidden="true">
              {cardSteps.map((s, i) => (
                <li
                  key={s}
                  className={`${i < progress.reached ? 'is-reached' : ''}${i === progress.reached - 1 ? ' is-current' : ''}`.trim() || undefined}
                >
                  {s}
                </li>
              ))}
            </ol>
          </>
        )}
        {order.note && <p className="acct-order-card__note">{order.note}</p>}
      </div>

      <div className="acct-order-card__actions">
        {primary}
        <Link to={order.trackUrl} className="btn btn--sm acct-order-card__btn acct-order-card__btn--ghost">
          Details
        </Link>
      </div>
    </article>
  );
}
