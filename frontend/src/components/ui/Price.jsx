import { formatBDT } from '../../utils/format.js';

/**
 * Price with optional struck-through old price.
 *   <Price price={2450} oldPrice={2950} />
 * Sale prices render in --sale red. `className` lets callers set font sizes
 * (.price__now / .price__old are the inner hooks).
 */
export default function Price({ price, oldPrice, className = '', saleColour = true }) {
  const onSale = Boolean(oldPrice && oldPrice > price);
  return (
    <span className={`price${onSale && saleColour ? ' price--sale' : ''} ${className}`.trim()}>
      <span className="price__now">
        {onSale && <span className="visually-hidden">Sale price </span>}
        {formatBDT(price)}
      </span>
      {onSale && (
        <span className="price__old">
          <span className="visually-hidden">Regular price </span>
          {formatBDT(oldPrice)}
        </span>
      )}
    </span>
  );
}
