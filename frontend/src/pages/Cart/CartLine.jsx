import { useId } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import Price from '../../components/ui/Price.jsx';
import QtyStepper from '../../components/ui/QtyStepper.jsx';
import { useCart } from '../../context/CartContext.jsx';
import { getStock } from '../../data/products.js';
import { formatBDT, imageUrl } from '../../utils/format.js';

/** Low-stock warning appears at this many units left (Cart artboard: "Only 1 left in XL"). */
const LOW_STOCK = 3;

const VARIANT_SEP = '|';

/** Every size × colour of the product, with its stock, for the variant select. */
function variantOptions(product) {
  return product.colours.flatMap((c) =>
    product.sizes.map((s) => ({
      value: `${s.label}${VARIANT_SEP}${c.name}`,
      label: `${s.label} · ${c.name}`,
      stock: getStock(product, c.name, s.label),
    })),
  );
}

/**
 * One cart row (desktop table row / phone card).
 * Props: line (CartContext line), onRemove(line), onMoveToWishlist(line)
 */
export default function CartLine({ line, onRemove, onMoveToWishlist }) {
  const { updateQty, updateVariant } = useCart();
  const selectId = useId();
  const { product } = line;
  const url = `/product/${product.slug}`;
  const current = `${line.size}${VARIANT_SEP}${line.colour}`;
  const atMax = line.qty >= line.maxQty;

  let warning = null;
  if (line.maxQty <= 0) warning = `Out of stock in ${line.size}. Choose another size.`;
  else if (line.maxQty <= LOW_STOCK) warning = `Only ${line.maxQty} left in ${line.size}`;

  const changeVariant = (e) => {
    const [size, colour] = e.target.value.split(VARIANT_SEP);
    updateVariant(line.key, { size, colour });
  };

  return (
    <li className="cart-line">
      <Link to={url} className="cart-line__media media" style={{ background: line.tone }} tabIndex={-1} aria-hidden="true">
        <img src={imageUrl(line.image)} alt={line.name} loading="lazy" decoding="async" />
      </Link>

      <div className="cart-line__info">
        <div className="cart-line__top">
          <Link to={url} className="cart-line__name">
            {line.name}
          </Link>
          <button
            type="button"
            className="cart-line__trash only-mobile"
            aria-label={`Remove ${line.name} from cart`}
            onClick={() => onRemove(line)}
          >
            <Icon name="trash" size={19} />
          </button>
        </div>
        <div className="cart-line__sku only-desktop">SKU {line.sku}</div>

        <div className="cart-line__variant">
          <label htmlFor={selectId} className="cart-line__variant-label">
            Change size · colour
          </label>
          <select id={selectId} className="cart-line__select" value={current} onChange={changeVariant}>
            {variantOptions(product).map((o) => (
              <option key={o.value} value={o.value} disabled={o.stock <= 0 && o.value !== current}>
                {o.label}
                {o.stock <= 0 ? ' (sold out)' : ''}
              </option>
            ))}
          </select>
        </div>

        <div className="cart-line__unit">
          <Price price={line.unitPrice} oldPrice={line.oldPrice} />
          <span className="cart-line__each">each</span>
        </div>

        {warning && (
          <p className="cart-line__warn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M12 3l9 16H3l9-16z" />
              <path d="M12 10v4" />
              <path d="M12 17v.5" />
            </svg>
            {warning}
          </p>
        )}

        <div className="cart-line__actions only-desktop">
          <button type="button" className="cart-line__action" onClick={() => onMoveToWishlist(line)}>
            <Icon name="heart" size={16} />
            Move to wishlist<span className="visually-hidden">: {line.name}</span>
          </button>
          <button
            type="button"
            className="cart-line__action cart-line__action--remove"
            aria-label={`Remove ${line.name} from cart`}
            onClick={() => onRemove(line)}
          >
            <Icon name="trash" size={16} />
            Remove
          </button>
        </div>
      </div>

      <div className="cart-line__qty">
        <QtyStepper
          value={line.qty}
          onChange={(qty) => updateQty(line.key, qty)}
          max={Math.max(1, line.maxQty)}
          label={line.name}
          size="sm"
        />
        <span className="cart-line__stock only-desktop">{atMax ? 'Max available' : `${line.maxQty} in stock`}</span>
      </div>

      <div className="cart-line__total">
        <span className="visually-hidden">Line total </span>
        {formatBDT(line.lineTotal)}
      </div>
    </li>
  );
}
