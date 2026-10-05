import { useWishlist } from '../../context/WishlistContext.jsx';
import Icon from './Icon.jsx';
import './WishlistButton.css';

/**
 * Heart toggle bound to WishlistContext.
 *   <WishlistButton product={p} />                       44px white circle
 *   <WishlistButton product={p} size="sm" />             40px circle (carousel cards)
 *   <WishlistButton product={p} variant="inset" />       44px hit area, 32px visual (phone grids)
 * Position it with `className` from the parent component.
 */
export default function WishlistButton({ product, size = 'md', variant = 'circle', className = '' }) {
  const { has, toggle } = useWishlist();
  const wished = has(product.id);
  const label = wished ? `Remove ${product.name} from wishlist` : `Add ${product.name} to wishlist`;
  return (
    <button
      type="button"
      className={`wish-btn wish-btn--${size} wish-btn--${variant}${wished ? ' is-active' : ''} ${className}`.trim()}
      aria-label={label}
      aria-pressed={wished}
      onClick={(e) => {
        e.preventDefault();
        e.stopPropagation();
        toggle(product.id);
      }}
    >
      <span className="wish-btn__circle">
        <Icon name="heart" size={size === 'sm' ? 19 : 20} filled={wished} />
      </span>
    </button>
  );
}
